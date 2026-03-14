<?php

namespace App\Services;

use App\Repositories\PeriodoFacturacionRepository;
use App\Repositories\ClienteRepository;
use App\Repositories\FacturaRepository;
use App\Models\PeriodoFacturacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PeriodoFacturacionService
{
    protected $periodoRepository;
    protected $clienteRepository;
    protected $facturaRepository;

    public function __construct(
        PeriodoFacturacionRepository $periodoRepository,
        ClienteRepository $clienteRepository,
        FacturaRepository $facturaRepository
    ) {
        $this->periodoRepository = $periodoRepository;
        $this->clienteRepository = $clienteRepository;
        $this->facturaRepository = $facturaRepository;
    }

    /**
     * Crear nuevo período de facturación
     */
    public function crearPeriodo(int $empresaId, int $año, int $mes, array $datos = []): array
    {
        try {
            // Validar que no exista el período
            if ($this->periodoRepository->existePeriodo($empresaId, $año, $mes)) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un período para este mes y año'
                ];
            }

            DB::beginTransaction();

            $periodo = $this->periodoRepository->crearPeriodoMensual($empresaId, $año, $mes);

            // Si se proporcionan datos adicionales, actualizarlos
            if (!empty($datos)) {
                $periodo->update($datos);
            }

            DB::commit();

            Log::info('Período de facturación creado', [
                'empresa_id' => $empresaId,
                'periodo_id' => $periodo->id,
                'año' => $año,
                'mes' => $mes
            ]);

            return [
                'success' => true,
                'message' => 'Período creado exitosamente',
                'periodo' => $periodo
            ];

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al crear período de facturación', [
                'error' => $e->getMessage(),
                'empresa_id' => $empresaId
            ]);

            return [
                'success' => false,
                'message' => 'Error al crear el período: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Procesar facturación masiva del período
     */
    public function procesarFacturacionMasiva(int $periodoId): array
    {
        try {
            $periodo = $this->periodoRepository->find($periodoId);
            
            if (!$periodo) {
                return [
                    'success' => false,
                    'message' => 'Período no encontrado'
                ];
            }

            if (!$periodo->puedeFacturar()) {
                return [
                    'success' => false,
                    'message' => 'El período no se puede facturar en su estado actual'
                ];
            }

            DB::beginTransaction();

            // Obtener clientes activos
            $clientes = $this->clienteRepository->getClientesActivos($periodo->empresa_id);
            $facturasGeneradas = 0;
            $montoTotal = 0;
            $errores = [];

            foreach ($clientes as $cliente) {
                try {
                    // Generar factura para el cliente
                    $facturaData = [
                        'cliente_id' => $cliente->id,
                        'periodo_id' => $periodo->id,
                        'periodo' => $periodo->nombre,
                        'fecha_emision' => now(),
                        'fecha_vencimiento' => $periodo->fecha_vencimiento,
                        'monto_base' => $cliente->tarifa->precio_base,
                        'consumo' => 0, // Por defecto, se puede actualizar después
                        'monto_consumo' => 0,
                        'otros_cargos' => 0,
                        'descuentos' => 0,
                        'estado' => 'pendiente'
                    ];

                    $facturaData['monto_total'] = $facturaData['monto_base'] + 
                                                 $facturaData['monto_consumo'] + 
                                                 $facturaData['otros_cargos'] - 
                                                 $facturaData['descuentos'];

                    $factura = $this->facturaRepository->create($facturaData);
                    $facturasGeneradas++;
                    $montoTotal += $factura->monto_total;

                } catch (\Exception $e) {
                    $errores[] = "Cliente {$cliente->nombre}: " . $e->getMessage();
                    Log::error('Error al generar factura', [
                        'cliente_id' => $cliente->id,
                        'periodo_id' => $periodo->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Actualizar estadísticas del período
            $this->periodoRepository->marcarFacturado($periodo->id, $facturasGeneradas, $montoTotal);

            DB::commit();

            Log::info('Facturación masiva procesada', [
                'periodo_id' => $periodo->id,
                'facturas_generadas' => $facturasGeneradas,
                'monto_total' => $montoTotal
            ]);

            return [
                'success' => true,
                'message' => 'Facturación procesada exitosamente',
                'estadisticas' => [
                    'total_clientes' => $clientes->count(),
                    'facturas_generadas' => $facturasGeneradas,
                    'monto_total' => $montoTotal,
                    'errores' => count($errores)
                ],
                'errores' => $errores
            ];

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error en facturación masiva', [
                'periodo_id' => $periodoId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error en el procesamiento: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener período actual o crearlo si no existe
     */
    public function obtenerPeriodoActual(int $empresaId): PeriodoFacturacion
    {
        $fechaActual = Carbon::now();
        $año = $fechaActual->year;
        $mes = $fechaActual->month;
        
        $periodo = $this->periodoRepository->obtenerPorFecha($empresaId, $año, $mes);
        
        if (!$periodo) {
            $resultado = $this->crearPeriodo($empresaId, $año, $mes);
            if ($resultado['success']) {
                $periodo = $resultado['periodo'];
            } else {
                throw new \Exception('No se pudo crear el período actual: ' . $resultado['message']);
            }
        }
        
        return $periodo;
    }
    
    /**
     * Obtener período del mes siguiente
     */
    public function obtenerPeriodoMesSiguiente(int $empresaId): PeriodoFacturacion
    {
        $fechaSiguiente = Carbon::now()->addMonth();
        $año = $fechaSiguiente->year;
        $mes = $fechaSiguiente->month;
        
        $periodo = $this->periodoRepository->obtenerPorFecha($empresaId, $año, $mes);
        
        if (!$periodo) {
            $resultado = $this->crearPeriodo($empresaId, $año, $mes);
            if ($resultado['success']) {
                $periodo = $resultado['periodo'];
            } else {
                throw new \Exception('No se pudo crear el período del mes siguiente: ' . $resultado['message']);
            }
        }
        
        return $periodo;
    }
    
    /**
     * Verificar si el período actual está listo para facturar
     */
    public function periodoActualListoParaFacturar(int $empresaId): array
    {
        try {
            $periodo = $this->obtenerPeriodoActual($empresaId);
            
            if ($periodo->estado === 'cerrado') {
                return [
                    'listo' => false,
                    'mensaje' => 'El período actual ya está cerrado',
                    'periodo' => $periodo
                ];
            }
            
            return [
                'listo' => true,
                'mensaje' => 'Período actual disponible para facturar',
                'periodo' => $periodo
            ];
            
        } catch (\Exception $e) {
            return [
                'listo' => false,
                'mensaje' => 'Error: ' . $e->getMessage(),
                'periodo' => null
            ];
        }
    }

    /**
     * Cerrar período de facturación
     */
    public function cerrarPeriodo(int $periodoId, string $motivo = ''): array
    {
        try {
            $periodo = $this->periodoRepository->find($periodoId);
            
            if (!$periodo) {
                return [
                    'success' => false,
                    'message' => 'Período no encontrado'
                ];
            }

            if ($periodo->estado !== 'abierto') {
                return [
                    'success' => false,
                    'message' => 'Solo se pueden cerrar períodos abiertos'
                ];
            }

            DB::beginTransaction();

            $periodo->update([
                'estado' => 'cerrado',
                'observaciones' => $motivo
            ]);

            DB::commit();

            Log::info('Período cerrado', [
                'periodo_id' => $periodo->id,
                'motivo' => $motivo
            ]);

            return [
                'success' => true,
                'message' => 'Período cerrado exitosamente'
            ];

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al cerrar período', [
                'periodo_id' => $periodoId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al cerrar el período: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reabrir período cerrado
     */
    public function reabrirPeriodo(int $periodoId, string $motivo = ''): array
    {
        try {
            $periodo = $this->periodoRepository->find($periodoId);
            
            if (!$periodo) {
                return [
                    'success' => false,
                    'message' => 'Período no encontrado'
                ];
            }

            if ($periodo->estado !== 'cerrado') {
                return [
                    'success' => false,
                    'message' => 'Solo se pueden reabrir períodos cerrados'
                ];
            }

            DB::beginTransaction();

            $periodo->update([
                'estado' => 'abierto',
                'observaciones' => $motivo
            ]);

            DB::commit();

            Log::info('Período reabierto', [
                'periodo_id' => $periodo->id,
                'motivo' => $motivo
            ]);

            return [
                'success' => true,
                'message' => 'Período reabierto exitosamente'
            ];

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al reabrir período', [
                'periodo_id' => $periodoId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al reabrir el período: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener próximos períodos sugeridos
     */
    public function getPeriodosSugeridos(int $empresaId, int $cantidad = 3): array
    {
        $proximo = $this->periodoRepository->getProximoPeriodo($empresaId);
        $sugeridos = [];
        
        $año = $proximo['año'];
        $mes = $proximo['mes'];
        
        for ($i = 0; $i < $cantidad; $i++) {
            $nombre_mes = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];
            
            $sugeridos[] = [
                'año' => $año,
                'mes' => $mes,
                'nombre' => $nombre_mes[$mes] . ' ' . $año,
                'existe' => $this->periodoRepository->existePeriodo($empresaId, $año, $mes)
            ];
            
            $mes++;
            if ($mes > 12) {
                $mes = 1;
                $año++;
            }
        }
        
        return $sugeridos;
    }

    /**
     * Obtener estadísticas del período
     */
    public function getEstadisticas(int $empresaId): array
    {
        return $this->periodoRepository->getEstadisticas($empresaId);
    }

    /**
     * Crear períodos automáticamente
     */
    public function crearPeriodosAutomaticos(int $empresaId, int $mesesAdelante = 2): array
    {
        $creados = [];
        $errores = [];
        
        $proximo = $this->periodoRepository->getProximoPeriodo($empresaId);
        $año = $proximo['año'];
        $mes = $proximo['mes'];
        
        for ($i = 0; $i < $mesesAdelante; $i++) {
            if (!$this->periodoRepository->existePeriodo($empresaId, $año, $mes)) {
                $resultado = $this->crearPeriodo($empresaId, $año, $mes);
                
                if ($resultado['success']) {
                    $creados[] = $resultado['periodo'];
                } else {
                    $errores[] = "Mes {$mes}/{$año}: " . $resultado['message'];
                }
            }
            
            $mes++;
            if ($mes > 12) {
                $mes = 1;
                $año++;
            }
        }
        
        return [
            'periodos_creados' => count($creados),
            'errores' => count($errores),
            'detalle_creados' => $creados,
            'detalle_errores' => $errores
        ];
    }
}