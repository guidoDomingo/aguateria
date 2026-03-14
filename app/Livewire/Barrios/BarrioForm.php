<?php

namespace App\Livewire\Barrios;

use App\Models\Barrio;
use App\Models\Ciudad;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BarrioForm extends Component
{
    public $barrioId = null;
    public $nombre = '';
    public $descripcion = '';
    public $ciudad_id = '';
    public $referencia = '';
    public $latitud = '';
    public $longitud = '';
    public $activo = true;

    public $modo = 'crear'; // crear o editar

    public function mount($barrioId = null)
    {
        if ($barrioId) {
            $this->modo = 'editar';
            $this->barrioId = $barrioId;
            $this->cargarBarrio();
        }
    }

    private function cargarBarrio()
    {
        $barrio = Barrio::where('empresa_id', Auth::user()->empresa_id)
                       ->findOrFail($this->barrioId);

        $this->nombre = $barrio->nombre;
        $this->descripcion = $barrio->descripcion ?? '';
        $this->ciudad_id = $barrio->ciudad_id;
        $this->referencia = $barrio->referencia ?? '';
        $this->latitud = $barrio->latitud;
        $this->longitud = $barrio->longitud;
        $this->activo = $barrio->activo;
    }

    public function rules()
    {
        $empresaId = Auth::user()->empresa_id;
        
        return [
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('barrios', 'nombre')
                    ->where('empresa_id', $empresaId)
                    ->ignore($this->barrioId)
            ],
            'descripcion' => 'nullable|string|max:500',
            'ciudad_id' => 'required|exists:ciudades,id',
            'referencia' => 'nullable|string|max:255',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'activo' => 'boolean'
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Este nombre de barrio ya existe.',
            'ciudad_id.required' => 'La ciudad es obligatoria.',
            'ciudad_id.exists' => 'La ciudad seleccionada no es válida.',
            'latitud.between' => 'La latitud debe estar entre -90 y 90.',
            'longitud.between' => 'La longitud debe estar entre -180 y 180.',
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
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'ciudad_id' => $this->ciudad_id,
            'referencia' => $this->referencia,
            'latitud' => $this->latitud ?: null,
            'longitud' => $this->longitud ?: null,
            'activo' => $this->activo,
        ];

        if ($this->modo === 'editar') {
            $barrio = Barrio::where('empresa_id', $empresaId)->findOrFail($this->barrioId);
            $barrio->update($data);
            $mensaje = 'Barrio actualizado exitosamente.';
        } else {
            Barrio::create($data);
            $mensaje = 'Barrio creado exitosamente.';
        }

        $this->dispatch('toast', [
            'message' => $mensaje,
            'type' => 'success'
        ]);

        return redirect()->route('barrios.index');
    }

    public function cancelar()
    {
        return redirect()->route('barrios.index');
    }

    public function render()
    {
        $ciudades = Ciudad::where('activo', true)
                         ->orderBy('nombre')
                         ->get();

        return view('livewire.barrios.barrio-form', [
            'ciudades' => $ciudades
        ])->layout('components.layouts.app', [
            'title' => $this->modo === 'crear' ? 'Crear Barrio' : 'Editar Barrio'
        ]);
    }
}