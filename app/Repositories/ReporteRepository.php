<?php

namespace App\Repositories;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Pago;
use App\Models\CorteServicio;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReporteRepository
{
    /**
     * Reporte de ingresos por período
     */
    public function reporteIngresos(int $empresaId, string $fechaInicio, string $fechaFin): array
    {
        $pagos = Pago::join('facturas', 'pagos.factura_id', '=', 'facturas.id')
            ->join('clientes', 'facturas.cliente_id', '=', 'clientes.id')
            ->where('clientes.empresa_id', $empresaId)
            ->whereBetween('pagos.fecha_pago', [$fechaInicio, $fechaFin])
            ->select([
                'pagos.fecha_pago',
                'pagos.monto',
                'pagos.metodo_pago',
                'clientes.nombre as cliente_nombre',
                'facturas.numero_factura'
            ])
            ->orderBy('pagos.fecha_pago', 'desc')
            ->get();

        $totalIngresos = $pagos->sum('monto');
        $ingresosPorMetodo = $pagos->groupBy('metodo_pago')->map(function ($items) {
            return $items->sum('monto');
        });

        $ingresosPorDia = $pagos->groupBy(function ($item) {
            return Carbon::parse($item->fecha_pago)->format('Y-m-d');
        })->map(function ($items) {
            return $items->sum('monto');
        });

        return [
            'total_ingresos' => $totalIngresos,
            'cantidad_pagos' => $pagos->count(),
            'promedio_pago' => $pagos->count() > 0 ? $totalIngresos / $pagos->count() : 0,
            'ingresos_por_metodo' => $ingresosPorMetodo,
            'ingresos_por_dia' => $ingresosPorDia,
            'detalle_pagos' => $pagos
        ];
    }

    /**
     * Reporte de cartera de clientes
     */
    public function reporteCartera(int $empresaId): array
    {
        $clientesEstados = Cliente::where('empresa_id', $empresaId)
            ->select('estado', DB::raw('COUNT(*) as cantidad'))
            ->groupBy('estado')
            ->get()
            ->keyBy('estado');

        $clientesPorZona = Cliente::where('empresa_id', $empresaId)
            ->select('zona', DB::raw('COUNT(*) as cantidad'))
            ->groupBy('zona')
            ->get();

        $clientesPorTarifa = Cliente::join('tarifas', 'clientes.tarifa_id', '=', 'tarifas.id')
            ->where('clientes.empresa_id', $empresaId)
            ->select('tarifas.nombre', DB::raw('COUNT(*) as cantidad'))
            ->groupBy('tarifas.id', 'tarifas.nombre')
            ->get();

        return [
            'total_clientes' => Cliente::where('empresa_id', $empresaId)->count(),
            'clientes_activos' => $clientesEstados['activo']->cantidad ?? 0,
            'clientes_suspendidos' => $clientesEstados['suspendido']->cantidad ?? 0,
            'clientes_por_zona' => $clientesPorZona,
            'clientes_por_tarifa' => $clientesPorTarifa
        ];
    }

    /**
     * Reporte de facturación
     */
    public function reporteFacturacion(int $empresaId, string $periodo): array
    {
        $facturas = Factura::join('clientes', 'facturas.cliente_id', '=', 'clientes.id')
            ->where('clientes.empresa_id', $empresaId)
            ->where('facturas.periodo', $periodo)
            ->get();

        $facturasVencidas = $facturas->where('fecha_vencimiento', '<', now())
            ->where('estado', '!=', 'pagada');

        $facturasPorEstado = $facturas->groupBy('estado')->map(function ($items) {
            return [
                'cantidad' => $items->count(),
                'monto' => $items->sum('monto_total')
            ];
        });

        return [
            'total_facturas' => $facturas->count(),
            'monto_total_facturado' => $facturas->sum('monto_total'),
            'facturas_pagadas' => $facturas->where('estado', 'pagada')->count(),
            'facturas_pendientes' => $facturas->where('estado', 'pendiente')->count(),
            'facturas_vencidas' => $facturasVencidas->count(),
            'monto_vencido' => $facturasVencidas->sum('monto_total'),
            'facturas_por_estado' => $facturasPorEstado,
            'promedio_factura' => $facturas->count() > 0 ? $facturas->sum('monto_total') / $facturas->count() : 0
        ];
    }

    /**
     * Reporte de morosidad
     */
    public function reporteMorosidad(int $empresaId): array
    {
        $facturasVencidas = Factura::join('clientes', 'facturas.cliente_id', '=', 'clientes.id')
            ->where('clientes.empresa_id', $empresaId)
            ->where('facturas.fecha_vencimiento', '<', now())
            ->where('facturas.estado', '!=', 'pagada')
            ->select([
                'facturas.*',
                'clientes.nombre as cliente_nombre',
                'clientes.cedula',
                DB::raw('DATEDIFF(CURDATE(), facturas.fecha_vencimiento) as dias_vencido')
            ])
            ->get();

        $morosidadPorRango = [
            '1-30' => $facturasVencidas->whereBetween('dias_vencido', [1, 30]),
            '31-60' => $facturasVencidas->whereBetween('dias_vencido', [31, 60]),
            '61-90' => $facturasVencidas->whereBetween('dias_vencido', [61, 90]),
            '90+' => $facturasVencidas->where('dias_vencido', '>', 90)
        ];

        $clientesMorosos = $facturasVencidas->groupBy('cliente_id')->map(function ($facturas, $clienteId) {
            $cliente = $facturas->first();
            return [
                'cliente_id' => $clienteId,
                'cliente_nombre' => $cliente->cliente_nombre,
                'cedula' => $cliente->cedula,
                'facturas_vencidas' => $facturas->count(),
                'monto_total_vencido' => $facturas->sum('monto_total'),
                'dias_mayor_vencimiento' => $facturas->max('dias_vencido')
            ];
        })->values();

        return [
            'total_facturas_vencidas' => $facturasVencidas->count(),
            'monto_total_vencido' => $facturasVencidas->sum('monto_total'),
            'clientes_morosos' => $clientesMorosos->count(),
            'morosidad_por_rango' => [
                '1-30' => [
                    'cantidad' => $morosidadPorRango['1-30']->count(),
                    'monto' => $morosidadPorRango['1-30']->sum('monto_total')
                ],
                '31-60' => [
                    'cantidad' => $morosidadPorRango['31-60']->count(),
                    'monto' => $morosidadPorRango['31-60']->sum('monto_total')
                ],
                '61-90' => [
                    'cantidad' => $morosidadPorRango['61-90']->count(),
                    'monto' => $morosidadPorRango['61-90']->sum('monto_total')
                ],
                '90+' => [
                    'cantidad' => $morosidadPorRango['90+']->count(),
                    'monto' => $morosidadPorRango['90+']->sum('monto_total')
                ]
            ],
            'detalle_clientes_morosos' => $clientesMorosos
        ];
    }

    /**
     * Reporte de cortes de servicio
     */
    public function reporteCortes(int $empresaId, string $fechaInicio, string $fechaFin): array
    {
        $cortes = CorteServicio::join('clientes', 'cortes_servicio.cliente_id', '=', 'clientes.id')
            ->where('clientes.empresa_id', $empresaId)
            ->whereBetween('cortes_servicio.fecha_corte', [$fechaInicio, $fechaFin])
            ->select([
                'cortes_servicio.*',
                'clientes.nombre as cliente_nombre',
                'clientes.cedula',
                'clientes.direccion'
            ])
            ->get();

        $cortesPorMotivo = $cortes->groupBy('motivo_corte')->map(function ($items) {
            return $items->count();
        });

        $cortesPorZona = $cortes->join('clientes', 'cortes_servicio.cliente_id', '=', 'clientes.id')
            ->groupBy('clientes.zona')->map(function ($items) {
                return $items->count();
            });

        return [
            'total_cortes' => $cortes->count(),
            'cortes_pendientes' => $cortes->where('estado', 'cortado')->count(),
            'cortes_reconectados' => $cortes->where('estado', 'reconectado')->count(),
            'cortes_por_motivo' => $cortesPorMotivo,
            'cortes_por_zona' => $cortesPorZona,
            'detalle_cortes' => $cortes
        ];
    }

    /**
     * Dashboard - Estadísticas generales
     */
    public function estadisticasDashboard(int $empresaId): array
    {
        $hoy = now();
        $mesActual = $hoy->format('Y-m');
        
        // Clientes
        $totalClientes = Cliente::where('empresa_id', $empresaId)->count();
        $clientesActivos = Cliente::where('empresa_id', $empresaId)->where('estado', 'activo')->count();
        
        // Facturas del mes actual
        $facturasMes = Factura::where('empresa_id', $empresaId)
            ->whereRaw('DATE_FORMAT(fecha_emision, "%Y-%m") = ?', [$mesActual])
            ->get();

        // Pagos del mes actual
        $pagosMes = Pago::where('empresa_id', $empresaId)
            ->whereRaw('DATE_FORMAT(fecha_pago, "%Y-%m") = ?', [$mesActual])
            ->sum('monto_pagado');

        // Facturas vencidas
        $facturasVencidas = Factura::where('empresa_id', $empresaId)
            ->where('fecha_vencimiento', '<', $hoy)
            ->where('estado', '!=', 'pagado')
            ->get();

        return [
            'clientes' => [
                'total' => $totalClientes,
                'activos' => $clientesActivos,
                'suspendidos' => $totalClientes - $clientesActivos
            ],
            'facturacion_mes' => [
                'cantidad_facturas' => $facturasMes->count(),
                'monto_facturado' => $facturasMes->sum('total'),
                'monto_cobrado' => $pagosMes,
                'porcentaje_cobranza' => $facturasMes->sum('total') > 0
                    ? ($pagosMes / $facturasMes->sum('total')) * 100
                    : 0
            ],
            'morosidad' => [
                'facturas_vencidas' => $facturasVencidas->count(),
                'monto_vencido' => $facturasVencidas->sum('total'),
                'clientes_morosos' => $facturasVencidas->unique('cliente_id')->count()
            ]
        ];
    }

    /**
     * Reporte personalizado con filtros
     */
    public function reportePersonalizado(int $empresaId, array $filtros): array
    {
        $query = Factura::join('clientes', 'facturas.cliente_id', '=', 'clientes.id')
            ->leftJoin('pagos', 'facturas.id', '=', 'pagos.factura_id')
            ->where('clientes.empresa_id', $empresaId);

        // Aplicar filtros
        if (isset($filtros['fecha_inicio']) && isset($filtros['fecha_fin'])) {
            $query->whereBetween('facturas.fecha_emision', [$filtros['fecha_inicio'], $filtros['fecha_fin']]);
        }

        if (isset($filtros['estado_factura'])) {
            $query->where('facturas.estado', $filtros['estado_factura']);
        }

        if (isset($filtros['zona'])) {
            $query->where('clientes.zona', $filtros['zona']);
        }

        if (isset($filtros['tarifa_id'])) {
            $query->where('clientes.tarifa_id', $filtros['tarifa_id']);
        }

        $resultados = $query->select([
            'facturas.*',
            'clientes.nombre as cliente_nombre',
            'clientes.cedula',
            'clientes.zona',
            'pagos.fecha_pago',
            'pagos.monto as monto_pagado',
            'pagos.metodo_pago'
        ])->get();

        return [
            'total_registros' => $resultados->count(),
            'monto_total_facturas' => $resultados->sum('monto_total'),
            'monto_total_pagos' => $resultados->sum('monto_pagado'),
            'resultados' => $resultados
        ];
    }
}