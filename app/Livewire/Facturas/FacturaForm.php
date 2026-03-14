<?php

namespace App\Livewire\Facturas;

use Livewire\Component;
use App\Services\FacturacionService;
use App\Models\Factura;
use App\Models\Cliente;
use App\Models\PeriodoFacturacion;
use Illuminate\Support\Facades\Auth;

class FacturaForm extends Component
{
    public $facturaId = null;
    public $esEdicion = false;
    
    // Datos de la factura
    public $cliente_id = '';
    public $periodo_facturacion_id = '';
    public $fecha_vencimiento = '';
    public $descuento_aplicado = 0;
    public $observaciones = '';

    // Control de UI
    public $cargando = false;
    public $cliente = null;
    public $periodo = null;
    public $preview = [];

    protected $listeners = [
        'editarFactura' => 'cargarFactura'
    ];

    public function mount($facturaId = null)
    {
        if ($facturaId) {
            $this->facturaId = $facturaId;
            $this->esEdicion = true;
            $this->cargarFactura($facturaId);
        } else {
            // Para nueva factura, seleccionar período abierto más reciente
            $empresaId = Auth::user()->empresa_id ?? 1;
            $periodoAbierto = PeriodoFacturacion::where('empresa_id', $empresaId)
                ->where('estado', 'abierto')
                ->orderBy('año', 'desc')
                ->orderBy('mes', 'desc')
                ->first();

            if ($periodoAbierto) {
                $this->periodo_facturacion_id = $periodoAbierto->id;
                $this->fecha_vencimiento = $periodoAbierto->fecha_vencimiento->format('Y-m-d');
                $this->periodo = $periodoAbierto;
            }
        }
    }

    public function cargarFactura($facturaId)
    {
        try {
            $factura = Factura::with(['cliente', 'periodo', 'detalles'])
                ->where('empresa_id', Auth::user()->empresa_id)
                ->findOrFail($facturaId);

            $this->facturaId = $factura->id;
            $this->cliente_id = $factura->cliente_id;
            $this->periodo_facturacion_id = $factura->periodo_id;
            $this->fecha_vencimiento = $factura->fecha_vencimiento;
            $this->descuento_aplicado = $factura->descuento_aplicado ?? 0;
            $this->observaciones = $factura->observaciones ?? '';
            $this->cliente = $factura->cliente;
            $this->periodo = $factura->periodo;
            $this->esEdicion = true;

            $this->calcularPreview();

        } catch (\Exception $e) {
            session()->flash('error', 'Factura no encontrada');
            return redirect()->route('facturas.index');
        }
    }

    protected function rules()
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'periodo_facturacion_id' => 'required|exists:periodos_facturacion,id',
            'fecha_vencimiento' => 'required|date|after_or_equal:today',
            'descuento_aplicado' => 'numeric|min:0|max:100',
            'observaciones' => 'nullable|string|max:500'
        ];
    }

    protected function validationAttributes()
    {
        return [
            'cliente_id' => 'cliente',
            'periodo_facturacion_id' => 'período de facturación',
            'fecha_vencimiento' => 'fecha de vencimiento',
            'descuento_aplicado' => 'descuento aplicado',
            'observaciones' => 'observaciones'
        ];
    }

    public function updatedClienteId()
    {
        $this->cargarInfoCliente();
    }

    public function cargarInfoCliente()
    {
        if ($this->cliente_id) {
            $this->cliente = Cliente::with(['tarifa', 'barrio', 'cobrador'])
                ->where('empresa_id', Auth::user()->empresa_id)
                ->find($this->cliente_id);
            
            if ($this->cliente) {
                $this->calcularPreview();
            }
        } else {
            $this->cliente = null;
            $this->preview = [];
        }
    }

    public function updatedPeriodoFacturacionId()
    {
        if ($this->periodo_facturacion_id) {
            $this->periodo = PeriodoFacturacion::find($this->periodo_facturacion_id);
            
            if ($this->periodo) {
                $this->fecha_vencimiento = $this->periodo->fecha_vencimiento->format('Y-m-d');
            }
            
            $this->calcularPreview();
        } else {
            $this->periodo = null;
            $this->preview = [];
        }
    }

    public function updatedDescuentoAplicado()
    {
        $this->calcularPreview();
    }

    public function calcularPreview()
    {
        if (!$this->cliente || !$this->periodo) {
            $this->preview = [];
            return;
        }

        try {
            $facturacionService = app(FacturacionService::class);
            
            // Simular cálculo de factura
            $datos = [
                'cliente_id' => $this->cliente_id,
                'periodo_facturacion_id' => $this->periodo_facturacion_id,
                'descuento_adicional' => $this->descuento_aplicado
            ];

            $resultado = $facturacionService->calcularFactura($datos);
            
            if ($resultado['success']) {
                $this->preview = $resultado['data'];
            } else {
                $this->preview = [];
            }

        } catch (\Exception $e) {
            $this->preview = [];
        }
    }

    public function generarFactura()
    {
        $this->cargando = true;

        try {
            $this->validate();

            $facturacionService = app(FacturacionService::class);

            $datos = [
                'cliente_id' => $this->cliente_id,
                'periodo_facturacion_id' => $this->periodo_facturacion_id,
                'fecha_vencimiento' => $this->fecha_vencimiento,
                'descuento_adicional' => $this->descuento_aplicado,
                'observaciones' => $this->observaciones ?: null
            ];

            if ($this->esEdicion) {
                // En edición, solo actualizar algunos campos
                $resultado = $facturacionService->actualizarFactura($this->facturaId, $datos, Auth::id());
            } else {
                // Nueva factura
                $resultado = $facturacionService->generarFacturaIndividual($datos, Auth::id());
            }

            if ($resultado['success']) {
                session()->flash('message', $resultado['message']);
                $this->dispatch('facturaActualizada');
                return redirect()->route('facturas.index');
            } else {
                session()->flash('error', $resultado['message']);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al procesar factura: ' . $e->getMessage());
        } finally {
            $this->cargando = false;
        }
    }

    public function cancelar()
    {
        return redirect()->route('facturas.index');
    }

    public function render()
    {
        $empresaId = Auth::user()->empresa_id ?? 1; // Fallback para testing

        $clientes = Cliente::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get()
            ->map(function ($cliente) {
                return [
                    'id' => $cliente->id,
                    'nombre_completo' => $cliente->nombre . ' ' . $cliente->apellido . ' (' . $cliente->cedula . ')',
                    'direccion' => $cliente->direccion,
                    'barrio' => $cliente->barrio->nombre ?? 'Sin barrio',
                    'tarifa' => $cliente->tarifa->nombre ?? 'Sin tarifa'
                ];
            });

        $periodos = PeriodoFacturacion::where('empresa_id', $empresaId)
            ->whereIn('estado', ['abierto', 'cerrado'])
            ->orderBy('año', 'desc')
            ->orderBy('mes', 'desc')
            ->get();

        return view('livewire.facturas.factura-form', [
            'clientes' => $clientes,
            'periodos' => $periodos
        ]);
    }
}