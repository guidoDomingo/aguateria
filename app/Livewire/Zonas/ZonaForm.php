<?php

namespace App\Livewire\Zonas;

use App\Models\Zona;
use App\Models\Barrio;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ZonaForm extends Component
{
    public $zonaId;
    public $nombre = '';
    public $descripcion = '';
    public $barrio_id = '';
    public $color = '#3B82F6';
    public $orden = 0;
    public $activo = true;
    public $cargando = false;

    public function mount($zonaId = null)
    {
        $this->zonaId = $zonaId;
        
        if ($zonaId) {
            $zona = Zona::where('empresa_id', Auth::user()->empresa_id)
                        ->findOrFail($zonaId);
            
            $this->nombre = $zona->nombre;
            $this->descripcion = $zona->descripcion;
            $this->barrio_id = $zona->barrio_id;
            $this->color = $zona->color ?: '#3B82F6';
            $this->orden = $zona->orden;
            $this->activo = $zona->activo;
        }
    }

    public function rules()
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('zonas')->where(function($query) {
                    return $query->where('empresa_id', Auth::user()->empresa_id)
                                ->where('barrio_id', $this->barrio_id);
                })->ignore($this->zonaId)
            ],
            'descripcion' => 'nullable|string|max:500',
            'barrio_id' => 'required|exists:barrios,id',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'orden' => 'required|integer|min:0|max:999',
            'activo' => 'boolean'
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre de la zona es obligatorio',
            'nombre.unique' => 'Ya existe una zona con este nombre en el barrio seleccionado',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres',
            'barrio_id.required' => 'Debe seleccionar un barrio',
            'barrio_id.exists' => 'El barrio seleccionado no es válido',
            'color.regex' => 'El color debe ser un código hexadecimal válido (ej: #FF0000)',
            'orden.required' => 'El orden es obligatorio',
            'orden.integer' => 'El orden debe ser un número entero',
            'orden.min' => 'El orden debe ser mayor o igual a 0',
            'orden.max' => 'El orden no puede ser mayor a 999',
            'descripcion.max' => 'La descripción no puede tener más de 500 caracteres'
        ];
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

            // Validar que el barrio pertenezca a la empresa
            $barrio = Barrio::where('id', $this->barrio_id)
                           ->where('empresa_id', $empresaId)
                           ->first();

            if (!$barrio) {
                $this->dispatch('toast', [
                    'message' => 'Error: El barrio seleccionado no pertenece a su empresa.',
                    'type' => 'error'
                ]);
                $this->cargando = false;
                return;
            }

            $datos = [
                'empresa_id' => $empresaId,
                'nombre' => trim($this->nombre),
                'descripcion' => $this->descripcion ? trim($this->descripcion) : null,
                'barrio_id' => $this->barrio_id,
                'color' => $this->color,
                'orden' => $this->orden,
                'activo' => $this->activo
            ];

            if ($this->zonaId) {
                // Actualizar
                $zona = Zona::where('empresa_id', $empresaId)
                           ->findOrFail($this->zonaId);
                $zona->update($datos);
                
                $mensaje = 'Zona actualizada correctamente';
            } else {
                // Crear
                Zona::create($datos);
                $mensaje = 'Zona creada correctamente';
            }

            $this->dispatch('toast', [
                'message' => $mensaje,
                'type' => 'success'
            ]);

            return $this->redirect(route('zonas.index'));

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->cargando = false;
            throw $e;
        } catch (\Exception $e) {
            $this->cargando = false;
            
            $this->dispatch('toast', [
                'message' => 'Error al guardar la zona: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function cancelar()
    {
        return $this->redirect(route('zonas.index'));
    }

    public function render()
    {
        $empresaId = Auth::user()->empresa_id;

        $barrios = Barrio::where('empresa_id', $empresaId)
                        ->activos()
                        ->orderBy('nombre')
                        ->get();

        return view('livewire.zonas.zona-form', [
            'barrios' => $barrios
        ]);
    }
}