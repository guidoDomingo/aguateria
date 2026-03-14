<?php

namespace App\Livewire\Zonas;

use App\Models\Zona;
use App\Models\Barrio;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ZonaIndex extends Component
{
    use WithPagination;

    public $buscar = '';
    public $barrioFiltro = '';
    public $estadoFiltro = '';
    public $cargando = false;
    
    protected $queryString = [
        'buscar' => ['except' => ''],
        'barrioFiltro' => ['except' => ''],
        'estadoFiltro' => ['except' => '']
    ];

    public function updatingBuscar()
    {
        $this->resetPage();
    }

    public function updatingBarrioFiltro()
    {
        $this->resetPage();
    }

    public function updatingEstadoFiltro()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->buscar = '';
        $this->barrioFiltro = '';
        $this->estadoFiltro = '';
        $this->resetPage();
    }

    public function activar($zonaId)
    {
        $zona = Zona::where('empresa_id', Auth::user()->empresa_id)
                    ->find($zonaId);
        
        if ($zona) {
            $zona->update(['activo' => true]);
            $this->dispatch('toast', [
                'message' => 'Zona activada correctamente',
                'type' => 'success'
            ]);
        }
    }

    public function desactivar($zonaId)
    {
        $zona = Zona::where('empresa_id', Auth::user()->empresa_id)
                    ->find($zonaId);
        
        if ($zona) {
            $zona->update(['activo' => false]);
            $this->dispatch('toast', [
                'message' => 'Zona desactivada correctamente',
                'type' => 'success'
            ]);
        }
    }

    public function render()
    {
        $empresaId = Auth::user()->empresa_id;

        $zonasQuery = Zona::with(['barrio.ciudad'])
                         ->where('empresa_id', $empresaId);

        // Aplicar filtros
        if ($this->buscar) {
            $zonasQuery->where(function($query) {
                $query->where('nombre', 'like', '%' . $this->buscar . '%')
                      ->orWhere('descripcion', 'like', '%' . $this->buscar . '%')
                      ->orWhereHas('barrio', function($q) {
                          $q->where('nombre', 'like', '%' . $this->buscar . '%');
                      });
            });
        }

        if ($this->barrioFiltro) {
            $zonasQuery->where('barrio_id', $this->barrioFiltro);
        }

        if ($this->estadoFiltro !== '') {
            $zonasQuery->where('activo', $this->estadoFiltro === '1');
        }

        $zonas = $zonasQuery->orderBy('orden')
                           ->orderBy('nombre')
                           ->paginate(10);
        
        $barrios = Barrio::where('empresa_id', $empresaId)
                        ->activos()
                        ->orderBy('nombre')
                        ->get();

        return view('livewire.zonas.zona-index', [
            'zonas' => $zonas,
            'barrios' => $barrios
        ]);
    }
}