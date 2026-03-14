<?php

namespace App\Livewire\Tarifas;

use App\Models\Tarifa;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class TarifaIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $estado_filtro = '';
    
    protected $queryString = [
        'search' => ['except' => ''],
        'estado_filtro' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEstadoFiltro()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'estado_filtro']);
        $this->resetPage();
    }

    public function activar($tarifaId)
    {
        $tarifa = Tarifa::where('empresa_id', Auth::user()->empresa_id)
                       ->findOrFail($tarifaId);
        
        $tarifa->update(['estado' => 'activa']);

        $this->dispatch('toast', [
            'message' => 'Tarifa activada exitosamente.',
            'type' => 'success'
        ]);
    }

    public function desactivar($tarifaId)
    {
        $tarifa = Tarifa::where('empresa_id', Auth::user()->empresa_id)
                       ->findOrFail($tarifaId);

        // Verificar si tiene clientes
        if ($tarifa->clientes()->count() > 0) {
            $this->dispatch('toast', [
                'message' => 'No se puede desactivar la tarifa porque tiene clientes asociados.',
                'type' => 'error'
            ]);
            return;
        }

        $tarifa->update(['estado' => 'inactiva']);

        $this->dispatch('toast', [
            'message' => 'Tarifa desactivada exitosamente.',
            'type' => 'success'
        ]);
    }

    public function render()
    {
        $empresaId = Auth::user()->empresa_id;

        // Consulta base
        $query = Tarifa::where('empresa_id', $empresaId);

        // Aplicar filtros
        if ($this->search) {
            $query->where(function($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('codigo', 'like', '%' . $this->search . '%')
                  ->orWhere('descripcion', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->estado_filtro) {
            $query->where('estado', $this->estado_filtro);
        }

        $tarifas = $query->orderBy('nombre')
                        ->paginate(15);

        return view('livewire.tarifas.tarifa-index', [
            'tarifas' => $tarifas
        ])->layout('components.layouts.app', [
            'title' => 'Tarifas'
        ]);
    }
}