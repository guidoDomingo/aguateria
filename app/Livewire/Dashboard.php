<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\ReporteService;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public $estadisticas = [];
    public $cargando = true;

    protected $reporteService;

    public function boot(ReporteService $reporteService)
    {
        $this->reporteService = $reporteService;
    }

    public function mount()
    {
        $this->cargarEstadisticas();
    }

    public function cargarEstadisticas()
    {
        $this->cargando = true;
        
        try {
            $empresaId = Auth::user()->empresa_id;
            
            if ($empresaId) {
                $resultado = $this->reporteService->getEstadisticasDashboard($empresaId);
                
                if ($resultado['success']) {
                    $this->estadisticas = $resultado['data'];
                } else {
                    $this->estadisticas = $this->getEstadisticasDefecto();
                }
            } else {
                $this->estadisticas = $this->getEstadisticasDefecto();
            }
            
        } catch (\Exception $e) {
            $this->estadisticas = $this->getEstadisticasDefecto();
        } finally {
            $this->cargando = false;
        }
    }
    
    public function refrescarEstadisticas()
    {
        $this->cargarEstadisticas();
        $this->dispatch('toast', [
            'message' => 'Estadísticas actualizadas',
            'type' => 'success'
        ]);
    }

    private function getEstadisticasDefecto()
    {
        return [
            'clientes' => [
                'total' => 0,
                'activos' => 0,
                'suspendidos' => 0
            ],
            'facturacion_mes' => [
                'cantidad_facturas' => 0,
                'monto_facturado' => 0,
                'monto_cobrado' => 0,
                'porcentaje_cobranza' => 0
            ],
            'morosidad' => [
                'facturas_vencidas' => 0,
                'monto_vencido' => 0,
                'clientes_morosos' => 0
            ],
            'kpis' => [
                'crecimiento_clientes' => 0,
                'eficiencia_cobranza' => 0,
                'indice_morosidad' => 0
            ]
        ];
    }

    public function render()
    {
        return view('livewire.dashboard')->layout('layouts.app', [
            'titulo' => 'Dashboard',
            'subtitulo' => 'Panel de control y estadísticas generales'
        ]);
    }
}
