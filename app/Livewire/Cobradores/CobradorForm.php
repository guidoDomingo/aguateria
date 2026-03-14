<?php

namespace App\Livewire\Cobradores;

use App\Models\Cobrador;
use App\Models\Zona;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CobradorForm extends Component
{
    public $cobradorId = null;
    public $codigo = '';
    public $nombre = '';
    public $apellido = '';
    public $cedula = '';
    public $telefono = '';
    public $email = '';
    public $direccion = '';
    public $zona_id = '';
    public $user_id = '';
    public $comision_porcentaje = 0;
    public $comision_fija = 0;
    public $fecha_ingreso = '';
    public $fecha_salida = '';
    public $estado = 'activo';
    public $observaciones = '';

    public $modo = 'crear'; // crear o editar

    public function mount($cobradorId = null)
    {
        if ($cobradorId) {
            $this->modo = 'editar';
            $this->cobradorId = $cobradorId;
            $this->cargarCobrador();
        } else {
            $this->fecha_ingreso = now()->format('Y-m-d');
        }
    }

    private function cargarCobrador()
    {
        $cobrador = Cobrador::where('empresa_id', Auth::user()->empresa_id)
                           ->findOrFail($this->cobradorId);

        $this->codigo = $cobrador->codigo;
        $this->nombre = $cobrador->nombre;
        $this->apellido = $cobrador->apellido;
        $this->cedula = $cobrador->cedula;
        $this->telefono = $cobrador->telefono;
        $this->email = $cobrador->email;
        $this->direccion = $cobrador->direccion;
        $this->zona_id = $cobrador->zona_id;
        $this->user_id = $cobrador->user_id;
        $this->comision_porcentaje = $cobrador->comision_porcentaje;
        $this->comision_fija = $cobrador->comision_fija;
        $this->fecha_ingreso = $cobrador->fecha_ingreso?->format('Y-m-d');
        $this->fecha_salida = $cobrador->fecha_salida?->format('Y-m-d');
        $this->estado = $cobrador->estado;
        $this->observaciones = $cobrador->observaciones ?? '';
    }

    public function rules()
    {
        $empresaId = Auth::user()->empresa_id;
        
        return [
            'codigo' => [
                'required',
                'string',
                'max:20',
                Rule::unique('cobradores', 'codigo')
                    ->where('empresa_id', $empresaId)
                    ->ignore($this->cobradorId)
            ],
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'cedula' => [
                'required',
                'string',
                'max:20',
                Rule::unique('cobradores', 'cedula')
                    ->where('empresa_id', $empresaId)
                    ->ignore($this->cobradorId)
            ],
            'telefono' => 'nullable|string|max:20',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('cobradores', 'email')
                    ->where('empresa_id', $empresaId)
                    ->ignore($this->cobradorId)
            ],
            'direccion' => 'nullable|string|max:255',
            'zona_id' => 'required|exists:zonas,id',
            'user_id' => 'nullable|exists:users,id',
            'comision_porcentaje' => 'nullable|numeric|min:0|max:100',
            'comision_fija' => 'nullable|numeric|min:0',
            'fecha_ingreso' => 'required|date',
            'fecha_salida' => 'nullable|date|after_or_equal:fecha_ingreso',
            'estado' => 'required|in:activo,inactivo,suspendido',
            'observaciones' => 'nullable|string|max:1000'
        ];
    }

    public function messages()
    {
        return [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique' => 'Este código ya está en uso.',
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'cedula.required' => 'La cédula es obligatoria.',
            'cedula.unique' => 'Esta cédula ya está registrada.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.unique' => 'Este email ya está en uso.',
            'zona_id.required' => 'La zona es obligatoria.',
            'zona_id.exists' => 'La zona seleccionada no es válida.',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria.',
            'fecha_salida.after_or_equal' => 'La fecha de salida debe ser posterior a la fecha de ingreso.',
            'estado.required' => 'El estado es obligatorio.',
        ];
    }

    public function guardar()
    {
        $this->validate();

        $empresaId = Auth::user()->empresa_id;

        // Validar que el usuario tenga empresa asignada
        if (!$empresaId) {
            $this->dispatch('toast', [
                'message' => 'Error: Usuario no tiene empresa asignada. Contacte al administrador.',
                'type' => 'error'
            ]);
            return;
        }

        $data = [
            'empresa_id' => $empresaId,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'cedula' => $this->cedula,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'direccion' => $this->direccion,
            'zona_id' => $this->zona_id,
            'user_id' => $this->user_id ?: null,
            'comision_porcentaje' => $this->comision_porcentaje ?: 0,
            'comision_fija' => $this->comision_fija ?: 0,
            'fecha_ingreso' => $this->fecha_ingreso,
            'fecha_salida' => $this->fecha_salida ?: null,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,
        ];

        if ($this->modo === 'editar') {
            $cobrador = Cobrador::where('empresa_id', $empresaId)->findOrFail($this->cobradorId);
            $cobrador->update($data);
            $mensaje = 'Cobrador actualizado exitosamente.';
        } else {
            Cobrador::create($data);
            $mensaje = 'Cobrador creado exitosamente.';
        }

        $this->dispatch('toast', [
            'message' => $mensaje,
            'type' => 'success'
        ]);

        return redirect()->route('cobradores.index');
    }

    public function cancelar()
    {
        return redirect()->route('cobradores.index');
    }

    public function render()
    {
        $empresaId = Auth::user()->empresa_id;

        $zonas = Zona::where('empresa_id', $empresaId)
                    ->orderBy('nombre')
                    ->get();

        $usuarios = User::where('empresa_id', $empresaId)
                       ->where('estado', 'activo')
                       ->whereNotIn('id', function($query) use ($empresaId) {
                           $query->select('user_id')
                                 ->from('cobradores')
                                 ->where('empresa_id', $empresaId)
                                 ->where('estado', 'activo')
                                 ->whereNotNull('user_id')
                                 ->when($this->cobradorId, function($q) {
                                     $q->where('id', '!=', $this->cobradorId);
                                 });
                       })
                       ->orderBy('name')
                       ->get();

        return view('livewire.cobradores.cobrador-form', [
            'zonas' => $zonas,
            'usuarios' => $usuarios
        ])->layout('components.layouts.app', [
            'title' => $this->modo === 'crear' ? 'Crear Cobrador' : 'Editar Cobrador'
        ]);
    }
}