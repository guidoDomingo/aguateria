<?php

namespace App\Livewire\Barrios;

use App\Models\Barrio;
use App\Models\Ciudad;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class BarrioIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $ciudad_filtro = '';
    public $estado_filtro = '';
    
    protected $queryString = [
        'search' => ['except' => ''],
        'ciudad_filtro' => ['except' => ''],
        'estado_filtro' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCiudadFiltro()
    {
        $this->resetPage();
    }

    public function updatingEstadoFiltro()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'ciudad_filtro', 'estado_filtro']);
        $this->resetPage();
    }

    public function activar($barrioId)
    {
        $barrio = Barrio::where('empresa_id', Auth::user()->empresa_id)
                       ->findOrFail($barrioId);
        
        $barrio->update(['activo' => true]);

        $this->dispatch('toast', [
            'message' => 'Barrio activado exitosamente.',
            'type' => 'success'
        ]);
    }

    public function desactivar($barrioId)
    {
        $barrio = Barrio::where('empresa_id', Auth::user()->empresa_id)
                       ->findOrFail($barrioId);

        // Verificar si tiene clientes
        if ($barrio->clientes()->count() > 0) {
            $this->dispatch('toast', [
                'message' => 'No se puede desactivar el barrio porque tiene clientes asociados.',
                'type' => 'error'
            ]);
            return;
        }

        $barrio->update(['activo' => false]);

        $this->dispatch('toast', [
            'message' => 'Barrio desactivado exitosamente.',
            'type' => 'success'
        ]);
    }

    public function render()
    {
        $empresaId = Auth::user()->empresa_id;

        // Consulta base
        $query = Barrio::where('empresa_id', $empresaId)
                      ->with(['ciudad']);

        // Aplicar filtros
        if ($this->search) {
            $query->where(function($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('descripcion', 'like', '%' . $this->search . '%')
                  ->orWhere('referencia', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->ciudad_filtro) {
            $query->where('ciudad_id', $this->ciudad_filtro);
        }

        if ($this->estado_filtro !== '') {
            $query->where('activo', $this->estado_filtro == '1');
        }

        $barrios = $query->orderBy('nombre')
                        ->paginate(15);

        // Datos para filtros
        $ciudades = Ciudad::where('activo', true)
                         ->orderBy('nombre')
                         ->get();

        return view('livewire.barrios.barrio-index', [
            'barrios' => $barrios,
            'ciudades' => $ciudades
        ])->layout('components.layouts.app', [
            'title' => 'Barrios'
        ]);
    }
}