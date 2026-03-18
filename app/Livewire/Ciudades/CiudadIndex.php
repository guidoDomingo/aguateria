<?php

namespace App\Livewire\Ciudades;

use App\Models\Ciudad;
use Livewire\Component;
use Livewire\WithPagination;

class CiudadIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $departamento_filtro = '';
    public $estado_filtro = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'departamento_filtro' => ['except' => ''],
        'estado_filtro' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingDepartamentoFiltro() { $this->resetPage(); }
    public function updatingEstadoFiltro() { $this->resetPage(); }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'departamento_filtro', 'estado_filtro']);
        $this->resetPage();
    }

    public function activar($ciudadId)
    {
        Ciudad::findOrFail($ciudadId)->update(['activo' => true]);
        $this->dispatch('toast', ['message' => 'Ciudad activada.', 'type' => 'success']);
    }

    public function desactivar($ciudadId)
    {
        $ciudad = Ciudad::withCount('barrios')->findOrFail($ciudadId);

        if ($ciudad->barrios_count > 0) {
            $this->dispatch('toast', [
                'message' => 'No se puede desactivar: tiene barrios asociados.',
                'type' => 'error'
            ]);
            return;
        }

        $ciudad->update(['activo' => false]);
        $this->dispatch('toast', ['message' => 'Ciudad desactivada.', 'type' => 'success']);
    }

    public function render()
    {
        $query = Ciudad::withCount('barrios');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('departamento', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->departamento_filtro) {
            $query->where('departamento', $this->departamento_filtro);
        }

        if ($this->estado_filtro !== '') {
            $query->where('activo', $this->estado_filtro == '1');
        }

        $ciudades = $query->orderBy('departamento')->orderBy('nombre')->paginate(15);

        $departamentos = Ciudad::distinct()->orderBy('departamento')->pluck('departamento');

        return view('livewire.ciudades.ciudad-index', [
            'ciudades' => $ciudades,
            'departamentos' => $departamentos,
        ])->layout('components.layouts.app', [
            'title' => 'Ciudades',
        ]);
    }
}
