<?php

namespace App\Repositories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ClienteRepository extends BaseRepository
{
    protected function getModel(): Model
    {
        return new Cliente();
    }

    /**
     * Buscar clientes por término de búsqueda
     */
    public function buscar(string $termino, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where(function ($query) use ($termino) {
            $query->where('nombre', 'like', "%{$termino}%")
                  ->orWhere('apellido', 'like', "%{$termino}%")
                  ->orWhere('razon_social', 'like', "%{$termino}%")
                  ->orWhere('cedula', 'like', "%{$termino}%")
                  ->orWhere('codigo_cliente', 'like', "%{$termino}%");
        })->with(['barrio', 'tarifa', 'cobrador'])->paginate($perPage);
    }

    /**
     * Obtener clientes morosos
     */
    public function morosos(): Collection
    {
        return $this->model->whereHas('facturas', function ($query) {
            $query->where('estado', 'vencido');
        })->with(['facturas' => function ($query) {
            $query->where('estado', 'vencido');
        }])->get();
    }

    /**
     * Clientes por barrio
     */
    public function porBarrio(int $barrioId): Collection
    {
        return $this->model->where('barrio_id', $barrioId)
                          ->with(['tarifa', 'cobrador'])
                          ->get();
    }

    /**
     * Clientes por cobrador
     */
    public function porCobrador(int $cobradorId): Collection
    {
        return $this->model->where('cobrador_id', $cobradorId)
                          ->with(['barrio', 'tarifa'])
                          ->get();
    }

    /**
     * Clientes activos
     */
    public function activos(): Collection
    {
        return $this->model->where('estado', 'activo')
                          ->with(['barrio', 'tarifa', 'cobrador'])
                          ->get();
    }

    /**
     * Obtener siguiente código de cliente
     */
    public function siguienteCodigoCliente(): string
    {
        $ultimoNumero = $this->model->selectRaw('CAST(SUBSTRING(codigo_cliente, 4) AS UNSIGNED) as numero')
                                   ->orderBy('numero', 'desc')
                                   ->first();
        
        $siguienteNumero = $ultimoNumero ? $ultimoNumero->numero + 1 : 1;
        
        return 'CLI' . str_pad($siguienteNumero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Estadísticas de clientes
     */
    public function estadisticas(): array
    {
        return [
            'total' => $this->count(),
            'activos' => $this->countBy('estado', 'activo'),
            'suspendidos' => $this->countBy('estado', 'suspendido'),
            'cortados' => $this->countBy('estado', 'cortado'),
            'retirados' => $this->countBy('estado', 'retirado'),
            'morosos' => $this->model->whereHas('facturas', function ($q) {
                $q->where('estado', 'vencido');
            })->count()
        ];
    }

    /**
     * Clientes con facturas vencidas
     */
    public function conFacturasVencidas(int $diasMinimos = 0): Collection
    {
        return $this->model->whereHas('facturas', function ($query) use ($diasMinimos) {
            $query->where('estado', 'vencido');
            if ($diasMinimos > 0) {
                $query->where('fecha_vencimiento', '<=', now()->subDays($diasMinimos));
            }
        })->with(['facturas' => function ($query) {
            $query->where('estado', 'vencido')->orderBy('fecha_vencimiento');
        }])->get();
    }

    /**
     * Listar clientes con filtros y paginación
     */
    public function listarConFiltros(array $filtros, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->query();

        // Filtrar por empresa
        if (isset($filtros['empresa_id'])) {
            $query->where('empresa_id', $filtros['empresa_id']);
        }

        // Filtrar por búsqueda
        if (!empty($filtros['buscar'])) {
            $termino = $filtros['buscar'];
            $query->where(function ($q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%")
                  ->orWhere('apellido', 'like', "%{$termino}%")
                  ->orWhere('razon_social', 'like', "%{$termino}%")
                  ->orWhere('cedula', 'like', "%{$termino}%")
                  ->orWhere('codigo_cliente', 'like', "%{$termino}%")
                  ->orWhere('direccion', 'like', "%{$termino}%");
            });
        }

        // Filtrar por estado
        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        } else if (!($filtros['incluir_inactivos'] ?? false)) {
            // Si no se incluyen inactivos, excluir retirados
            $query->whereNotIn('estado', ['retirado']);
        }

        // Filtrar por barrio
        if (!empty($filtros['barrio_id'])) {
            $query->where('barrio_id', $filtros['barrio_id']);
        }

        // Filtrar por cobrador
        if (!empty($filtros['cobrador_id'])) {
            $query->where('cobrador_id', $filtros['cobrador_id']);
        }

        return $query->with(['barrio', 'cobrador', 'tarifa'])
                     ->orderBy('nombre')
                     ->orderBy('apellido')
                     ->paginate($perPage);
    }

    /**
     * Obtener estadísticas básicas de clientes por empresa
     */
    public function getEstadisticasBasicas(int $empresaId): array
    {
        $baseQuery = $this->model->where('empresa_id', $empresaId);

        return [
            'total' => (clone $baseQuery)->count(),
            'activos' => (clone $baseQuery)->where('estado', 'activo')->count(),
            'suspendidos' => (clone $baseQuery)->where('estado', 'suspendido')->count(),
            'cortados' => (clone $baseQuery)->where('estado', 'cortado')->count(),
            'retirados' => (clone $baseQuery)->where('estado', 'retirado')->count(),
            'morosos' => (clone $baseQuery)->whereHas('facturas', function ($q) {
                $q->where('estado', 'vencido');
            })->count()
        ];
    }
}