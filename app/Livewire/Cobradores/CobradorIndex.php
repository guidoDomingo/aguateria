<?php

namespace App\Livewire\Cobradores;

use App\Models\Cobrador;
use App\Models\Zona;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class CobradorIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $zona_filtro = '';
    public $estado_filtro = '';
    
    protected $queryString = [
        'search' => ['except' => ''],
        'zona_filtro' => ['except' => ''],
        'estado_filtro' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingZonaFiltro()
    {
        $this->resetPage();
    }

    public function updatingEstadoFiltro()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'zona_filtro', 'estado_filtro']);
        $this->resetPage();
    }

    public function eliminar($cobradorId)
    {
        $cobrador = Cobrador::where('empresa_id', Auth::user()->empresa_id)
                          ->findOrFail($cobradorId);

        // Verificar si tiene clientes asignados
        if ($cobrador->clientes()->count() > 0) {
            $this->dispatch('toast', [
                'message' => 'No se puede eliminar el cobrador porque tiene clientes asignados.',
                'type' => 'error'
            ]);
            return;
        }

        $cobrador->update(['estado' => 'inactivo']);

        $this->dispatch('toast', [
            'message' => 'Cobrador eliminado exitosamente.',
            'type' => 'success'
        ]);
    }

    public function activar($cobradorId)
    {
        $cobrador = Cobrador::where('empresa_id', Auth::user()->empresa_id)
                          ->findOrFail($cobradorId);
        
        $cobrador->update(['estado' => 'activo']);

        $this->dispatch('toast', [
            'message' => 'Cobrador activado exitosamente.',
            'type' => 'success'
        ]);
    }

    public function render()
    {
        $empresaId = Auth::user()->empresa_id;

        // Consulta base
        $query = Cobrador::where('empresa_id', $empresaId)
                        ->with(['zona']);

        // Aplicar filtros
        if ($this->search) {
            $query->where(function($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('apellido', 'like', '%' . $this->search . '%')
                  ->orWhere('cedula', 'like', '%' . $this->search . '%')
                  ->orWhere('codigo', 'like', '%' . $this->search . '%')
                  ->orWhere('telefono', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->zona_filtro) {
            $query->where('zona_id', $this->zona_filtro);
        }

        if ($this->estado_filtro) {
            $query->where('estado', $this->estado_filtro);
        }

        $cobradores = $query->orderBy('nombre')
                           ->orderBy('apellido')
                           ->paginate(15);

        // Datos para filtros
        $zonas = Zona::where('empresa_id', $empresaId)
                    ->orderBy('nombre')
                    ->get();

        return view('livewire.cobradores.cobrador-index', [
            'cobradores' => $cobradores,
            'zonas' => $zonas
        ])->layout('components.layouts.app', [
            'title' => 'Cobradores'
        ]);
    }
}