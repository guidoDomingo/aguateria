<?php

namespace App\Services;

use App\Repositories\ReporteRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReporteService
{
    protected $reporteRepository;

    public function __construct(ReporteRepository $reporteRepository)
    {
        $this->reporteRepository = $reporteRepository;
    }

    /**
     * Generar reporte de ingresos
     */
    public function generarReporteIngresos(int $empresaId, string $fechaInicio, string $fechaFin): array
    {
        try {
            $reporte = $this->reporteRepository->reporteIngresos($empresaId, $fechaInicio, $fechaFin);
            
            // Agregar análisis adicional
            $reporte['analisis'] = [
                'periodo_dias' => Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin)) + 1,
                'promedio_diario' => $reporte['ingresos_por_dia']->avg() ?? 0,
                'mejor_dia' => [
                    'fecha' => $reporte['ingresos_por_dia']->keys()->first(),
                    'monto' => $reporte['ingresos_por_dia']->max() ?? 0
                ],
                'metodo_principal' => $reporte['ingresos_por_metodo']->keys()->first() ?? 'N/A'
            ];

            Log::info('Reporte de ingresos generado', [
                'empresa_id' => $empresaId,
                'periodo' => "{$fechaInicio} - {$fechaFin}",
                'total_ingresos' => $reporte['total_ingresos']
            ]);

            return [
                'success' => true,
                'data' => $reporte
            ];

        } catch (\Exception $e) {
            Log::error('Error al generar reporte de ingresos', [
                'empresa_id' => $empresaId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generar reporte de cartera
     */
    public function generarReporteCartera(int $empresaId): array
    {
        try {
            $reporte = $this->reporteRepository->reporteCartera($empresaId);
            
            // Agregar análisis
            $reporte['analisis'] = [
                'porcentaje_activos' => $reporte['total_clientes'] > 0 
                    ? ($reporte['clientes_activos'] / $reporte['total_clientes']) * 100 
                    : 0,
                'zona_principal' => $reporte['clientes_por_zona']->sortByDesc('cantidad')->first()?->zona ?? 'N/A',
                'tarifa_popular' => $reporte['clientes_por_tarifa']->sortByDesc('cantidad')->first()?->nombre ?? 'N/A'
            ];

            return [
                'success' => true,
                'data' => $reporte
            ];

        } catch (\Exception $e) {
            Log::error('Error al generar reporte de cartera', [
                'empresa_id' => $empresaId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generar reporte de facturación
     */
    public function generarReporteFacturacion(int $empresaId, string $periodo): array
    {
        try {
            $reporte = $this->reporteRepository->reporteFacturacion($empresaId, $periodo);
            
            // Agregar análisis
            $reporte['analisis'] = [
                'porcentaje_cobranza' => $reporte['monto_total_facturado'] > 0 
                    ? (($reporte['monto_total_facturado'] - ($reporte['facturas_por_estado']['pendiente']['monto'] ?? 0)) / $reporte['monto_total_facturado']) * 100 
                    : 0,
                'porcentaje_vencidas' => $reporte['total_facturas'] > 0 
                    ? ($reporte['facturas_vencidas'] / $reporte['total_facturas']) * 100 
                    : 0,
                'efectividad' => $reporte['total_facturas'] > 0 
                    ? ($reporte['facturas_pagadas'] / $reporte['total_facturas']) * 100 
                    : 0
            ];

            return [
                'success' => true,
                'data' => $reporte
            ];

        } catch (\Exception $e) {
            Log::error('Error al generar reporte de facturación', [
                'empresa_id' => $empresaId,
                'periodo' => $periodo,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generar reporte de morosidad
     */
    public function generarReporteMorosidad(int $empresaId): array
    {
        try {
            $reporte = $this->reporteRepository->reporteMorosidad($empresaId);
            
            // Agregar análisis de riesgo
            $reporte['analisis_riesgo'] = [
                'riesgo_alto' => $reporte['morosidad_por_rango']['90+']['cantidad'],
                'riesgo_medio' => $reporte['morosidad_por_rango']['61-90']['cantidad'],
                'riesgo_bajo' => $reporte['morosidad_por_rango']['1-30']['cantidad'] + $reporte['morosidad_por_rango']['31-60']['cantidad'],
                'concentracion_riesgo' => $reporte['monto_total_vencido'] > 0 
                    ? ($reporte['morosidad_por_rango']['90+']['monto'] / $reporte['monto_total_vencido']) * 100 
                    : 0
            ];

            // Top 10 clientes morosos
            $reporte['top_morosos'] = collect($reporte['detalle_clientes_morosos'])
                ->sortByDesc('monto_total_vencido')
                ->take(10)
                ->values();

            return [
                'success' => true,
                'data' => $reporte
            ];

        } catch (\Exception $e) {
            Log::error('Error al generar reporte de morosidad', [
                'empresa_id' => $empresaId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generar reporte de cortes
     */
    public function generarReporteCortes(int $empresaId, string $fechaInicio, string $fechaFin): array
    {
        try {
            $reporte = $this->reporteRepository->reporteCortes($empresaId, $fechaInicio, $fechaFin);
            
            // Agregar análisis
            $reporte['analisis'] = [
                'tasa_reconexion' => $reporte['total_cortes'] > 0 
                    ? ($reporte['cortes_reconectados'] / $reporte['total_cortes']) * 100 
                    : 0,
                'motivo_principal' => $reporte['cortes_por_motivo']->keys()->first() ?? 'N/A',
                'zona_mayor_cortes' => $reporte['cortes_por_zona']->keys()->first() ?? 'N/A',
                'promedio_cortes_dia' => Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin)) + 1 > 0 
                    ? $reporte['total_cortes'] / (Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin)) + 1)
                    : 0
            ];

            return [
                'success' => true,
                'data' => $reporte
            ];

        } catch (\Exception $e) {
            Log::error('Error al generar reporte de cortes', [
                'empresa_id' => $empresaId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estadísticas para dashboard
     */
    public function getEstadisticasDashboard(int $empresaId): array
    {
        try {
            $estadisticas = $this->reporteRepository->estadisticasDashboard($empresaId);
            
            // Agregar indicadores KPI
            $estadisticas['kpis'] = [
                'crecimiento_clientes' => 0, // Se podría calcular comparando con mes anterior
                'eficiencia_cobranza' => $estadisticas['facturacion_mes']['porcentaje_cobranza'],
                'indice_morosidad' => $estadisticas['clientes']['total'] > 0 
                    ? ($estadisticas['morosidad']['clientes_morosos'] / $estadisticas['clientes']['total']) * 100 
                    : 0
            ];

            return [
                'success' => true,
                'data' => $estadisticas
            ];

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas dashboard', [
                'empresa_id' => $empresaId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generar reporte personalizado
     */
    public function generarReportePersonalizado(int $empresaId, array $filtros): array
    {
        try {
            // Validar filtros obligatorios
            if (empty($filtros['fecha_inicio']) || empty($filtros['fecha_fin'])) {
                return [
                    'success' => false,
                    'message' => 'Las fechas de inicio y fin son obligatorias'
                ];
            }

            $reporte = $this->reporteRepository->reportePersonalizado($empresaId, $filtros);
            
            // Agregar metadatos del reporte
            $reporte['metadatos'] = [
                'fecha_generacion' => now()->format('Y-m-d H:i:s'),
                'filtros_aplicados' => $filtros,
                'periodo_consulta' => $filtros['fecha_inicio'] . ' - ' . $filtros['fecha_fin']
            ];

            Log::info('Reporte personalizado generado', [
                'empresa_id' => $empresaId,
                'filtros' => $filtros,
                'total_registros' => $reporte['total_registros']
            ]);

            return [
                'success' => true,
                'data' => $reporte
            ];

        } catch (\Exception $e) {
            Log::error('Error al generar reporte personalizado', [
                'empresa_id' => $empresaId,
                'filtros' => $filtros,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Exportar reporte a CSV
     */
    public function exportarReporte(string $tipoReporte, array $datos): string
    {
        $nombreArchivo = "reporte_{$tipoReporte}_" . date('Y-m-d_H-i-s') . '.csv';
        $rutaArchivo = storage_path('app/reportes/' . $nombreArchivo);
        
        // Crear directorio si no existe
        if (!file_exists(dirname($rutaArchivo))) {
            mkdir(dirname($rutaArchivo), 0755, true);
        }
        
        $archivo = fopen($rutaArchivo, 'w');
        
        // Agregar BOM para UTF-8
        fwrite($archivo, "\xEF\xBB\xBF");
        
        // Headers según tipo de reporte
        switch ($tipoReporte) {
            case 'ingresos':
                fputcsv($archivo, ['Fecha', 'Cliente', 'Factura', 'Monto', 'Método Pago']);
                foreach ($datos['detalle_pagos'] as $pago) {
                    fputcsv($archivo, [
                        $pago->fecha_pago,
                        $pago->cliente_nombre,
                        $pago->numero_factura,
                        $pago->monto,
                        $pago->metodo_pago
                    ]);
                }
                break;
                
            case 'morosidad':
                fputcsv($archivo, ['Cliente', 'Cédula', 'Facturas Vencidas', 'Monto Vencido', 'Días Mayor Vencimiento']);
                foreach ($datos['detalle_clientes_morosos'] as $cliente) {
                    fputcsv($archivo, [
                        $cliente['cliente_nombre'],
                        $cliente['cedula'],
                        $cliente['facturas_vencidas'],
                        $cliente['monto_total_vencido'],
                        $cliente['dias_mayor_vencimiento']
                    ]);
                }
                break;
                
            // Agregar más tipos según necesidad
        }
        
        fclose($archivo);
        
        return $nombreArchivo;
    }

    /**
     * Programar reporte automático
     */
    public function programarReporteAutomatico(int $empresaId, string $tipoReporte, string $frecuencia, array $parametros = []): array
    {
        try {
            // Lógica para programar reportes automáticos
            // Se puede usar Laravel Scheduler o colas
            
            Log::info('Reporte automático programado', [
                'empresa_id' => $empresaId,
                'tipo' => $tipoReporte,
                'frecuencia' => $frecuencia
            ]);
            
            return [
                'success' => true,
                'message' => 'Reporte automático programado exitosamente'
            ];
            
        } catch (\Exception $e) {
            Log::error('Error al programar reporte automático', [
                'empresa_id' => $empresaId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error al programar reporte: ' . $e->getMessage()
            ];
        }
    }
}