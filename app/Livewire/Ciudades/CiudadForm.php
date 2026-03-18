<?php

namespace App\Livewire\Ciudades;

use App\Models\Ciudad;
use Livewire\Component;
use Illuminate\Validation\Rule;

class CiudadForm extends Component
{
    public $ciudadId = null;
    public $nombre = '';
    public $departamento = '';
    public $pais = 'Paraguay';
    public $codigo_postal = '';
    public $activo = true;

    public $modo = 'crear';

    public static array $departamentos = [
        'Alto Paraguay',
        'Alto Paraná',
        'Amambay',
        'Boquerón',
        'Caaguazú',
        'Caazapá',
        'Canindeyú',
        'Central',
        'Concepción',
        'Cordillera',
        'Guairá',
        'Itapúa',
        'Misiones',
        'Ñeembucú',
        'Paraguarí',
        'Presidente Hayes',
        'San Pedro',
    ];

    public function mount($ciudadId = null)
    {
        if ($ciudadId) {
            $this->modo = 'editar';
            $this->ciudadId = $ciudadId;
            $ciudad = Ciudad::findOrFail($ciudadId);
            $this->nombre = $ciudad->nombre;
            $this->departamento = $ciudad->departamento;
            $this->pais = $ciudad->pais;
            $this->codigo_postal = $ciudad->codigo_postal ?? '';
            $this->activo = $ciudad->activo;
        }
    }

    public function rules()
    {
        return [
            'nombre' => [
                'required', 'string', 'max:100',
                Rule::unique('ciudades', 'nombre')
                    ->where('departamento', $this->departamento)
                    ->where('pais', $this->pais)
                    ->ignore($this->ciudadId),
            ],
            'departamento' => 'required|string|max:100',
            'pais'         => 'required|string|max:100',
            'codigo_postal'=> 'nullable|string|max:10',
            'activo'       => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'nombre.required'      => 'El nombre es obligatorio.',
            'nombre.unique'        => 'Ya existe una ciudad con ese nombre en el mismo departamento.',
            'departamento.required'=> 'El departamento es obligatorio.',
        ];
    }

    public function guardar()
    {
        $this->validate();

        $data = [
            'nombre'        => $this->nombre,
            'departamento'  => $this->departamento,
            'pais'          => $this->pais,
            'codigo_postal' => $this->codigo_postal ?: null,
            'activo'        => $this->activo,
        ];

        if ($this->modo === 'editar') {
            Ciudad::findOrFail($this->ciudadId)->update($data);
            $mensaje = 'Ciudad actualizada exitosamente.';
        } else {
            Ciudad::create($data);
            $mensaje = 'Ciudad creada exitosamente.';
        }

        $this->dispatch('toast', ['message' => $mensaje, 'type' => 'success']);

        return redirect()->route('ciudades.index');
    }

    public function cancelar()
    {
        return redirect()->route('ciudades.index');
    }

    public function render()
    {
        return view('livewire.ciudades.ciudad-form')->layout('components.layouts.app', [
            'title' => $this->modo === 'crear' ? 'Nueva Ciudad' : 'Editar Ciudad',
        ]);
    }
}
