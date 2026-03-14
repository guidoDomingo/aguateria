<?php

namespace App\Repositories;

use App\Models\Factura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class FacturaRepository extends BaseRepository
{
    protected function getModel(): Model
    {
        return new Factura();
    }

    /**
     * Facturas del mes
     */
    public function delMes(int $año, int $mes): Collection
    {
        return $this->model->whereHas('periodo', function ($query) use ($año, $mes) {
            $query->where('año', $año)->where('mes', $mes);
        })->with(['cliente', 'periodo', 'detalles'])->get();
    }

    /**
     * Facturas pendientes
     */
    public function pendientes(): Collection
    {
        return $this->model->whereIn('estado', ['pendiente', 'parcial'])
                          ->with(['cliente', 'detalles'])
                          ->orderBy('fecha_vencimiento')
                          ->get();
    }

    /**
     * Facturas vencidas
     */
    public function vencidas(): Collection
    {
        return $this->model->where('estado', 'vencido')
                          ->with(['cliente', 'detalles'])
                          ->orderBy('fecha_vencimiento')
                          ->get();
    }

    /**
     * Facturas por cliente
     */
    public function porCliente(int $clienteId): Collection
    {
        return $this->model->where('cliente_id', $clienteId)
                          ->with(['periodo', 'detalles', 'pagos'])
                          ->orderBy('fecha_emision', 'desc')
                          ->get();
    }

    /**
     * Facturas por rango de fechas
     */
    public function porRangoFechas(Carbon $fechaInicio, Carbon $fechaFin): Collection
    {
        return $this->model->whereBetween('fecha_emision', [$fechaInicio, $fechaFin])
                          ->with(['cliente', 'detalles'])
                          ->orderBy('fecha_emision', 'desc')
                          ->get();
    }

    /**
     * Obtener siguiente número de factura
     */
    public function siguienteNumeroFactura(string $serie = '001'): array
    {
        $ultimaFactura = $this->model->where('serie', $serie)
                                    ->orderBy('numero', 'desc')
                                    ->first();
        
        $siguienteNumero = $ultimaFactura ? $ultimaFactura->numero + 1 : 1;
        
        return [
            'serie' => $serie,
            'numero' => $siguienteNumero,
            'numero_factura' => $serie . '-' . str_pad($siguienteNumero, 8, '0', STR_PAD_LEFT)
        ];
    }

    /**
     * Estadísticas de facturación
     */
    public function estadisticas(): array
    {
        $facturas = $this->model->selectRaw('
            COUNT(*) as total,
            COUNT(CASE WHEN estado = "pendiente" THEN 1 END) as pendientes,
            COUNT(CASE WHEN estado = "pagado" THEN 1 END) as pagadas,
            COUNT(CASE WHEN estado = "vencido" THEN 1 END) as vencidas,
            COUNT(CASE WHEN estado = "parcial" THEN 1 END) as parciales,
            SUM(total) as monto_total,
            SUM(saldo_pendiente) as saldo_pendiente_total
        ')->first();
        
        return $facturas ? $facturas->toArray() : [];
    }

    /**
     * Facturas próximas a vencer
     */
    public function proximasAVencer(int $dias = 7): Collection
    {
        return $this->model->whereIn('estado', ['pendiente', 'parcial'])
                          ->where('fecha_vencimiento', '<=', now()->addDays($dias))
                          ->where('fecha_vencimiento', '>=', now())
                          ->with(['cliente'])
                          ->orderBy('fecha_vencimiento')
                          ->get();
    }

    /**
     * Aplicar mora a facturas vencidas
     */
    public function aplicarMoras(): int
    {
        $facturasVencidas = $this->model->where('estado', 'pendiente')
                                       ->where('fecha_vencimiento', '<', now())
                                       ->with(['cliente.tarifa'])
                                       ->get();
        
        $actualizadas = 0;
        
        foreach ($facturasVencidas as $factura) {
            if ($factura->cliente && $factura->cliente->puedeGenerarMora()) {
                $mora = $factura->calcularMora();
                if ($mora > 0) {
                    $factura->mora = $mora;
                    $factura->total = $factura->subtotal + $mora;
                    $factura->saldo_pendiente = $factura->total - ($factura->total - $factura->saldo_pendiente);
                    $factura->estado = 'vencido';
                    $factura->save();
                    $actualizadas++;
                }
            }
        }
        
        return $actualizadas;
    }

    /**
     * Listar facturas con filtros y paginación
     */
    public function listarConFiltros(array $filtros, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['cliente', 'periodo']);

        // Filtrar por empresa
        if (isset($filtros['empresa_id'])) {
            $query->where('empresa_id', $filtros['empresa_id']);
        }

        // Filtrar por búsqueda (número factura, cliente)
        if (!empty($filtros['buscar'])) {
            $termino = $filtros['buscar'];
            $query->where(function ($q) use ($termino) {
                $q->where('numero_factura', 'like', "%{$termino}%")
                  ->orWhereHas('cliente', function ($clienteQuery) use ($termino) {
                      $clienteQuery->where('nombre', 'like', "%{$termino}%")
                                  ->orWhere('apellido', 'like', "%{$termino}%")
                                  ->orWhere('codigo_cliente', 'like', "%{$termino}%");
                  });
            });
        }

        // Filtrar por estado
        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        // Filtrar por período
        if (!empty($filtros['periodo_id'])) {
            $query->where('periodo_id', $filtros['periodo_id']);
        }

        // Filtrar por cliente específico
        if (!empty($filtros['cliente_id'])) {
            $query->where('cliente_id', $filtros['cliente_id']);
        }

        // Solo facturas vencidas
        if (!empty($filtros['solo_vencidas'])) {
            $query->where('estado', 'vencido');
        }

        // Ordenar por fecha de emisión descendente
        $query->orderBy('fecha_emision', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Obtener estadísticas de un período específico
     */
    public function getEstadisticasPeriodo(int $periodoId): array
    {
        return $this->model->where('periodo_id', $periodoId)
            ->selectRaw('
                COUNT(*) as total_facturas,
                COUNT(CASE WHEN estado = "pendiente" THEN 1 END) as pendientes,
                COUNT(CASE WHEN estado = "pagado" THEN 1 END) as pagadas,
                COUNT(CASE WHEN estado = "vencido" THEN 1 END) as vencidas,
                COUNT(CASE WHEN estado = "parcial" THEN 1 END) as parciales,
                COUNT(CASE WHEN estado = "anulado" THEN 1 END) as anuladas,
                SUM(CASE WHEN estado != "anulado" THEN total ELSE 0 END) as monto_total,
                SUM(CASE WHEN estado != "anulado" THEN saldo_pendiente ELSE 0 END) as saldo_pendiente_total,
                SUM(CASE WHEN estado = "pagado" THEN total ELSE 0 END) as monto_cobrado,
                AVG(CASE WHEN estado != "anulado" THEN total ELSE NULL END) as promedio_factura
            ')
            ->first()
            ->toArray();
    }

    /**
     * Buscar facturas por término
     */
    public function buscar(string $termino, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where(function ($query) use ($termino) {
            $query->where('numero_factura', 'like', "%{$termino}%")
                  ->orWhereHas('cliente', function ($q) use ($termino) {
                      $q->where('nombre', 'like', "%{$termino}%")
                        ->orWhere('apellido', 'like', "%{$termino}%")
                        ->orWhere('codigo_cliente', 'like', "%{$termino}%");
                  });
        })
        ->with(['cliente', 'periodo'])
        ->orderBy('fecha_emision', 'desc')
        ->paginate($perPage);
    }
}