<?php

namespace App\Livewire\Pagos;

use Livewire\Component;
use App\Services\PagoService;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\MetodoPago;
use App\Models\Cobrador;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PagoForm extends Component
{
    // Datos del pago
    public $cliente_id = '';
    public $monto = '';
    public $metodo_pago_id = '';
    public $cobrador_id = '';
    public $fecha_pago = '';
    public $observaciones = '';

    // Control de facturas
    public $facturasCliente = [];
    public $facturasSeleccionadas = [];
    public $montoTotal = 0;
    public $montoRestante = 0;

    // Control de UI
    public $cargando = false;
    public $buscarClientePorDocumento = '';

    protected $rules = [
        'cliente_id' => 'required|exists:clientes,id',
        'monto' => 'required|numeric|min:1',
        'metodo_pago_id' => 'required|exists:metodos_pago,id',
        'cobrador_id' => 'required|exists:cobradores,id',
        'fecha_pago' => 'required|date|before_or_equal:today',
        'observaciones' => 'nullable|string|max:500'
    ];

    protected $validationAttributes = [
        'cliente_id' => 'cliente',
        'monto' => 'monto',
        'metodo_pago_id' => 'método de pago',
        'cobrador_id' => 'cobrador',
        'fecha_pago' => 'fecha de pago',
        'observaciones' => 'observaciones'
    ];

    public function mount()
    {
        $this->fecha_pago = now()->format('Y-m-d');
        
        // Asignar cobrador del usuario logueado si es cobrador
        $usuario = Auth::user();
        if ($usuario->tipo_usuario === 'cobrador' && $usuario->cobrador_id) {
            $this->cobrador_id = $usuario->cobrador_id;
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function buscarClientePorDoc()
    {
        if (!$this->buscarClientePorDocumento) {
            return;
        }

        $cliente = Cliente::where('empresa_id', Auth::user()->empresa_id)
            ->where('cedula', $this->buscarClientePorDocumento)
            ->where('estado', '!=', 'retirado')
            ->first();

        if ($cliente) {
            $this->cliente_id = $cliente->id;
        } else {
            session()->flash('error', 'No se encontró cliente con la cédula: ' . $this->buscarClientePorDocumento);
        }
    }

    public function updatedClienteId()
    {
        \Log::info('Cliente ID updated', ['cliente_id' => $this->cliente_id]);
        
        if ($this->cliente_id) {
            $cliente = Cliente::with(['barrio', 'cobrador'])
                ->where('empresa_id', Auth::user()->empresa_id)
                ->find($this->cliente_id);

            if ($cliente) {
                \Log::info('Cliente found', ['cliente' => $cliente->nombre]);
                $this->cargarFacturasCliente();
                
                // Auto-seleccionar cobrador si está disponible
                if (!$this->cobrador_id && $cliente->cobrador_id) {
                    $this->cobrador_id = $cliente->cobrador_id;
                }
            } else {
                \Log::warning('Cliente not found', ['cliente_id' => $this->cliente_id]);
            }
        } else {
            $this->facturasCliente = [];
            $this->facturasSeleccionadas = [];
            $this->actualizarMontos();
        }
    }

    public function cargarFacturasCliente()
    {
        if (!$this->cliente_id) {
            return;
        }

        $facturas = Factura::with(['periodoFacturacion'])
            ->where('cliente_id', $this->cliente_id)
            ->whereIn('estado', ['pendiente', 'vencido', 'parcial'])
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        \Log::info('Loading client invoices', [
            'cliente_id' => $this->cliente_id,
            'facturas_count' => $facturas->count(),
            'facturas' => $facturas->pluck('id')->toArray()
        ]);

        $this->facturasCliente = $facturas
            ->map(function ($factura) {
                return [
                    'id' => $factura->id,
                    'numero' => str_pad($factura->numero_factura, 6, '0', STR_PAD_LEFT),
                    'periodo' => Carbon::createFromDate($factura->periodoFacturacion->año, $factura->periodoFacturacion->mes, 1)->locale('es')->isoFormat('MMM YYYY'),
                    'total' => $factura->total,
                    'pagado' => $factura->monto_pagado,
                    'pendiente' => $factura->total - $factura->monto_pagado,
                    'vencimiento' => $factura->fecha_vencimiento,
                    'esta_vencida' => $factura->fecha_vencimiento->isPast(),
                    'estado' => $factura->estado,
                    'seleccionada' => false,
                    'monto_a_pagar' => 0
                ];
            })->toArray();

        \Log::info('Facturas loaded', [
            'facturas_cliente_count' => count($this->facturasCliente)
        ]);

        $this->actualizarMontos();
    }

    public function toggleFactura($facturaIndex, $seleccionada)
    {
        \Log::info('Toggling factura', [
            'factura_index' => $facturaIndex,
            'seleccionada' => $seleccionada
        ]);
        
        if (isset($this->facturasCliente[$facturaIndex])) {
            $this->facturasCliente[$facturaIndex]['seleccionada'] = $seleccionada;
            
            if ($seleccionada) {
                // Auto-completar con el monto pendiente
                $this->facturasCliente[$facturaIndex]['monto_a_pagar'] = $this->facturasCliente[$facturaIndex]['pendiente'];
            } else {
                $this->facturasCliente[$facturaIndex]['monto_a_pagar'] = 0;
            }
            
            \Log::info('Factura toggled', [
                'factura_id' => $this->facturasCliente[$facturaIndex]['id'],
                'monto_a_pagar' => $this->facturasCliente[$facturaIndex]['monto_a_pagar']
            ]);
            
            $this->actualizarMontos();
        }
    }

    public function aplicarMontoCompleto($facturaIndex)
    {
        if (isset($this->facturasCliente[$facturaIndex])) {
            $this->facturasCliente[$facturaIndex]['seleccionada'] = true;
            $this->facturasCliente[$facturaIndex]['monto_a_pagar'] = $this->facturasCliente[$facturaIndex]['pendiente'];
            $this->actualizarMontos();
        }
    }

    public function updatedFacturasMonto($value, $key)
    {
        \Log::info('Updating facturas monto', [
            'value' => $value,
            'key' => $key
        ]);
        
        // $key será algo como "facturas.0.monto_a_pagar"
        $parts = explode('.', $key);
        $index = $parts[1];
        
        if (isset($this->facturasCliente[$index])) {
            $factura = &$this->facturasCliente[$index];
            $monto = floatval($value);
            
            // Validar que no exceda el monto pendiente
            if ($monto > $factura['pendiente']) {
                $factura['monto_a_pagar'] = $factura['pendiente'];
            } else if ($monto < 0) {
                $factura['monto_a_pagar'] = 0;
            } else {
                $factura['monto_a_pagar'] = $monto;
            }
            
            // Auto-seleccionar si hay monto
            $factura['seleccionada'] = $factura['monto_a_pagar'] > 0;
            
            \Log::info('Updated monto for factura', [
                'factura_id' => $factura['id'],
                'new_monto' => $factura['monto_a_pagar'],
                'seleccionada' => $factura['seleccionada']
            ]);
            
            $this->actualizarMontos();
        }
    }

    public function actualizarMontos()
    {
        $this->montoTotal = 0;
        $this->facturasSeleccionadas = [];

        foreach ($this->facturasCliente as $index => $factura) {
            if ($factura['seleccionada'] && $factura['monto_a_pagar'] > 0) {
                $this->montoTotal += $factura['monto_a_pagar'];
                $this->facturasSeleccionadas[] = [
                    'factura_id' => $factura['id'],
                    'monto' => $factura['monto_a_pagar']
                ];
            }
        }

        // Actualizar monto del pago
        $this->monto = $this->montoTotal;
        $this->montoRestante = floatval($this->monto) - $this->montoTotal;
        
        // Debug log
        \Log::info('Montos actualizados', [
            'facturas_cliente_count' => count($this->facturasCliente),
            'facturas_seleccionadas_count' => count($this->facturasSeleccionadas),
            'monto_total' => $this->montoTotal,
            'monto_pago' => $this->monto
        ]);
    }

    public function updatedMonto()
    {
        $this->montoRestante = floatval($this->monto) - $this->montoTotal;
    }

    public function registrarPago()
    {
        $this->cargando = true;

        try {
            // Debug: Log attempt
            \Log::info('Attempting to register payment', [
                'cliente_id' => $this->cliente_id,
                'monto' => $this->monto,
                'metodo_pago_id' => $this->metodo_pago_id,
                'cobrador_id' => $this->cobrador_id,
                'facturas_seleccionadas' => $this->facturasSeleccionadas
            ]);

            $this->validate();

            // Validar que haya facturas seleccionadas
            if (empty($this->facturasSeleccionadas)) {
                $this->addError('facturas', 'Debe seleccionar al menos una factura para aplicar el pago');
                return;
            }

            // Validar que el monto sea suficiente
            if ($this->montoTotal > floatval($this->monto)) {
                $this->addError('monto', 'El monto del pago no es suficiente para cubrir las facturas seleccionadas');
                return;
            }

            $pagoService = app(PagoService::class);

            $datos = [
                'cliente_id' => $this->cliente_id,
                'monto' => floatval($this->monto),
                'metodo_pago_id' => $this->metodo_pago_id,
                'cobrador_id' => $this->cobrador_id,
                'fecha_pago' => $this->fecha_pago,
                'observaciones' => $this->observaciones ?: null,
                'empresa_id' => Auth::user()->empresa_id,
                'facturas' => $this->facturasSeleccionadas
            ];

            $resultado = $pagoService->registrarPago($datos, Auth::id());

            if ($resultado['success']) {
                session()->flash('message', $resultado['message']);
                $this->dispatch('pagoActualizado');
                return $this->redirect('/pagos', navigate: true);
            } else {
                session()->flash('error', $resultado['message']);
                $this->addError('general', $resultado['message']);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Let Livewire handle validation errors
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Payment registration failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', $e->getMessage());
            $this->addError('general', $e->getMessage());
        } finally {
            $this->cargando = false;
        }
    }

    public function cancelar()
    {
        return redirect()->route('pagos.index');
    }

    public function render()
    {
        $empresaId = Auth::user()->empresa_id;

        $clientes = Cliente::where('empresa_id', $empresaId)
            ->where('estado', '!=', 'retirado')
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get()
            ->map(function ($cliente) {
                return [
                    'id' => $cliente->id,
                    'nombre_completo' => $cliente->nombre . ' ' . $cliente->apellido . ' (' . $cliente->cedula . ')',
                    'direccion' => $cliente->direccion
                ];
            });

        $metodosPago = MetodoPago::where('empresa_id', $empresaId)
            ->activos()
            ->orderBy('nombre')
            ->get();

        $cobradores = Cobrador::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        // Get selected cliente data
        $clienteSeleccionado = null;
        if ($this->cliente_id) {
            $clienteSeleccionado = Cliente::with(['barrio', 'cobrador'])
                ->where('empresa_id', $empresaId)
                ->find($this->cliente_id);
        }

        return view('livewire.pagos.pago-form', [
            'clientes' => $clientes,
            'metodosPago' => $metodosPago,
            'cobradores' => $cobradores,
            'cliente' => $clienteSeleccionado
        ]);
    }
}