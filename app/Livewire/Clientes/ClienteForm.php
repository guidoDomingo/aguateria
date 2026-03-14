<?php

namespace App\Livewire\Clientes;

use Livewire\Component;
use App\Services\ClienteService;
use App\Models\Cliente;
use App\Models\Barrio;
use App\Models\Cobrador;
use App\Models\Tarifa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClienteForm extends Component
{
    public $clienteId = null;
    public $esEdicion = false;
    
    // Datos del cliente
    public $nombre = '';
    public $apellido = '';
    public $cedula = '';
    public $telefono = '';
    public $email = '';
    public $direccion = '';
    public $barrio_id = '';
    public $cobrador_id = '';
    public $tarifa_id = '';
    public $descuento_especial = 0;
    public $observaciones = '';
    public $estado = 'activo';

    // Control de UI
    public $cargando = false;

    protected $listeners = [
        'editarCliente' => 'cargarCliente'
    ];

    public function mount($clienteId = null)
    {
        if ($clienteId) {
            $this->clienteId = $clienteId;
            $this->esEdicion = true;
            $this->cargarCliente($clienteId);
        }
    }

    public function cargarCliente($clienteId)
    {
        try {
            $cliente = Cliente::with(['barrio', 'cobrador', 'tarifa'])
                ->where('empresa_id', Auth::user()->empresa_id)
                ->findOrFail($clienteId);

            $this->clienteId = $cliente->id;
            $this->nombre = $cliente->nombre;
            $this->apellido = $cliente->apellido;
            $this->cedula = $cliente->cedula;
            $this->telefono = $cliente->telefono ?? '';
            $this->email = $cliente->email ?? '';
            $this->direccion = $cliente->direccion;
            $this->barrio_id = $cliente->barrio_id;
            $this->cobrador_id = $cliente->cobrador_id;
            $this->tarifa_id = $cliente->tarifa_id;
            $this->descuento_especial = $cliente->descuento_especial ?? 0;
            $this->observaciones = $cliente->observaciones ?? '';
            $this->estado = $cliente->estado;
            $this->esEdicion = true;

        } catch (\Exception $e) {
            session()->flash('error', 'Cliente no encontrado');
            return redirect()->route('clientes.index');
        }
    }

    protected function rules()
    {
        $empresaId = Auth::user()->empresa_id;
        
        return [
            'nombre' => 'required|string|max:100|min:2',
            'apellido' => 'required|string|max:100|min:2',
            'cedula' => [
                'required',
                'string',
                'max:20',
                Rule::unique('clientes', 'cedula')
                    ->where('empresa_id', $empresaId)
                    ->ignore($this->clienteId)
            ],
            'telefono' => 'nullable|string|max:20',
            'email' => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('clientes', 'email')
                    ->where('empresa_id', $empresaId)
                    ->ignore($this->clienteId)
            ],
            'direccion' => 'required|string|max:255',
            'barrio_id' => 'required|exists:barrios,id',
            'cobrador_id' => 'required|exists:cobradores,id',
            'tarifa_id' => 'required|exists:tarifas,id',
            'descuento_especial' => 'numeric|min:0|max:100',
            'observaciones' => 'nullable|string|max:500',
            'estado' => 'required|in:activo,suspendido,retirado,cortado'
        ];
    }

    protected function validationAttributes()
    {
        return [
            'nombre' => 'nombre',
            'apellido' => 'apellido',
            'cedula' => 'cédula',
            'telefono' => 'teléfono',
            'email' => 'email',
            'direccion' => 'dirección',
            'barrio_id' => 'barrio',
            'cobrador_id' => 'cobrador',
            'tarifa_id' => 'tarifa',
            'descuento_especial' => 'descuento especial',
            'observaciones' => 'observaciones',
            'estado' => 'estado'
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function guardar()
    {
        $this->cargando = true;

        try {
            $this->validate();

            // Validar que el usuario tenga empresa asignada
            $empresaId = Auth::user()->empresa_id;
            if (!$empresaId) {
                $this->dispatch('toast', [
                    'message' => 'Error: Usuario no tiene empresa asignada. Contacte al administrador.',
                    'type' => 'error'
                ]);
                $this->cargando = false;
                return;
            }

            $clienteService = app(ClienteService::class);

            $datos = [
                'nombre' => $this->nombre,
                'apellido' => $this->apellido,
                'cedula' => $this->cedula,
                'telefono' => $this->telefono ?: null,
                'email' => $this->email ?: null,
                'direccion' => $this->direccion,
                'barrio_id' => $this->barrio_id,
                'cobrador_id' => $this->cobrador_id,
                'tarifa_id' => $this->tarifa_id,
                'descuento_especial' => $this->descuento_especial,
                'observaciones' => $this->observaciones ?: null,
                'estado' => $this->estado,
                'empresa_id' => $empresaId
            ];

            if ($this->esEdicion) {
                $resultado = $clienteService->actualizarPorId($this->clienteId, $datos);
            } else {
                $resultado = $clienteService->crear($datos);
            }

            if ($resultado['success']) {
                session()->flash('message', $resultado['message']);
                $this->dispatch('clienteActualizado');
                return redirect()->route('clientes.index');
            } else {
                session()->flash('error', $resultado['message']);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Los errores de validación se muestran automáticamente
            throw $e;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar cliente: ' . $e->getMessage());
        } finally {
            $this->cargando = false;
        }
    }

    public function cancelar()
    {
        return redirect()->route('clientes.index');
    }

    public function render()
    {
        $empresaId = Auth::user()->empresa_id;

        $barrios = Barrio::where('empresa_id', $empresaId)
            ->orderBy('nombre')
            ->get();

        $cobradores = Cobrador::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        $tarifas = Tarifa::where('empresa_id', $empresaId)
            ->activas()
            ->orderBy('nombre')
            ->get();

        return view('livewire.clientes.cliente-form', [
            'barrios' => $barrios,
            'cobradores' => $cobradores,
            'tarifas' => $tarifas
        ]);
    }
}