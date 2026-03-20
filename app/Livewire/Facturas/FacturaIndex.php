<?php

namespace App\Livewire\Facturas;

use Livewire\Component;
use Livewire\WithPagination;
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
            
            // Forzar refresh completo de la vista para asegurar que se muestre el estado correcto
            $this->resetPage();
            $this->dispatch('facturaActualizada');
            
            // También forzar un redirect para refresh completo si es necesario
            return redirect()->to(request()->header('Referer') ?: route('facturas.index'));

        } catch (\Exception $e) {
            session()->flash('error', 'Error al anular factura: ' . $e->getMessage());
        }
    }

    public function verFactura($facturaId)
    {
        try {
            $factura = \App\Models\Factura::find($facturaId);
            
            if (!$factura) {
                session()->flash('error', 'Factura no encontrada');
                return;
            }
            
            // Verificar que el usuario tenga acceso a la factura
            if ($factura->empresa_id !== Auth::user()->empresa_id) {
                session()->flash('error', 'No tiene permisos para acceder a esta factura');
                return;
            }
            
            // Redirigir a la vista de detalle
            return redirect()->route('facturas.ver', $factura->id);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al ver factura: ' . $e->getMessage());
        }
    }

    private function tipoComprobante(): string
    {
        $config = Auth::user()->empresa->configuraciones ?? [];
        return $config['tipo_comprobante'] ?? 'factura';
    }

    public function imprimirFactura($facturaId)
    {
        try {
            $factura = \App\Models\Factura::find($facturaId);
            if (!$factura || $factura->empresa_id !== Auth::user()->empresa_id) {
                session()->flash('error', 'Factura no encontrada o sin permisos');
                return;
            }

            if ($this->tipoComprobante() === 'recibo') {
                return redirect()->route('facturas.boleta.imprimir', $factura->id);
            }

            return redirect()->route('facturas.imprimir', $factura->id);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al imprimir: ' . $e->getMessage());
        }
    }

    public function descargarPdf($facturaId)
    {
        try {
            $factura = \App\Models\Factura::find($facturaId);
            if (!$factura || $factura->empresa_id !== Auth::user()->empresa_id) {
                session()->flash('error', 'Factura no encontrada o sin permisos');
                return;
            }

            if ($this->tipoComprobante() === 'recibo') {
                return redirect()->route('facturas.boleta.pdf', $factura->id);
            }

            return redirect()->route('facturas.pdf', $factura->id);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al generar PDF: ' . $e->getMessage());
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

        // Día de facturación configurado
        $empresa = Auth::user()->empresa;
        $diaFacturacion = (int) ($empresa->configuraciones['dia_facturacion'] ?? 1);
        $hoy = Carbon::now();
        $proximaFecha = $hoy->day <= $diaFacturacion
            ? $hoy->copy()->setDay($diaFacturacion)
            : $hoy->copy()->addMonth()->setDay($diaFacturacion);

        // Deuda acumulada por cliente (todas sus facturas no pagadas/anuladas)
        $clienteIds = $facturas->pluck('cliente_id')->unique()->filter();
        $deudasPorCliente = \App\Models\Factura::where('empresa_id', $empresaId)
            ->whereIn('cliente_id', $clienteIds)
            ->whereIn('estado', ['pendiente', 'vencido', 'parcial'])
            ->groupBy('cliente_id')
            ->selectRaw('cliente_id, SUM(total) as deuda_total, COUNT(*) as cant_facturas')
            ->get()
            ->keyBy('cliente_id');

        return view('livewire.facturas.factura-index', [
            'facturas'          => $facturas,
            'periodos'          => $periodos,
            'estadisticas'      => $estadisticas,
            'diaFacturacion'    => $diaFacturacion,
            'proximaFecha'      => $proximaFecha,
            'deudasPorCliente'  => $deudasPorCliente,
        ]);
    }
}