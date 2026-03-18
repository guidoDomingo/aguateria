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
        'monto' => 'nullable|numeric|min:0', // Ahora es opcional, solo para calcular vuelto
        'metodo_pago_id' => 'required|exists:metodos_pago,id',
        'cobrador_id' => 'nullable|exists:cobradores,id',
        'fecha_pago' => 'required|date|before_or_equal:today',
        'observaciones' => 'nullable|string|max:500'
    ];

    protected $messages = [
        'cliente_id.required' => 'Debe seleccionar un cliente',
        'monto.numeric' => 'El monto debe ser un número válido',
        'monto.min' => 'El monto debe ser mayor o igual a 0',
        'metodo_pago_id.required' => 'Debe seleccionar un método de pago',
        'fecha_pago.required' => 'La fecha de pago es obligatoria',
        'fecha_pago.before_or_equal' => 'La fecha no puede ser futura'
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
            // Llamar explícitamente para cargar las facturas
            $this->updatedClienteId();
            // Limpiar el campo de búsqueda
            $this->buscarClientePorDocumento = '';
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
        if (isset($this->facturasCliente[$facturaIndex])) {
            $this->facturasCliente[$facturaIndex]['seleccionada'] = $seleccionada;
            
            if ($seleccionada) {
                // Siempre aplicar el monto completo pendiente
                $this->facturasCliente[$facturaIndex]['monto_a_pagar'] = $this->facturasCliente[$facturaIndex]['pendiente'];
            } else {
                $this->facturasCliente[$facturaIndex]['monto_a_pagar'] = 0;
            }
            
            $this->actualizarMontos();
        }
    }



    public function actualizarMontos()
    {
        $this->montoTotal = 0;
        $this->facturasSeleccionadas = [];

        foreach ($this->facturasCliente as $factura) {
            if ($factura['seleccionada'] && $factura['monto_a_pagar'] > 0) {
                $this->montoTotal += $factura['monto_a_pagar'];
                $this->facturasSeleccionadas[] = [
                    'factura_id' => $factura['id'],
                    'monto' => $factura['monto_a_pagar']
                ];
            }
        }

        // Calcular el sobrante/faltante
        $this->montoRestante = floatval($this->monto) - $this->montoTotal;
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
                $this->addError('facturas', 'Debe seleccionar al menos una factura para procesar el pago');
                return;
            }

            // El pago es exacto a las facturas seleccionadas - no validar monto recibido
            // El monto recibido es solo para calcular vuelto

            $pagoService = app(PagoService::class);

            $datos = [
                'cliente_id' => $this->cliente_id,
                'monto' => $this->montoTotal, // Usar el monto exacto de las facturas
                'metodo_pago_id' => $this->metodo_pago_id,
                'cobrador_id' => $this->cobrador_id,
                'fecha_pago' => $this->fecha_pago,
                'observaciones' => $this->observaciones ?: null,
                'empresa_id' => Auth::user()->empresa_id,
                'facturas' => $this->facturasSeleccionadas
            ];

            $resultado = $pagoService->registrarPago($datos, Auth::id());

            if ($resultado['success']) {
                // Formatear mensaje de éxito más claro
                $cliente = Cliente::find($this->cliente_id);
                $montoFormateado = number_format($this->montoTotal, 0, ',', '.');
                $vuelto = floatval($this->monto) - $this->montoTotal;
                
                $mensajeExito = "¡Pago de {$montoFormateado} Gs. registrado exitosamente para {$cliente->nombre} {$cliente->apellido}!";
                if ($vuelto > 0) {
                    $vueltoFormateado = number_format($vuelto, 0, ',', '.');
                    $mensajeExito .= " Vuelto: {$vueltoFormateado} Gs.";
                }
                
                session()->flash('pago_exitoso', [
                    'mensaje' => $mensajeExito,
                    'cliente' => $cliente->nombre . ' ' . $cliente->apellido,
                    'monto' => $montoFormateado,
                    'vuelto' => $vuelto > 0 ? number_format($vuelto, 0, ',', '.') : null,
                    'fecha' => \Carbon\Carbon::parse($this->fecha_pago)->format('d/m/Y'),
                    'facturas' => count($this->facturasSeleccionadas)
                ]);
                
                $this->dispatch('pagoActualizado');
                return redirect('/pagos');
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
        return redirect('/pagos');
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