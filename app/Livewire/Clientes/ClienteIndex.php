<?php

namespace App\Livewire\Clientes;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\ClienteService;
use App\Repositories\ClienteRepository;
use Illuminate\Support\Facades\Auth;

class ClienteIndex extends Component
{
    use WithPagination;

    public $buscar = '';
    public $filtroEstado = 'todos';
    public $filtroBarrio = '';
    public $filtroCobrador = '';
    public $mostrarInactivos = false;
    public $clienteSeleccionado = null;
    public $mostrarModal = false;

    protected $queryString = [
        'buscar' => ['except' => ''],
        'filtroEstado' => ['except' => 'todos'],
        'page' => ['except' => 1]
    ];

    protected $listeners = [
        'clienteActualizado' => 'actualizarLista',
        'clienteEliminado' => 'actualizarLista'
    ];

    public function mount()
    {
        $this->resetPage();
    }

    public function actualizarLista()
    {
        $this->resetPage();
        $this->render();
    }

    public function updatedBuscar()
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->buscar = '';
        $this->filtroEstado = 'todos';
        $this->filtroBarrio = '';
        $this->filtroCobrador = '';
        $this->mostrarInactivos = false;
        $this->resetPage();
    }

    public function cambiarEstado($clienteId, $nuevoEstado)
    {
        try {
            $clienteService = app(ClienteService::class);
            $resultado = $clienteService->cambiarEstado($clienteId, $nuevoEstado, Auth::id());
            
            if ($resultado['success']) {
                session()->flash('message', $resultado['message']);
                $this->actualizarLista();
            } else {
                session()->flash('error', $resultado['message']);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cambiar estado del cliente: ' . $e->getMessage());
        }
    }

    public function confirmarEliminacion($clienteId)
    {
        $this->clienteSeleccionado = $clienteId;
        $this->mostrarModal = true;
    }

    public function eliminar()
    {
        if (!$this->clienteSeleccionado) {
            return;
        }

        try {
            $clienteService = app(ClienteService::class);
            $resultado = $clienteService->eliminar($this->clienteSeleccionado);
            
            if ($resultado['success']) {
                session()->flash('message', $resultado['message']);
                $this->actualizarLista();
            } else {
                session()->flash('error', $resultado['message']);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar cliente: ' . $e->getMessage());
        } finally {
            $this->mostrarModal = false;
            $this->clienteSeleccionado = null;
        }
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->clienteSeleccionado = null;
    }

    public function render()
    {
        $clienteRepository = app(ClienteRepository::class);
        $empresaId = Auth::user()->empresa_id;

        // Validar que el usuario tenga empresa asignada
        if (!$empresaId) {
            session()->flash('error', 'Usuario sin empresa asignada. Contacta al administrador.');
            $empresaId = 0; // Valor por defecto para evitar errores
        }

        // Construir filtros
        $filtros = [
            'empresa_id' => $empresaId,
            'buscar' => $this->buscar,
            'estado' => $this->filtroEstado !== 'todos' ? $this->filtroEstado : null,
            'barrio_id' => $this->filtroBarrio ?: null,
            'cobrador_id' => $this->filtroCobrador ?: null,
            'incluir_inactivos' => $this->mostrarInactivos
        ];

        $clientes = $clienteRepository->listarConFiltros($filtros, 15);

        // Datos adicionales para filtros
        $barrios = \App\Models\Barrio::where('empresa_id', $empresaId)
            ->orderBy('nombre')
            ->get();

        $cobradores = \App\Models\Cobrador::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        $estadisticas = $clienteRepository->getEstadisticasBasicas($empresaId);

        return view('livewire.clientes.cliente-index', [
            'clientes' => $clientes,
            'barrios' => $barrios,
            'cobradores' => $cobradores,
            'estadisticas' => $estadisticas
        ]);
    }
}