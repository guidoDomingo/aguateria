<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Factura;
use App\Models\Cliente;
use App\Models\Recibo;
use App\Repositories\PagoRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PagoService
{
    protected $pagoRepository;

    public function __construct(PagoRepository $pagoRepository)
    {
        $this->pagoRepository = $pagoRepository;
    }

    /**
     * Registrar un pago
     */
    public function registrar(array $datos): Pago
    {
        return DB::transaction(function () use ($datos) {
            // Generar número de recibo si no se proporciona
            if (empty($datos['numero_recibo'])) {
                $datos['numero_recibo'] = $this->pagoRepository->siguienteNumeroRecibo();
            }

            // Establecer usuario actual
            $datos['user_id'] = Auth::id();

            // Crear el pago
            $pago = $this->pagoRepository->create($datos);

            // Si hay factura asociada, aplicar el pago
            if (!empty($datos['factura_id'])) {
                $factura = Factura::findOrFail($datos['factura_id']);
                $this->aplicarPagoAFactura($pago, $factura);
            }

            // Generar recibo
            $this->generarRecibo($pago);

            return $pago->load(['cliente', 'factura', 'metodoPago', 'cobrador', 'recibo']);
        });
    }

    /**
     * Registrar un pago con múltiples facturas (desde formulario)
     */
    public function registrarPago(array $datos, int $userId): array
    {
        try {
            return DB::transaction(function () use ($datos, $userId) {
                // Validar datos básicos
                if (empty($datos['facturas']) || !is_array($datos['facturas'])) {
                    throw new \Exception('Debe seleccionar al menos una factura para aplicar el pago');
                }

                $cliente = Cliente::findOrFail($datos['cliente_id']);
                $montoTotal = floatval($datos['monto']);
                $montoAsignado = 0;
                $pagosCreados = [];
                $facturasAfectadas = [];

                // Generar número base para los recibos
                $baseRecibo = $this->pagoRepository->siguienteNumeroRecibo();

                foreach ($datos['facturas'] as $index => $facturaData) {
                    $factura = Factura::findOrFail($facturaData['factura_id']);
                    $montoAAplicar = floatval($facturaData['monto']);
                    
                    if ($montoAAplicar <= 0) continue;
                    
                    // Verificar que no exceda el saldo pendiente
                    if ($montoAAplicar > $factura->saldo_pendiente) {
                        $montoAAplicar = $factura->saldo_pendiente;
                    }
                    
                    // Si hay múltiples facturas, generar números correlativos
                    if ($index > 0) {
                        $baseNumero = intval(substr($baseRecibo, 3));
                        $numeroRecibo = 'REC' . str_pad($baseNumero + $index, 8, '0', STR_PAD_LEFT);
                    } else {
                        $numeroRecibo = $baseRecibo;
                    }

                    // Crear el pago
                    $pago = Pago::create([
                        'empresa_id' => $datos['empresa_id'],
                        'cliente_id' => $datos['cliente_id'],
                        'factura_id' => $factura->id,
                        'metodo_pago_id' => $datos['metodo_pago_id'],
                        'cobrador_id' => $datos['cobrador_id'],
                        'monto_pagado' => $montoAAplicar,
                        'fecha_pago' => $datos['fecha_pago'],
                        'hora_pago' => now()->format('H:i:s'),
                        'numero_recibo' => $numeroRecibo,
                        'observaciones' => $datos['observaciones'],
                        'estado' => 'confirmado',
                        'user_id' => $userId,
                    ]);

                    // Aplicar pago a la factura
                    $this->aplicarPagoAFactura($pago, $factura);

                    // Generar recibo
                    $this->generarRecibo($pago);

                    $pagosCreados[] = $pago;
                    $facturasAfectadas[] = [
                        'factura_id' => $factura->id,
                        'numero_factura' => $factura->numero_factura,
                        'monto_aplicado' => $montoAAplicar,
                        'nuevo_estado' => $factura->fresh()->estado
                    ];

                    $montoAsignado += $montoAAplicar;
                }

                // Verificar que el monto asignado coincida con el total
                $diferencia = abs($montoTotal - $montoAsignado);
                if ($diferencia > 0.01) { // Permitir diferencia mínima por redondeo
                    throw new \Exception("El monto total ({$montoTotal}) no coincide con la suma de pagos asignados ({$montoAsignado})");
                }

                return [
                    'success' => true,
                    'message' => count($pagosCreados) === 1 ? 
                        'Pago registrado exitosamente' : 
                        'Se registraron ' . count($pagosCreados) . ' pagos exitosamente',
                    'pagos_creados' => $pagosCreados,
                    'facturas_afectadas' => $facturasAfectadas,
                    'monto_total' => $montoTotal,
                    'cliente' => $cliente->nombre_completo
                ];
            });
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al registrar el pago: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Aplicar pago a factura específica
     */
    public function aplicarPagoAFactura(Pago $pago, Factura $factura): void
    {
        $montoAplicar = min($pago->monto_pagado, $factura->saldo_pendiente);
        
        $factura->saldo_pendiente -= $montoAplicar;
        
        if ($factura->saldo_pendiente <= 0) {
            $factura->estado = 'pagado';
            $factura->fecha_pago = $pago->fecha_pago;
            $factura->saldo_pendiente = 0;
        } elseif ($factura->saldo_pendiente < $factura->total) {
            $factura->estado = 'parcial';
        }
        
        $factura->save();
    }

    /**
     * Procesar pago múltiple (para varias facturas)
     */
    public function procesarPagoMultiple(Cliente $cliente, array $facturas, array $datosPago): array
    {
        return DB::transaction(function () use ($cliente, $facturas, $datosPago) {
            $montoTotal = $datosPago['monto_pagado'];
            $montoRestante = $montoTotal;
            $facturasAfectadas = [];
            $pagos = [];

            // Ordenar facturas por fecha de vencimiento (primero las más antiguas)
            usort($facturas, function($a, $b) {
                return strtotime($a['fecha_vencimiento']) - strtotime($b['fecha_vencimiento']);
            });

            foreach ($facturas as $facturaData) {
                if ($montoRestante <= 0) break;

                $factura = Factura::findOrFail($facturaData['id']);
                $montoAAplicar = min($montoRestante, $factura->saldo_pendiente);

                if ($montoAAplicar > 0) {
                    // Crear pago específico para esta factura
                    $datosPagoEspecifico = array_merge($datosPago, [
                        'cliente_id' => $cliente->id,
                        'factura_id' => $factura->id,
                        'monto_pagado' => $montoAAplicar,
                        'numero_recibo' => $this->pagoRepository->siguienteNumeroRecibo()
                    ]);

                    $pago = $this->registrar($datosPagoEspecifico);
                    $pagos[] = $pago;

                    $facturasAfectadas[] = [
                        'factura_id' => $factura->id,
                        'monto_aplicado' => $montoAAplicar,
                        'nuevo_estado' => $factura->fresh()->estado
                    ];

                    $montoRestante -= $montoAAplicar;
                }
            }

            return [
                'pagos_creados' => $pagos,
                'facturas_afectadas' => $facturasAfectadas,
                'monto_aplicado' => $montoTotal - $montoRestante,
                'monto_sobrante' => $montoRestante
            ];
        });
    }

    /**
     * Anular un pago
     */
    public function anular(Pago $pago, string $motivo): Pago
    {
        return DB::transaction(function () use ($pago, $motivo) {
            // Verificar que el pago se pueda anular
            if ($pago->estado === 'anulado') {
                throw new \Exception('El pago ya está anulado');
            }

            // Revertir el pago en la factura
            if ($pago->factura) {
                $pago->factura->saldo_pendiente += $pago->monto_pagado;
                
                if ($pago->factura->saldo_pendiente >= $pago->factura->total) {
                    $pago->factura->estado = $pago->factura->estaVencida() ? 'vencido' : 'pendiente';
                    $pago->factura->fecha_pago = null;
                } elseif ($pago->factura->saldo_pendiente > 0) {
                    $pago->factura->estado = 'parcial';
                }
                
                $pago->factura->save();
            }

            // Anular el pago
            $pago->estado = 'anulado';
            $pago->observaciones = $motivo;
            $pago->save();

            return $pago;
        });
    }

    /**
     * Generar recibo para el pago
     */
    public function generarRecibo(Pago $pago): Recibo
    {
        $cliente = $pago->cliente;
        $empresa = $cliente->empresa;

        return Recibo::create([
            'pago_id' => $pago->id,
            'numero_recibo' => $pago->numero_recibo,
            'cliente_nombre' => $cliente->nombre_completo,
            'cliente_cedula' => $cliente->cedula,
            'cliente_direccion' => $cliente->direccion,
            'monto_pagado' => $pago->monto_pagado,
            'fecha_pago' => $pago->fecha_pago,
            'periodo_pagado' => $pago->factura ? $pago->factura->periodo->nombre : 'Pago general',
            'metodo_pago' => $pago->metodoPago->nombre,
            'referencia' => $pago->referencia,
            'observaciones' => $pago->observaciones,
            'datos_empresa' => [
                'nombre' => $empresa->nombre,
                'direccion' => $empresa->direccion,
                'telefono' => $empresa->telefono,
                'email' => $empresa->email,
                'logo' => $empresa->logo
            ]
        ]);
    }

    /**
     * Obtener estadísticas de cobranza
     */
    public function estadisticasCobranza(): array
    {
        return $this->pagoRepository->estadisticasCobranza();
    }

    /**
     * Resumen de cobranza por período
     */
    public function resumenCobranza(\DateTime $fechaInicio, \DateTime $fechaFin): array
    {
        $fechaInicio = \Carbon\Carbon::instance($fechaInicio);
        $fechaFin = \Carbon\Carbon::instance($fechaFin);
        
        $pagos = $this->pagoRepository->porRangoFechas($fechaInicio, $fechaFin);
        $resumenPorMetodo = $this->pagoRepository->resumenPorMetodo($fechaInicio, $fechaFin);

        return [
            'periodo' => [
                'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                'fecha_fin' => $fechaFin->format('Y-m-d')
            ],
            'resumen_general' => [
                'cantidad_pagos' => $pagos->count(),
                'monto_total' => $pagos->sum('monto_pagado'),
                'clientes_unicos' => $pagos->unique('cliente_id')->count(),
                'promedio_pago' => $pagos->count() > 0 ? $pagos->avg('monto_pagado') : 0
            ],
            'por_metodo_pago' => $resumenPorMetodo,
            'por_dia' => $this->agruparPagosPorDia($pagos),
        ];
    }

    /**
     * Cobranza por cobrador en un período
     */
    public function cobranzaPorCobrador(int $cobradorId, \DateTime $fechaInicio, \DateTime $fechaFin): array
    {
        $fechaInicio = \Carbon\Carbon::instance($fechaInicio);
        $fechaFin = \Carbon\Carbon::instance($fechaFin);
        
        return $this->pagoRepository->porCobradorEnPeriodo($cobradorId, $fechaInicio, $fechaFin);
    }

    /**
     * Agrupar pagos por día
     */
    private function agruparPagosPorDia($pagos): array
    {
        return $pagos->groupBy(function ($pago) {
            return $pago->fecha_pago;
        })->map(function ($pagosPorDia) {
            return [
                'cantidad' => $pagosPorDia->count(),
                'monto_total' => $pagosPorDia->sum('monto_pagado')
            ];
        })->toArray();
    }
}