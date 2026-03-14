<?php

namespace App\Repositories;

use App\Models\Pago;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PagoRepository extends BaseRepository
{
    protected function getModel(): Model
    {
        return new Pago();
    }

    /**
     * Pagos del día
     */
    public function delDia(?Carbon $fecha = null): Collection
    {
        $fecha = $fecha ?? now();
        
        return $this->model->where('fecha_pago', $fecha->format('Y-m-d'))
                          ->where('estado', 'confirmado')
                          ->with(['cliente', 'factura', 'metodoPago', 'cobrador'])
                          ->orderBy('hora_pago', 'desc')
                          ->get();
    }

    /**
     * Pagos por rango de fechas
     */
    public function porRangoFechas(Carbon $fechaInicio, Carbon $fechaFin): Collection
    {
        return $this->model->whereBetween('fecha_pago', [$fechaInicio, $fechaFin])
                          ->where('estado', 'confirmado')
                          ->with(['cliente', 'factura', 'metodoPago', 'cobrador'])
                          ->orderBy('fecha_pago', 'desc')
                          ->get();
    }

    /**
     * Pagos por cliente
     */
    public function porCliente(int $clienteId): Collection
    {
        return $this->model->where('cliente_id', $clienteId)
                          ->with(['factura', 'metodoPago', 'usuario', 'recibo'])
                          ->orderBy('fecha_pago', 'desc')
                          ->get();
    }

    /**
     * Pagos por cobrador
     */
    public function porCobrador(int $cobradorId): Collection
    {
        return $this->model->where('cobrador_id', $cobradorId)
                          ->with(['cliente', 'factura', 'metodoPago'])
                          ->orderBy('fecha_pago', 'desc')
                          ->get();
    }

    /**
     * Obtener siguiente número de recibo
     */
    public function siguienteNumeroRecibo(): string
    {
        $ultimoPago = $this->model->selectRaw('CAST(SUBSTRING(numero_recibo, 4) AS UNSIGNED) as numero')
                                 ->orderBy('numero', 'desc')
                                 ->first();
        
        $siguienteNumero = $ultimoPago ? $ultimoPago->numero + 1 : 1;
        
        return 'REC' . str_pad($siguienteNumero, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Estadísticas de cobranza
     */
    public function estadisticasCobranza(): array
    {
        $hoy = now();
        
        return [
            'hoy' => [
                'cantidad' => $this->countBy('fecha_pago', $hoy->format('Y-m-d')),
                'monto' => $this->model->where('fecha_pago', $hoy->format('Y-m-d'))
                                      ->where('estado', 'confirmado')
                                      ->sum('monto_pagado'),
            ],
            'mes_actual' => [
                'cantidad' => $this->model->whereYear('fecha_pago', $hoy->year)
                                         ->whereMonth('fecha_pago', $hoy->month)
                                         ->where('estado', 'confirmado')
                                         ->count(),
                'monto' => $this->model->whereYear('fecha_pago', $hoy->year)
                                      ->whereMonth('fecha_pago', $hoy->month)
                                      ->where('estado', 'confirmado')
                                      ->sum('monto_pagado'),
            ],
        ];
    }

    /**
     * Resumen por método de pago
     */
    public function resumenPorMetodo(Carbon $fechaInicio, Carbon $fechaFin): Collection
    {
        return $this->model->selectRaw('
            metodo_pago_id,
            COUNT(*) as cantidad_pagos,
            SUM(monto_pagado) as total_cobrado
        ')
        ->whereBetween('fecha_pago', [$fechaInicio, $fechaFin])
        ->where('estado', 'confirmado')
        ->with('metodoPago')
        ->groupBy('metodo_pago_id')
        ->get();
    }

    /**
     * Pagos por cobrador en un período
     */
    public function porCobradorEnPeriodo(int $cobradorId, Carbon $fechaInicio, Carbon $fechaFin): array
    {
        $pagos = $this->model->where('cobrador_id', $cobradorId)
                            ->whereBetween('fecha_pago', [$fechaInicio, $fechaFin])
                            ->where('estado', 'confirmado')
                            ->get();
        
        return [
            'cantidad_pagos' => $pagos->count(),
            'monto_total' => $pagos->sum('monto_pagado'),
            'clientes_únicos' => $pagos->unique('cliente_id')->count(),
        ];
    }

    /**
     * Listar pagos con filtros y paginación
     */
    public function listarConFiltros(array $filtros, int $perPage = 15)
    {
        $query = $this->model->with(['cliente', 'factura', 'metodoPago', 'cobrador', 'usuario']);

        // Filtrar por empresa
        if (isset($filtros['empresa_id'])) {
            $query->whereHas('cliente', function ($q) use ($filtros) {
                $q->where('empresa_id', $filtros['empresa_id']);
            });
        }

        // Filtrar por búsqueda (número recibo, cliente)
        if (!empty($filtros['buscar'])) {
            $termino = $filtros['buscar'];
            $query->where(function ($q) use ($termino) {
                $q->where('numero_recibo', 'like', "%{$termino}%")
                  ->orWhereHas('cliente', function ($clienteQuery) use ($termino) {
                      $clienteQuery->where('nombre', 'like', "%{$termino}%")
                                  ->orWhere('apellido', 'like', "%{$termino}%")
                                  ->orWhere('codigo_cliente', 'like', "%{$termino}%");
                  })
                  ->orWhereHas('factura', function ($facturaQuery) use ($termino) {
                      $facturaQuery->where('numero_factura', 'like', "%{$termino}%");
                  });
            });
        }

        // Filtrar por método de pago
        if (!empty($filtros['metodo_pago_id'])) {
            $query->where('metodo_pago_id', $filtros['metodo_pago_id']);
        }

        // Filtrar por cobrador
        if (!empty($filtros['cobrador_id'])) {
            $query->where('cobrador_id', $filtros['cobrador_id']);
        }

        // Filtrar por rango de fechas
        if (!empty($filtros['fecha_desde'])) {
            $query->where('fecha_pago', '>=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->where('fecha_pago', '<=', $filtros['fecha_hasta']);
        }

        // Filtrar por estado
        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        } else {
            // Por defecto, solo mostrar pagos confirmados
            $query->where('estado', 'confirmado');
        }

        // Ordenar por fecha descendente
        $query->orderBy('fecha_pago', 'desc')
              ->orderBy('hora_pago', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Obtener estadísticas de pagos
     */
    public function getEstadisticas(array $filtros = []): array
    {
        $query = $this->model->where('estado', 'confirmado');

        // Aplicar filtros básicos
        if (isset($filtros['empresa_id'])) {
            $query->whereHas('cliente', function ($q) use ($filtros) {
                $q->where('empresa_id', $filtros['empresa_id']);
            });
        }

        if (!empty($filtros['fecha_desde'])) {
            $query->where('fecha_pago', '>=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->where('fecha_pago', '<=', $filtros['fecha_hasta']);
        }

        $estadisticas = $query->selectRaw('
            COUNT(*) as total_pagos,
            SUM(monto_pagado) as monto_total,
            AVG(monto_pagado) as promedio_pago,
            COUNT(DISTINCT cliente_id) as clientes_unicos,
            COUNT(DISTINCT DATE(fecha_pago)) as dias_con_pagos
        ')->first();

        return $estadisticas->toArray();
    }

    /**
     * Buscar pagos por término
     */
    public function buscar(string $termino, int $perPage = 15)
    {
        return $this->model->where(function ($query) use ($termino) {
            $query->where('numero_recibo', 'like', "%{$termino}%")
                  ->orWhereHas('cliente', function ($q) use ($termino) {
                      $q->where('nombre', 'like', "%{$termino}%")
                        ->orWhere('apellido', 'like', "%{$termino}%")
                        ->orWhere('codigo_cliente', 'like', "%{$termino}%");
                  })
                  ->orWhereHas('factura', function ($q) use ($termino) {
                      $q->where('numero_factura', 'like', "%{$termino}%");
                  });
        })
        ->with(['cliente', 'factura', 'metodoPago', 'cobrador'])
        ->orderBy('fecha_pago', 'desc')
        ->paginate($perPage);
    }

    /**
     * Obtener estadísticas de pagos para un período específico
     */
    public function getEstadisticasPeriodo(int $empresaId, $fechaDesde = null, $fechaHasta = null): array
    {
        $query = $this->model->where('estado', 'confirmado')
            ->whereHas('cliente', function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId);
            });

        if ($fechaDesde) {
            $query->where('fecha_pago', '>=', $fechaDesde);
        }

        if ($fechaHasta) {
            $query->where('fecha_pago', '<=', $fechaHasta);
        }

        $estadisticas = $query->selectRaw('
            COUNT(*) as total_pagos,
            SUM(monto_pagado) as monto_total,
            AVG(monto_pagado) as promedio_pago,
            COUNT(DISTINCT cliente_id) as clientes_unicos
        ')->first();

        return [
            'total_pagos' => (int)$estadisticas->total_pagos,
            'monto_total' => (float)$estadisticas->monto_total,
            'promedio_pago' => (float)$estadisticas->promedio_pago,
            'clientes_unicos' => (int)$estadisticas->clientes_unicos
        ];
    }

    /**
     * Obtener estadísticas de pagos del día actual
     */
    public function getEstadisticasHoy(int $empresaId): array
    {
        $hoy = now()->format('Y-m-d');
        return $this->getEstadisticasPeriodo($empresaId, $hoy, $hoy);
    }
}