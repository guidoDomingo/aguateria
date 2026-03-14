<?php

namespace App\Livewire\Facturas;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\FacturacionService;
use App\Services\PeriodoFacturacionService;
use App\Repositories\FacturaRepository;
use App\Models\PeriodoFacturacion;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FacturaIndex extends Component
{
    use WithPagination;

    public $buscar = '';
    public $filtroEstado = 'todas';
    public $filtroPeriodo = '';
    public $filtroCliente = '';
    public $mostrarSoloVencidas = false;
    public $periodoSeleccionado = null;
    public $mostrarModalFacturacion = false;
    public $procesandoFacturacion = false;

    protected $queryString = [
        'buscar' => ['except' => ''],
        'filtroEstado' => ['except' => 'todas'],
        'filtroPeriodo' => ['except' => ''],
        'page' => ['except' => 1]
    ];

    protected $listeners = [
        'facturaActualizada' => 'actualizarLista',
        'facturaEliminada' => 'actualizarLista'
    ];

    public function mount()
    {
        // No pre-seleccionar período para mostrar todas las facturas inicialmente
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

    public function updatedFiltroPeriodo()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->buscar = '';
        $this->filtroEstado = 'todas';
        $this->filtroCliente = '';
        $this->mostrarSoloVencidas = false;
        $this->resetPage();
    }

    public function abrirModalFacturacion()
    {
        $this->mostrarModalFacturacion = true;
    }

    public function cerrarModalFacturacion()
    {
        $this->mostrarModalFacturacion = false;
        $this->periodoSeleccionado = null;
    }

    public function procesarFacturacionMasiva()
    {
        if (!$this->periodoSeleccionado) {
            session()->flash('error', 'Debe seleccionar un período para facturar');
            return;
        }

        $this->procesandoFacturacion = true;

        try {
            // Obtener el período seleccionado
            $periodo = PeriodoFacturacion::find($this->periodoSeleccionado);
            
            if (!$periodo) {
                session()->flash('error', 'Período de facturación no encontrado');
                return;
            }

            $facturacionService = app(FacturacionService::class);
            $resultado = $facturacionService->generarFacturasMensuales(
                $periodo->año, 
                $periodo->mes,
                Auth::user()->empresa_id
            );

            if ($resultado['success']) {
                session()->flash('message', $resultado['message']);
                $this->actualizarLista();
                $this->cerrarModalFacturacion();
            } else {
                session()->flash('error', $resultado['message']);
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Error en facturación masiva: ' . $e->getMessage());
        } finally {
            $this->procesandoFacturacion = false;
        }
    }

    public function procesarFacturacionAutomatica()
    {
        $this->procesandoFacturacion = true;

        try {
            $empresaId = Auth::user()->empresa_id ?? 1;
            $facturacionService = app(FacturacionService::class);
            
            $resultado = $facturacionService->generarFacturasPeriodoActual($empresaId);

            if ($resultado['success']) {
                session()->flash('message', 'Facturación automática completada: ' . $resultado['message']);
                $this->actualizarLista();
            } else {
                session()->flash('error', $resultado['message']);
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Error en facturación automática: ' . $e->getMessage());
        } finally {
            $this->procesandoFacturacion = false;
        }
    }

    public function procesarFacturacionMesSiguiente()
    {
        $this->procesandoFacturacion = true;

        try {
            $empresaId = Auth::user()->empresa_id ?? 1;
            $facturacionService = app(FacturacionService::class);
            
            $resultado = $facturacionService->generarFacturasProximoMes($empresaId);

            if ($resultado['success']) {
                session()->flash('message', 'Facturación del próximo mes completada: ' . $resultado['message']);
                $this->actualizarLista();
            } else {
                session()->flash('error', $resultado['message']);
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Error en facturación del próximo mes: ' . $e->getMessage());
        } finally {
            $this->procesandoFacturacion = false;
        }
    }

    public function obtenerInformacionPeriodoActual()
    {
        try {
            $empresaId = Auth::user()->empresa_id ?? 1;
            $periodoFacturacionService = app(PeriodoFacturacionService::class);
            
            return $periodoFacturacionService->periodoActualListoParaFacturar($empresaId);
            
        } catch (\Exception $e) {
            return [
                'listo' => false,
                'mensaje' => 'Error: ' . $e->getMessage(),
                'periodo' => null
            ];
        }
    }

    public function aplicarMoras()
    {
        try {
            $facturaRepository = app(FacturaRepository::class);
            $resultado = $facturaRepository->aplicarMorasVencidas(Auth::user()->empresa_id);

            if ($resultado['success']) {
                session()->flash('message', $resultado['message']);
                $this->actualizarLista();
            } else {
                session()->flash('error', $resultado['message']);
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Error al aplicar moras: ' . $e->getMessage());
        }
    }

    public function anularFactura($facturaId)
    {
        try {
            $facturaRepository = app(FacturaRepository::class);
            $factura = $facturaRepository->find($facturaId);
            
            if (!$factura) {
                session()->flash('error', 'Factura no encontrada');
                return;
            }

            if ($factura->estado === 'anulado') {
                session()->flash('error', 'La factura ya está anulada');
                return;
            }

            if ($factura->estado === 'pagado') {
                session()->flash('error', 'No se puede anular una factura pagada');
                return;
            }

            // Anular la factura
            $factura->update([
                'estado' => 'anulado',
                'observaciones' => ($factura->observaciones ? $factura->observaciones . ' | ' : '') . 'Anulado desde interfaz por ' . Auth::user()->name
            ]);

            session()->flash('message', "Factura {$factura->numero_factura} anulada exitosamente");
            $this->actualizarLista();

        } catch (\Exception $e) {
            session()->flash('error', 'Error al anular factura: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $facturaRepository = app(FacturaRepository::class);
        $empresaId = Auth::user()->empresa_id ?? 1; // Fallback para usuarios sin empresa

        // Construir filtros
        $filtros = [
            'empresa_id' => $empresaId,
            'buscar' => $this->buscar,
            'estado' => $this->filtroEstado !== 'todas' ? $this->filtroEstado : null,
            'periodo_id' => $this->filtroPeriodo ?: null,
            'cliente_id' => $this->filtroCliente ?: null,
            'solo_vencidas' => $this->mostrarSoloVencidas
        ];

        $facturas = $facturaRepository->listarConFiltros($filtros, 15);

        // Períodos disponibles para filtros
        $periodos = PeriodoFacturacion::where('empresa_id', $empresaId)
            ->orderBy('año', 'desc')
            ->orderBy('mes', 'desc')
            ->get();

        // Estadísticas del período seleccionado
        $estadisticas = [];
        if ($this->filtroPeriodo) {
            $estadisticas = $facturaRepository->getEstadisticasPeriodo($this->filtroPeriodo);
        }

        // Información del período actual para facturación automática
        $infoPeriodoActual = $this->obtenerInformacionPeriodoActual();
        $fechaActual = Carbon::now();

        return view('livewire.facturas.factura-index', [
            'facturas' => $facturas,
            'periodos' => $periodos,
            'estadisticas' => $estadisticas,
            'infoPeriodoActual' => $infoPeriodoActual,
            'fechaActual' => $fechaActual
        ]);
    }
}