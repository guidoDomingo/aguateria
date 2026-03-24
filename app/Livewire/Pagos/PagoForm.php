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

    // Exoneración y descuento
    public $exonerar_mora = false;
    public $porcentaje_descuento = 0;
    public float $montoMoraTotal = 0;
    public float $montoDescuento = 0;
    public float $montoFinal = 0;

    // Control de UI
    public $cargando = false;
    public $buscarClientePorDocumento = '';

    protected $rules = [
        'cliente_id'          => 'required|exists:clientes,id',
        'monto'               => 'nullable|numeric|min:0',
        'metodo_pago_id'      => 'required|exists:metodos_pago,id',
        'cobrador_id'         => 'nullable|exists:cobradores,id',
        'fecha_pago'          => 'required|date|before_or_equal:today',
        'observaciones'       => 'nullable|string|max:500',
        'porcentaje_descuento'=> 'numeric|min:0|max:100',
    ];

    protected $messages = [
        'cliente_id.required'       => 'Debe seleccionar un cliente',
        'monto.numeric'             => 'El monto debe ser un número válido',
        'metodo_pago_id.required'   => 'Debe seleccionar un método de pago',
        'fecha_pago.required'       => 'La fecha de pago es obligatoria',
        'fecha_pago.before_or_equal'=> 'La fecha no puede ser futura',
        'porcentaje_descuento.min'  => 'El descuento no puede ser negativo',
        'porcentaje_descuento.max'  => 'El descuento no puede superar el 100%',
    ];

    public function mount()
    {
        $this->fecha_pago = now()->format('Y-m-d');

        $usuario = Auth::user();
        if ($usuario->tipo_usuario === 'cobrador' && $usuario->cobrador_id) {
            $this->cobrador_id = $usuario->cobrador_id;
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function updatedExonerarMora()
    {
        $this->actualizarMontos();
    }

    public function updatedPorcentajeDescuento()
    {
        $this->actualizarMontos();
    }

    public function buscarClientePorDoc()
    {
        if (!$this->buscarClientePorDocumento) return;

        $cliente = Cliente::where('empresa_id', Auth::user()->empresa_id)
            ->where('cedula', $this->buscarClientePorDocumento)
            ->where('estado', '!=', 'retirado')
            ->first();

        if ($cliente) {
            $this->cliente_id = $cliente->id;
            $this->updatedClienteId();
            $this->buscarClientePorDocumento = '';
        } else {
            session()->flash('error', 'No se encontró cliente con la cédula: ' . $this->buscarClientePorDocumento);
        }
    }

    public function updatedClienteId()
    {
        if ($this->cliente_id) {
            $cliente = Cliente::with(['barrio', 'cobrador'])
                ->where('empresa_id', Auth::user()->empresa_id)
                ->find($this->cliente_id);

            if ($cliente) {
                $this->cargarFacturasCliente();
                if (!$this->cobrador_id && $cliente->cobrador_id) {
                    $this->cobrador_id = $cliente->cobrador_id;
                }
            }
        } else {
            $this->facturasCliente = [];
            $this->facturasSeleccionadas = [];
            $this->actualizarMontos();
        }
    }

    public function cargarFacturasCliente()
    {
        if (!$this->cliente_id) return;

        $facturas = Factura::with(['periodoFacturacion'])
            ->where('cliente_id', $this->cliente_id)
            ->whereIn('estado', ['pendiente', 'vencido', 'parcial'])
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        $this->facturasCliente = $facturas->map(function ($factura) {
            return [
                'id'           => $factura->id,
                'numero'       => $factura->numero_factura,
                'periodo'      => Carbon::createFromDate(
                                    $factura->periodoFacturacion->año,
                                    $factura->periodoFacturacion->mes, 1
                                  )->locale('es')->isoFormat('MMM YYYY'),
                'subtotal'     => $factura->subtotal,
                'mora'         => (float) $factura->mora,
                'total'        => $factura->total,
                'pagado'       => $factura->monto_pagado ?? 0,
                'pendiente'    => $factura->saldo_pendiente,
                'vencimiento'  => $factura->fecha_vencimiento,
                'esta_vencida' => $factura->fecha_vencimiento->isPast(),
                'aviso'        => $factura->aviso,
                'estado'       => $factura->estado,
                'seleccionada' => false,
                'monto_a_pagar'=> 0,
            ];
        })->toArray();

        $this->actualizarMontos();
    }

    public function toggleFactura($facturaIndex, $seleccionada)
    {
        if (isset($this->facturasCliente[$facturaIndex])) {
            $this->facturasCliente[$facturaIndex]['seleccionada'] = $seleccionada;
            $this->facturasCliente[$facturaIndex]['monto_a_pagar'] = $seleccionada
                ? $this->facturasCliente[$facturaIndex]['pendiente']
                : 0;
            $this->actualizarMontos();
        }
    }

    public function actualizarMontos()
    {
        $subtotal = 0;
        $moraTotal = 0;
        $this->facturasSeleccionadas = [];

        foreach ($this->facturasCliente as $factura) {
            if ($factura['seleccionada'] && $factura['monto_a_pagar'] > 0) {
                $subtotal  += $factura['monto_a_pagar'];
                $moraTotal += $factura['mora'];
                $this->facturasSeleccionadas[] = [
                    'factura_id' => $factura['id'],
                    'monto'      => $factura['monto_a_pagar'],
                    'mora'       => $factura['mora'],
                ];
            }
        }

        $this->montoTotal     = $subtotal;
        $this->montoMoraTotal = $moraTotal;

        // Aplicar exoneración de mora
        $baseDescuento = $this->exonerar_mora ? ($subtotal - $moraTotal) : $subtotal;

        // Aplicar descuento porcentual
        $this->montoDescuento = round($baseDescuento * ((float)$this->porcentaje_descuento / 100), 0);
        $this->montoFinal     = $baseDescuento - $this->montoDescuento;

        $this->montoRestante = floatval($this->monto) - $this->montoFinal;
    }

    public function updatedMonto()
    {
        $this->montoRestante = floatval($this->monto) - $this->montoFinal;
    }

    public function registrarPago()
    {
        $this->cargando = true;

        try {
            $this->validate();

            if (empty($this->facturasSeleccionadas)) {
                $this->addError('facturas', 'Debe seleccionar al menos una factura para procesar el pago');
                return;
            }

            $pagoService = app(PagoService::class);

            $datos = [
                'cliente_id'           => $this->cliente_id,
                'monto'                => $this->montoFinal,
                'metodo_pago_id'       => $this->metodo_pago_id,
                'cobrador_id'          => $this->cobrador_id,
                'fecha_pago'           => $this->fecha_pago,
                'observaciones'        => $this->observaciones ?: null,
                'empresa_id'           => Auth::user()->empresa_id,
                'facturas'             => $this->facturasSeleccionadas,
                'exonerar_mora'        => $this->exonerar_mora,
                'mora_exonerada_total' => $this->exonerar_mora ? $this->montoMoraTotal : 0,
                'porcentaje_descuento' => $this->porcentaje_descuento,
                'descuento_total'      => $this->montoDescuento,
            ];

            $resultado = $pagoService->registrarPago($datos, Auth::id());

            if ($resultado['success']) {
                $cliente = Cliente::find($this->cliente_id);
                $montoFmt = number_format($this->montoFinal, 0, ',', '.');
                $vuelto = floatval($this->monto) - $this->montoFinal;

                $mensaje = "¡Pago de {$montoFmt} Gs. registrado para {$cliente->nombre} {$cliente->apellido}!";
                if ($this->exonerar_mora && $this->montoMoraTotal > 0) {
                    $moraFmt = number_format($this->montoMoraTotal, 0, ',', '.');
                    $mensaje .= " Mora exonerada: {$moraFmt} Gs.";
                }
                if ($this->montoDescuento > 0) {
                    $descFmt = number_format($this->montoDescuento, 0, ',', '.');
                    $mensaje .= " Descuento: {$descFmt} Gs.";
                }

                session()->flash('pago_exitoso', [
                    'mensaje'  => $mensaje,
                    'cliente'  => $cliente->nombre . ' ' . $cliente->apellido,
                    'monto'    => $montoFmt,
                    'vuelto'   => $vuelto > 0 ? number_format($vuelto, 0, ',', '.') : null,
                    'fecha'    => Carbon::parse($this->fecha_pago)->format('d/m/Y'),
                    'facturas' => count($this->facturasSeleccionadas),
                ]);

                $this->dispatch('pagoActualizado');
                return redirect('/pagos');
            } else {
                session()->flash('error', $resultado['message']);
                $this->addError('general', $resultado['message']);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Payment registration failed: ' . $e->getMessage());
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
            ->orderBy('nombre')->orderBy('apellido')
            ->get()
            ->map(fn($c) => [
                'id'             => $c->id,
                'nombre_completo'=> $c->nombre . ' ' . $c->apellido . ' (' . $c->cedula . ')',
                'direccion'      => $c->direccion,
            ]);

        $metodosPago = MetodoPago::where('empresa_id', $empresaId)->activos()->orderBy('nombre')->get();
        $cobradores  = Cobrador::where('empresa_id', $empresaId)->where('estado', 'activo')->orderBy('nombre')->get();

        $clienteSeleccionado = $this->cliente_id
            ? Cliente::with(['barrio', 'cobrador'])->where('empresa_id', $empresaId)->find($this->cliente_id)
            : null;

        return view('livewire.pagos.pago-form', [
            'clientes'           => $clientes,
            'metodosPago'        => $metodosPago,
            'cobradores'         => $cobradores,
            'cliente'            => $clienteSeleccionado,
        ]);
    }
}
