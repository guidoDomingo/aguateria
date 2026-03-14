<?php

namespace App\Services;

use App\Models\Factura;
use App\Models\FacturaDetalle;
use App\Models\PeriodoFacturacion;
use App\Models\Cliente;
use App\Repositories\FacturaRepository;
use App\Repositories\ClienteRepository;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FacturacionService
{
    protected $facturaRepository;
    protected $clienteRepository;

    public function __construct(
        FacturaRepository $facturaRepository,
        ClienteRepository $clienteRepository
    ) {
        $this->facturaRepository = $facturaRepository;
        $this->clienteRepository = $clienteRepository;
    }

    /**
     * Generar facturas mensuales para todos los clientes activos
     */
    public function generarFacturasMensuales(int $año, int $mes, int $empresaId = null): array
    {
        try {
            return DB::transaction(function () use ($año, $mes, $empresaId) {
                // Crear o actualizar período de facturación
                $periodo = $this->crearOActualizarPeriodo($año, $mes, $empresaId);
                
                // Obtener clientes activos que no tengan factura para este período
                $clientesQuery = Cliente::where('estado', 'activo')
                    ->with(['barrio', 'tarifa', 'cobrador']);
                
                if ($empresaId) {
                    $clientesQuery->where('empresa_id', $empresaId);
                }
                
                $clientes = $clientesQuery->get();
                
                $facturasGeneradas = 0;
                $errores = [];
                $montoTotal = 0;
                
                foreach ($clientes as $cliente) {
                    // Verificar si ya tiene factura para este período
                    $facturaExistente = Factura::where('cliente_id', $cliente->id)
                                              ->where('periodo_id', $periodo->id)
                                              ->first();
                    
                    if (!$facturaExistente) {
                        try {
                            $factura = $this->generarFacturaParaClienteConAcumulacion($cliente, $periodo, []);
                            $facturasGeneradas++;
                            $montoTotal += $factura->total;
                        } catch (\Exception $e) {
                            $errores[] = [
                                'cliente_id' => $cliente->id,
                                'cliente_nombre' => $cliente->nombre_completo,
                                'error' => $e->getMessage()
                            ];
                        }
                    }
                }
                
                // Actualizar período
                $periodo->update([
                    'total_facturas' => $facturasGeneradas,
                    'monto_total' => $montoTotal,
                    'fecha_facturacion' => now(),
                    'estado' => 'facturado'
                ]);
                
                $message = "Facturación masiva completada: {$facturasGeneradas} facturas generadas";
                if (count($errores) > 0) {
                    $message .= " con " . count($errores) . " errores";
                }
                
                return [
                    'success' => true,
                    'message' => $message,
                    'periodo' => $periodo,
                    'facturas_generadas' => $facturasGeneradas,
                    'monto_total' => $montoTotal,
                    'errores' => $errores
                ];
            });
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error en facturación masiva: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generar facturas para el período actual automáticamente
     */
    public function generarFacturasPeriodoActual(int $empresaId = null): array
    {
        $fechaActual = Carbon::now();
        return $this->generarFacturasMensuales($fechaActual->year, $fechaActual->month, $empresaId);
    }

    /**
     * Generar facturas para el próximo mes
     */
    public function generarFacturasProximoMes(int $empresaId = null): array  
    {
        $fechaProximo = Carbon::now()->addMonth();
        return $this->generarFacturasMensuales($fechaProximo->year, $fechaProximo->month, $empresaId);
    }

    /**
     * Generar factura individual para un cliente con acumulación de saldos pendientes
     */
    public function generarFacturaParaClienteConAcumulacion(Cliente $cliente, PeriodoFacturacion $periodo, array $datosAdicionales = []): Factura
    {
        return DB::transaction(function () use ($cliente, $periodo, $datosAdicionales) {
            // Obtener siguiente número de factura
            $numeroInfo = $this->facturaRepository->siguienteNumeroFactura();
            
            // Calcular fechas - USAR EL PERÍODO COMO BASE, NO LA FECHA DE EMISIÓN
            $fechaEmision = now();
            $fechaVencimiento = isset($datosAdicionales['fecha_vencimiento']) ? 
                               Carbon::parse($datosAdicionales['fecha_vencimiento']) :
                               $this->calcularFechaVencimientoPorPeriodo($periodo, $cliente);
            
            // Calcular saldos pendientes de facturas anteriores
            $saldosPendientes = $this->calcularSaldosPendiantesCliente($cliente);
            
            // Calcular montos del período actual
            $subtotalPeriodo = $cliente->tarifa->monto_mensual;
            $descuentoCliente = $cliente->descuento_especial > 0 ? 
                               ($subtotalPeriodo * $cliente->descuento_especial / 100) : 0;
            $descuentoAdicional = isset($datosAdicionales['descuento_adicional']) && $datosAdicionales['descuento_adicional'] > 0 ?
                                 ($subtotalPeriodo * $datosAdicionales['descuento_adicional'] / 100) : 0;
            $descuentoTotal = $descuentoCliente + $descuentoAdicional;
            
            // Calcular totales incluyendo saldos pendientes
            $subtotal = $subtotalPeriodo + $saldosPendientes['saldo_principal'] + $saldosPendientes['mora_acumulada'];
            $total = $subtotal - $descuentoTotal;
            
            // Crear factura
            $factura = $this->facturaRepository->create([
                'empresa_id' => $cliente->empresa_id,
                'cliente_id' => $cliente->id,
                'periodo_id' => $periodo->id,
                'numero_factura' => $numeroInfo['numero_factura'],
                'serie' => $numeroInfo['serie'],
                'numero' => $numeroInfo['numero'],
                'subtotal' => $subtotal,
                'descuento' => $descuentoTotal,
                'mora' => $saldosPendientes['mora_acumulada'],
                'total' => $total,
                'saldo_pendiente' => $total,
                'fecha_emision' => $fechaEmision,
                'fecha_vencimiento' => $fechaVencimiento,
                'estado' => 'pendiente',
                'tipo_factura' => 'mensual',
                'observaciones' => $this->generarObservacionesAcumulacion($saldosPendientes, $datosAdicionales['observaciones'] ?? null),
                'datos_cliente' => $cliente->toArray()
            ]);
            
            // Crear detalle de servicio del período actual
            FacturaDetalle::create([
                'factura_id' => $factura->id,
                'concepto' => "Servicio de agua - {$periodo->nombre}",
                'descripcion' => $cliente->tarifa->descripcion ?? '',
                'cantidad' => 1,
                'precio_unitario' => $subtotalPeriodo,
                'subtotal' => $subtotalPeriodo,
                'tipo' => 'servicio'
            ]);
            
            // Agregar descuento si existe
            if ($descuentoTotal > 0) {
                $descripcionDescuento = [];
                if ($descuentoCliente > 0) {
                    $descripcionDescuento[] = "Descuento cliente {$cliente->descuento_especial}%";
                }
                if ($descuentoAdicional > 0) {
                    $descripcionDescuento[] = "Descuento adicional {$datosAdicionales['descuento_adicional']}%";
                }
                
                FacturaDetalle::create([
                    'factura_id' => $factura->id,
                    'concepto' => 'Descuentos aplicados',
                    'descripcion' => implode(', ', $descripcionDescuento),
                    'cantidad' => 1,
                    'precio_unitario' => -$descuentoTotal,
                    'subtotal' => -$descuentoTotal,
                    'tipo' => 'descuento'
                ]);
            }
            
            // Agregar saldos pendientes como detalles
            if ($saldosPendientes['cantidad_facturas'] > 0) {
                FacturaDetalle::create([
                    'factura_id' => $factura->id,
                    'concepto' => 'Saldos pendientes acumulados',
                    'descripcion' => "Facturas pendientes: {$saldosPendientes['cantidad_facturas']} | Saldo principal: " . number_format($saldosPendientes['saldo_principal'], 0, ',', '.') . " Gs.",
                    'cantidad' => 1,
                    'precio_unitario' => $saldosPendientes['saldo_principal'],
                    'subtotal' => $saldosPendientes['saldo_principal'],
                    'tipo' => 'saldo_pendiente'
                ]);
            }
            
            // Agregar moras pendientes si existen
            if ($saldosPendientes['mora_acumulada'] > 0) {
                FacturaDetalle::create([
                    'factura_id' => $factura->id,
                    'concepto' => 'Moras pendientes acumuladas',
                    'descripcion' => "Moras de facturas vencidas anteriores",
                    'cantidad' => 1,
                    'precio_unitario' => $saldosPendientes['mora_acumulada'],
                    'subtotal' => $saldosPendientes['mora_acumulada'],
                    'tipo' => 'mora_pendiente'
                ]);
            }
            
            // Marcar facturas anteriores como consolidadas en esta nueva factura
            if (count($saldosPendientes['facturas_ids']) > 0) {
                Factura::whereIn('id', $saldosPendientes['facturas_ids'])
                       ->update([
                           'estado' => 'consolidado',
                           'observaciones' => DB::raw("CONCAT(COALESCE(observaciones, ''), ' | Saldo consolidado en factura #" . $factura->numero_factura . "')")
                       ]);
            }
            
            return $factura->load(['detalles', 'cliente']);
        });
    }

    /**
     * Calcular saldos pendientes de un cliente
     */
    private function calcularSaldosPendiantesCliente(Cliente $cliente): array
    {
        $facturasPendientes = Factura::where('cliente_id', $cliente->id)
                                    ->whereIn('estado', ['pendiente', 'vencido', 'parcial'])
                                    ->where('saldo_pendiente', '>', 0)
                                    ->get();

        $saldoPrincipal = 0;
        $moraAcumulada = 0;
        $facturasIds = collect();

        foreach ($facturasPendientes as $factura) {
            $saldoPrincipal += $factura->saldo_pendiente;
            
            // Calcular mora si la factura está vencida
            if ($factura->estaVencida()) {
                $moraCalculada = $factura->calcularMora();
                if ($moraCalculada > ($factura->mora ?? 0)) {
                    $moraAcumulada += $moraCalculada;
                    
                    // Actualizar mora en la factura original
                    $factura->update(['mora' => $moraCalculada]);
                } else {
                    $moraAcumulada += ($factura->mora ?? 0);
                }
            }
            
            $facturasIds->push($factura->id);
        }

        return [
            'cantidad_facturas' => $facturasPendientes->count(),
            'saldo_principal' => $saldoPrincipal,
            'mora_acumulada' => $moraAcumulada,
            'facturas_ids' => $facturasIds->toArray(),
            'total_adeudado' => $saldoPrincipal + $moraAcumulada
        ];
    }

    /**
     * Generar observaciones para factura con acumulación
     */
    private function generarObservacionesAcumulacion(array $saldosPendientes, string $observacionesAdicionales = null): ?string
    {
        $observaciones = [];

        if ($saldosPendientes['cantidad_facturas'] > 0) {
            $observaciones[] = "Incluye {$saldosPendientes['cantidad_facturas']} factura(s) pendiente(s)";
        }

        if ($saldosPendientes['mora_acumulada'] > 0) {
            $observaciones[] = "Incluye Gs. " . number_format($saldosPendientes['mora_acumulada'], 0, ',', '.') . " en moras";
        }

        if ($observacionesAdicionales) {
            $observaciones[] = $observacionesAdicionales;
        }

        return empty($observaciones) ? null : implode(' | ', $observaciones);
    }

    /**
     * Generar factura individual para un cliente
     */
    public function generarFacturaParaCliente(Cliente $cliente, PeriodoFacturacion $periodo, array $datosAdicionales = []): Factura
    {
        return DB::transaction(function () use ($cliente, $periodo, $datosAdicionales) {
            // Obtener siguiente número de factura
            $numeroInfo = $this->facturaRepository->siguienteNumeroFactura();
            
            // Calcular fechas - USAR EL PERÍODO COMO BASE, NO LA FECHA DE EMISIÓN
            $fechaEmision = now();
            $fechaVencimiento = isset($datosAdicionales['fecha_vencimiento']) ? 
                               Carbon::parse($datosAdicionales['fecha_vencimiento']) :
                               $this->calcularFechaVencimientoPorPeriodo($periodo, $cliente);
            
            // Obtener saldos pendientes de facturas anteriores
            $saldosPendientes = $this->calcularSaldosPendiantesCliente($cliente);
            
            // Calcular montos del período actual
            $subtotalPeriodo = $cliente->tarifa->monto_mensual;
            $descuentoCliente = $cliente->descuento_especial > 0 ? 
                               ($subtotalPeriodo * $cliente->descuento_especial / 100) : 0;
            $descuentoAdicional = isset($datosAdicionales['descuento_adicional']) && $datosAdicionales['descuento_adicional'] > 0 ?
                                 ($subtotalPeriodo * $datosAdicionales['descuento_adicional'] / 100) : 0;
            $descuentoTotal = $descuentoCliente + $descuentoAdicional;
            
            // Calcular totales incluyendo saldos pendientes
            $subtotal = $subtotalPeriodo + $saldosPendientes['saldo_principal'] + $saldosPendientes['mora_acumulada'];
            $total = $subtotal - $descuentoTotal;
            
            // Crear factura
            $factura = $this->facturaRepository->create([
                'empresa_id' => $cliente->empresa_id,
                'cliente_id' => $cliente->id,
                'periodo_id' => $periodo->id,
                'numero_factura' => $numeroInfo['numero_factura'],
                'serie' => $numeroInfo['serie'],
                'numero' => $numeroInfo['numero'],
                'subtotal' => $subtotal,
                'descuento' => $descuentoTotal,
                'total' => $total,
                'saldo_pendiente' => $total,
                'fecha_emision' => $fechaEmision,
                'fecha_vencimiento' => $fechaVencimiento,
                'estado' => 'pendiente',
                'tipo_factura' => 'mensual',
                'observaciones' => $datosAdicionales['observaciones'] ?? null,
                'datos_cliente' => $cliente->toArray()
            ]);
            
            // Crear detalle de servicio
            FacturaDetalle::create([
                'factura_id' => $factura->id,
                'concepto' => "Servicio de agua - {$periodo->nombre}",
                'descripcion' => $cliente->tarifa->descripcion ?? '',
                'cantidad' => 1,
                'precio_unitario' => $subtotal,
                'subtotal' => $subtotal,
                'tipo' => 'servicio'
            ]);
            
            // Agregar descuento si existe
            if ($descuentoTotal > 0) {
                $descripcionDescuento = [];
                if ($descuentoCliente > 0) {
                    $descripcionDescuento[] = "Descuento cliente {$cliente->descuento_especial}%";
                }
                if ($descuentoAdicional > 0) {
                    $descripcionDescuento[] = "Descuento adicional {$datosAdicionales['descuento_adicional']}%";
                }
                
                FacturaDetalle::create([
                    'factura_id' => $factura->id,
                    'concepto' => 'Descuentos aplicados',
                    'descripcion' => implode(', ', $descripcionDescuento),
                    'cantidad' => 1,
                    'precio_unitario' => -$descuentoTotal,
                    'subtotal' => -$descuentoTotal,
                    'tipo' => 'descuento'
                ]);
            }
            
            return $factura->load(['detalles', 'cliente']);
        });
    }

    /**
     * Crear factura de reconexión
     */
    public function crearFacturaReconexion(Cliente $cliente): Factura
    {
        return DB::transaction(function () use ($cliente) {
            $numeroInfo = $this->facturaRepository->siguienteNumeroFactura();
            $costoReconexion = $cliente->tarifa->costo_reconexion;
            
            $factura = $this->facturaRepository->create([
                'cliente_id' => $cliente->id,
                'numero_factura' => $numeroInfo['numero_factura'],
                'serie' => $numeroInfo['serie'],
                'numero' => $numeroInfo['numero'],
                'subtotal' => $costoReconexion,
                'total' => $costoReconexion,
                'saldo_pendiente' => $costoReconexion,
                'fecha_emision' => now(),
                'fecha_vencimiento' => now()->addDays(5),
                'estado' => 'pendiente',
                'tipo_factura' => 'reconexion',
            ]);
            
            FacturaDetalle::create([
                'factura_id' => $factura->id,
                'concepto' => 'Costo de reconexión de servicio',
                'cantidad' => 1,
                'precio_unitario' => $costoReconexion,
                'subtotal' => $costoReconexion,
                'tipo' => 'reconexion'
            ]);
            
            return $factura->load(['detalles', 'cliente']);
        });
    }

    /**
     * Aplicar moras a facturas vencidas
     */
    public function aplicarMoras(): array
    {
        $resultado = [
            'facturas_procesadas' => 0,
            'moras_aplicadas' => 0,
            'monto_mora_total' => 0
        ];
        
        return DB::transaction(function () use ($resultado) {
            $facturasVencidas = $this->facturaRepository->model
                ->where('estado', 'pendiente')
                ->where('fecha_vencimiento', '<', now())
                ->with(['cliente.tarifa'])
                ->get();
            
            foreach ($facturasVencidas as $factura) {
                $resultado['facturas_procesadas']++;
                
                if ($factura->cliente && $factura->cliente->puedeGenerarMora()) {
                    $mora = $factura->calcularMora();
                    
                    if ($mora > 0 && $factura->mora != $mora) {
                        // Crear detalle de mora
                        FacturaDetalle::updateOrCreate(
                            [
                                'factura_id' => $factura->id,
                                'tipo' => 'mora'
                            ],
                            [
                                'concepto' => 'Mora por pago tardío',
                                'descripcion' => "Mora aplicada por {$factura->diasVencido()} días de atraso",
                                'cantidad' => 1,
                                'precio_unitario' => $mora,
                                'subtotal' => $mora
                            ]
                        );
                        
                        // Actualizar factura
                        $factura->mora = $mora;
                        $factura->total = $factura->subtotal + $mora - $factura->descuento;
                        $factura->saldo_pendiente = $factura->total;
                        $factura->estado = 'vencido';
                        $factura->save();
                        
                        $resultado['moras_aplicadas']++;
                        $resultado['monto_mora_total'] += $mora;
                    }
                }
            }
            
            return $resultado;
        });
    }

    /**
     * Obtener estadísticas de facturación
     */
    public function estadisticas(): array
    {
        return $this->facturaRepository->estadisticas();
    }

    /**
     * Calcular factura sin crearla (para preview)
     */
    public function calcularFactura(array $datos): array
    {
        try {
            $cliente = Cliente::with(['tarifa', 'barrio', 'cobrador'])
                ->find($datos['cliente_id']);
            
            $periodo = PeriodoFacturacion::find($datos['periodo_facturacion_id']);
            
            if (!$cliente || !$periodo) {
                return [
                    'success' => false,
                    'message' => 'Cliente o período no encontrado'
                ];
            }

            if (!$cliente->tarifa) {
                return [
                    'success' => false,
                    'message' => 'Cliente no tiene tarifa asignada'
                ];
            }

            // Calcular fechas
            $fechaEmision = now();
            $diasVencimiento = $cliente->dia_vencimiento_personalizado ?? $cliente->tarifa->dias_vencimiento ?? 15;
            $fechaVencimiento = $fechaEmision->copy()->addDays($diasVencimiento);

            // Calcular montos
            $subtotal = $cliente->tarifa->monto_mensual ?? 0;
            $descuentoCliente = $cliente->descuento_especial > 0 ? 
                              ($subtotal * $cliente->descuento_especial / 100) : 0;
            $descuentoAdicional = isset($datos['descuento_adicional']) && $datos['descuento_adicional'] > 0 ?
                                ($subtotal * $datos['descuento_adicional'] / 100) : 0;
            $descuentoTotal = $descuentoCliente + $descuentoAdicional;
            $total = $subtotal - $descuentoTotal;

            return [
                'success' => true,
                'data' => [
                    'cliente' => $cliente->nombre . ' ' . $cliente->apellido,
                    'barrio' => $cliente->barrio->nombre ?? 'Sin barrio',
                    'tarifa' => $cliente->tarifa->nombre,
                    'periodo' => $periodo->nombre,
                    'fecha_emision' => $fechaEmision->format('Y-m-d'),
                    'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                    'precio_base' => $subtotal,
                    'descuento_tarifa' => 0, // No hay descuento de tarifa por defecto
                    'porcentaje_descuento_tarifa' => 0,
                    'descuento_personalizado' => $descuentoCliente,
                    'porcentaje_descuento_personalizado' => $cliente->descuento_especial,
                    'descuento_adicional' => $descuentoAdicional,
                    'total' => $total,
                    'subtotal' => $subtotal,
                    'descuento_total' => $descuentoTotal,
                    'detalles' => [
                        [
                            'concepto' => "Servicio de agua - {$periodo->nombre}",
                            'descripcion' => $cliente->tarifa->descripcion ?? '',
                            'cantidad' => 1,
                            'precio_unitario' => $subtotal,
                            'subtotal' => $subtotal
                        ]
                    ]
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al calcular factura: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generar factura individual
     */
    public function generarFacturaIndividual(array $datos): array
    {
        try {
            return DB::transaction(function () use ($datos) {
                $cliente = Cliente::with(['tarifa', 'empresa'])->find($datos['cliente_id']);
                $periodo = PeriodoFacturacion::find($datos['periodo_facturacion_id']);

                if (!$cliente || !$periodo || !$cliente->tarifa) {
                    return [
                        'success' => false,
                        'message' => 'Datos incompletos para generar factura'
                    ];
                }

                // Verificar si ya existe factura para este cliente/período
                $facturaExistente = Factura::where('cliente_id', $cliente->id)
                                          ->where('periodo_id', $periodo->id)
                                          ->first();

                if ($facturaExistente) {
                    return [
                        'success' => false,
                        'message' => 'Ya existe una factura para este cliente en el período seleccionado'
                    ];
                }

                // Crear la factura
                $factura = $this->generarFacturaParaCliente($cliente, $periodo, $datos);
                
                return [
                    'success' => true,
                    'message' => "Factura {$factura->numero_factura} generada exitosamente",
                    'factura' => $factura
                ];
            });

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al generar factura: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar factura existente
     */
    public function actualizarFactura(int $facturaId, array $datos): array
    {
        try {
            return DB::transaction(function () use ($facturaId, $datos) {
                $factura = Factura::with(['detalles'])->find($facturaId);
                
                if (!$factura) {
                    return [
                        'success' => false,
                        'message' => 'Factura no encontrada'
                    ];
                }

                if ($factura->estado !== 'pendiente') {
                    return [
                        'success' => false,
                        'message' => 'Solo se pueden actualizar facturas pendientes'
                    ];
                }

                // Actualizar campos permitidos
                $factura->update([
                    'fecha_vencimiento' => $datos['fecha_vencimiento'] ?? $factura->fecha_vencimiento,
                    'observaciones' => $datos['observaciones'] ?? $factura->observaciones
                ]);

                return [
                    'success' => true,
                    'message' => "Factura {$factura->numero_factura} actualizada exitosamente",
                    'factura' => $factura
                ];
            });

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar factura: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calcular fecha de vencimiento según configuración de tarifa
     */
    private function calcularFechaVencimiento(Carbon $fechaEmision, Cliente $cliente): Carbon
    {
        $tarifa = $cliente->tarifa;
        
        // Si el cliente tiene día personalizado, siempre usarlo (día fijo del mes siguiente)
        if ($cliente->dia_vencimiento_personalizado) {
            return $fechaEmision->copy()->addMonth()->day($cliente->dia_vencimiento_personalizado);
        }
        
        // Usar configuración de la tarifa
        if ($tarifa->tipo_vencimiento === 'dia_fijo' && $tarifa->dia_fijo_vencimiento) {
            // Día fijo: SIEMPRE usar el día configurado del MES SIGUIENTE al de emisión
            $fechaVencimiento = $fechaEmision->copy()->addMonth();
            
            // Ajustar al día correcto del mes
            $diaDeseado = $tarifa->dia_fijo_vencimiento;
            $ultimoDiaDelMes = $fechaVencimiento->daysInMonth;
            
            // Si el día deseado no existe en el mes (ej: 31 en febrero), usar el último día del mes
            if ($diaDeseado > $ultimoDiaDelMes) {
                $fechaVencimiento->day($ultimoDiaDelMes);
            } else {
                $fechaVencimiento->day($diaDeseado);
            }
            
            return $fechaVencimiento;
        } else {
            // Días corridos: TAMBIÉN ir al mes siguiente para consistencia
            $diasVencimiento = $tarifa->dias_vencimiento ?? 30;
            
            // Para mantener consistencia, los días corridos también van al mes siguiente
            // Ejemplo: Factura de abril -> vence en mayo
            $fechaVencimiento = $fechaEmision->copy()->addMonth()->startOfMonth();
            $fechaVencimiento->addDays($diasVencimiento - 1); // -1 porque startOfMonth es día 1
            
            return $fechaVencimiento;
        }
    }

    /**
     * Calcular fecha de vencimiento basada en el período de la factura
     * La lógica es: período + 1 mes = vencimiento
     * Ejemplo: Facturas del período ABRIL 2026 vencen en MAYO 2026
     */
    private function calcularFechaVencimientoPorPeriodo(PeriodoFacturacion $periodo, Cliente $cliente): Carbon
    {
        $tarifa = $cliente->tarifa;
        
        // Si el cliente tiene día personalizado, siempre usarlo
        if ($cliente->dia_vencimiento_personalizado) {
            return Carbon::create($periodo->año, $periodo->mes, 1)->addMonth()->day($cliente->dia_vencimiento_personalizado);
        }
        
        // Usar configuración de la tarifa
        if ($tarifa->tipo_vencimiento === 'dia_fijo' && $tarifa->dia_fijo_vencimiento) {
            // Día fijo: mes siguiente del período + día específico
            $fechaVencimiento = Carbon::create($periodo->año, $periodo->mes, 1)->addMonth();
            
            // Ajustar al día correcto del mes
            $diaDeseado = $tarifa->dia_fijo_vencimiento;
            $ultimoDiaDelMes = $fechaVencimiento->daysInMonth;
            
            // Si el día deseado no existe en el mes (ej: 31 en febrero), usar el último día del mes
            if ($diaDeseado > $ultimoDiaDelMes) {
                $fechaVencimiento->day($ultimoDiaDelMes);
            } else {
                $fechaVencimiento->day($diaDeseado);
            }
            
            return $fechaVencimiento;
        } else {
            // Días corridos: mes siguiente del período + días configurados
            $diasVencimiento = $tarifa->dias_vencimiento ?? 30;
            
            // Calcular desde el primer día del mes siguiente al período
            $fechaVencimiento = Carbon::create($periodo->año, $periodo->mes, 1)->addMonth();
            $fechaVencimiento->addDays($diasVencimiento - 1); // -1 porque ya estamos en día 1
            
            return $fechaVencimiento;
        }
    }

    /**
     * Crear o actualizar período de facturación
     */
    private function crearOActualizarPeriodo(int $año, int $mes, int $empresaId = null): PeriodoFacturacion
    {
        $fechaInicio = Carbon::create($año, $mes, 1);
        $fechaFin = $fechaInicio->copy()->endOfMonth();
        // No establecer fecha de vencimiento fija, cada tarifa maneja su propio vencimiento
        $fechaVencimiento = $fechaInicio->copy()->addMonth()->day(15); // Valor por defecto para referencia
        
        $data = [
            'nombre' => $fechaInicio->locale('es')->format('F Y'),
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'fecha_vencimiento' => $fechaVencimiento,
            'estado' => 'abierto'
        ];
        
        if ($empresaId) {
            $data['empresa_id'] = $empresaId;
        }
        
        return PeriodoFacturacion::updateOrCreate(
            [
                'año' => $año,
                'mes' => $mes,
                'empresa_id' => $empresaId
            ],
            $data
        );
    }
}