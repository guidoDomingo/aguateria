<?php

namespace App\Livewire\Pagos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\PagoService;
use App\Repositories\PagoRepository;
use App\Models\MetodoPago;
use App\Models\Cobrador;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PagoIndex extends Component
{
    use WithPagination;

    public $buscar = '';
    public $filtroMetodo = '';
    public $filtroCobrador = '';
    public $fechaDesde = '';
    public $fechaHasta = '';
    public $filtroEstado = 'todos';
    public $pagoSeleccionado = null;
    public $mostrarModal = false;

    protected $queryString = [
        'buscar' => ['except' => ''],
        'filtroMetodo' => ['except' => ''],
        'fechaDesde' => ['except' => ''],
        'fechaHasta' => ['except' => ''],
        'page' => ['except' => 1]
    ];

    protected $listeners = [
        'pagoActualizado' => 'actualizarLista',
        'pagoEliminado' => 'actualizarLista'
    ];

    public function mount()
    {
        // Por defecto, mostrar pagos de hoy
        $this->fechaDesde = now()->format('Y-m-d');
        $this->fechaHasta = now()->format('Y-m-d');
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

    public function updatedFiltroMetodo()
    {
        $this->resetPage();
    }

    public function updatedFiltroCobrador()
    {
        $this->resetPage();
    }

    public function updatedFechaDesde()
    {
        $this->resetPage();
    }

    public function updatedFechaHasta()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->buscar = '';
        $this->filtroMetodo = '';
        $this->filtroCobrador = '';
        $this->fechaDesde = '';
        $this->fechaHasta = '';
        $this->filtroEstado = 'todos';
        $this->resetPage();
    }

    public function establecerHoy()
    {
        $this->fechaDesde = now()->format('Y-m-d');
        $this->fechaHasta = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function establecerSemana()
    {
        $this->fechaDesde = now()->startOfWeek()->format('Y-m-d');
        $this->fechaHasta = now()->endOfWeek()->format('Y-m-d');
        $this->resetPage();
    }

    public function establecerMes()
    {
        $this->fechaDesde = now()->startOfMonth()->format('Y-m-d');
        $this->fechaHasta = now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }

    public function confirmarAnulacion($pagoId)
    {
        $this->pagoSeleccionado = $pagoId;
        $this->mostrarModal = true;
    }

    public function anularPago()
    {
        if (!$this->pagoSeleccionado) {
            return;
        }

        try {
            $pagoService = app(PagoService::class);
            $resultado = $pagoService->anularPago($this->pagoSeleccionado, Auth::id(), 'Anulación desde interfaz');
            
            if ($resultado['success']) {
                session()->flash('message', $resultado['message']);
                $this->actualizarLista();
            } else {
                session()->flash('error', $resultado['message']);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error al anular pago: ' . $e->getMessage());
        } finally {
            $this->cerrarModal();
        }
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->pagoSeleccionado = null;
    }

    public function imprimirRecibo($pagoId)
    {
        // Aquí se implementaría la lógica para generar el PDF del recibo
        session()->flash('message', 'Funcionalidad de impresión en desarrollo');
    }

    public function render()
    {
        $pagoRepository = app(PagoRepository::class);
        $empresaId = Auth::user()->empresa_id;

        // Construir filtros
        $filtros = [
            'empresa_id' => $empresaId,
            'buscar' => $this->buscar,
            'metodo_pago_id' => $this->filtroMetodo ?: null,
            'cobrador_id' => $this->filtroCobrador ?: null,
            'fecha_desde' => $this->fechaDesde ?: null,
            'fecha_hasta' => $this->fechaHasta ?: null,
            'estado' => $this->filtroEstado !== 'todos' ? $this->filtroEstado : null
        ];

        $pagos = $pagoRepository->listarConFiltros($filtros, 15);

        // Datos para filtros
        $metodosPago = MetodoPago::where('empresa_id', $empresaId)
            ->activos()
            ->orderBy('nombre')
            ->get();

        $cobradores = Cobrador::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        // Estadísticas del período
        $estadisticas = [];
        if ($this->fechaDesde || $this->fechaHasta) {
            $estadisticas = $pagoRepository->getEstadisticasPeriodo(
                $empresaId,
                $this->fechaDesde,
                $this->fechaHasta
            );
        } else {
            // Estadísticas del día actual
            $estadisticas = $pagoRepository->getEstadisticasHoy($empresaId);
        }

        return view('livewire.pagos.pago-index', [
            'pagos' => $pagos,
            'metodosPago' => $metodosPago,
            'cobradores' => $cobradores,
            'estadisticas' => $estadisticas
        ]);
    }
}