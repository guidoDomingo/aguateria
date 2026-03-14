<?php

namespace App\Livewire\Tarifas;

use App\Models\Tarifa;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TarifaForm extends Component
{
    public $tarifaId = null;
    public $codigo = '';
    public $nombre = '';
    public $descripcion = '';
    public $monto_mensual = 0;
    public $genera_mora = true;
    public $monto_mora = 0;
    public $tipo_mora = 'fijo';
    public $dias_vencimiento = 30;
    public $tipo_vencimiento = 'dias_corridos';
    public $dia_fijo_vencimiento = 5;
    public $dias_gracia = 5;
    public $costo_reconexion = 0;
    public $corte_automatico = false;
    public $dias_corte = null;
    public $estado = 'activa';

    public $modo = 'crear'; // crear o editar

    public function mount($tarifaId = null)
    {
        if ($tarifaId) {
            $this->modo = 'editar';
            $this->tarifaId = $tarifaId;
            $this->cargarTarifa();
        }
    }

    private function cargarTarifa()
    {
        $tarifa = Tarifa::where('empresa_id', Auth::user()->empresa_id)
                       ->findOrFail($this->tarifaId);

        $this->codigo = $tarifa->codigo;
        $this->nombre = $tarifa->nombre;
        $this->descripcion = $tarifa->descripcion ?? '';
        $this->monto_mensual = $tarifa->monto_mensual;
        $this->genera_mora = $tarifa->genera_mora;
        $this->monto_mora = $tarifa->monto_mora;
        $this->tipo_mora = $tarifa->tipo_mora;
        $this->dias_vencimiento = $tarifa->dias_vencimiento;
        $this->tipo_vencimiento = $tarifa->tipo_vencimiento ?? 'dias_corridos';
        $this->dia_fijo_vencimiento = $tarifa->dia_fijo_vencimiento ?? 5;
        $this->dias_gracia = $tarifa->dias_gracia;
        $this->costo_reconexion = $tarifa->costo_reconexion;
        $this->corte_automatico = $tarifa->corte_automatico;
        $this->dias_corte = $tarifa->dias_corte;
        $this->estado = $tarifa->estado;
    }

    public function rules()
    {
        $empresaId = Auth::user()->empresa_id;
        
        return [
            'codigo' => [
                'required',
                'string',
                'max:20',
                Rule::unique('tarifas', 'codigo')
                    ->where('empresa_id', $empresaId)
                    ->ignore($this->tarifaId)
            ],
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tarifas', 'nombre')
                    ->where('empresa_id', $empresaId)
                    ->ignore($this->tarifaId)
            ],
            'descripcion' => 'nullable|string|max:500',
            'monto_mensual' => 'required|numeric|min:0',
            'genera_mora' => 'boolean',
            'monto_mora' => 'nullable|numeric|min:0',
            'tipo_mora' => 'required|in:fijo,porcentaje',
            'dias_vencimiento' => 'required|integer|min:1|max:365',
            'tipo_vencimiento' => 'required|in:dias_corridos,dia_fijo',
            'dia_fijo_vencimiento' => 'required_if:tipo_vencimiento,dia_fijo|nullable|integer|min:1|max:31',
            'dias_gracia' => 'required|integer|min:0|max:30',
            'costo_reconexion' => 'nullable|numeric|min:0',
            'corte_automatico' => 'boolean',
            'dias_corte' => 'nullable|integer|min:0|max:365',
            'estado' => 'required|in:activa,inactiva'
        ];
    }

    public function messages()
    {
        return [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique' => 'Este código ya está en uso.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Este nombre ya está en uso.',
            'monto_mensual.required' => 'El monto mensual es obligatorio.',
            'monto_mensual.min' => 'El monto mensual debe ser mayor a 0.',
            'dias_vencimiento.required' => 'Los días de vencimiento son obligatorios.',
            'dias_vencimiento.min' => 'Debe ser al menos 1 día.',
            'dias_vencimiento.max' => 'No puede ser mayor a 365 días.',
            'dias_gracia.max' => 'No puede ser mayor a 30 días.',
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
            'descripcion' => $this->descripcion,
            'monto_mensual' => $this->monto_mensual,
            'genera_mora' => $this->genera_mora,
            'monto_mora' => $this->genera_mora ? ($this->monto_mora ?: 0) : 0,
            'tipo_mora' => $this->tipo_mora,
            'dias_vencimiento' => $this->dias_vencimiento,
            'tipo_vencimiento' => $this->tipo_vencimiento,
            'dia_fijo_vencimiento' => $this->tipo_vencimiento === 'dia_fijo' ? $this->dia_fijo_vencimiento : null,
            'dias_gracia' => $this->dias_gracia,
            'costo_reconexion' => $this->costo_reconexion ?: 0,
            'corte_automatico' => $this->corte_automatico,
            'dias_corte' => $this->corte_automatico ? $this->dias_corte : null,
            'estado' => $this->estado,
        ];

        if ($this->modo === 'editar') {
            $tarifa = Tarifa::where('empresa_id', $empresaId)->findOrFail($this->tarifaId);
            $tarifa->update($data);
            $mensaje = 'Tarifa actualizada exitosamente.';
        } else {
            Tarifa::create($data);
            $mensaje = 'Tarifa creada exitosamente.';
        }

        $this->dispatch('toast', [
            'message' => $mensaje,
            'type' => 'success'
        ]);

        return redirect()->route('tarifas.index');
    }

    public function cancelar()
    {
        return redirect()->route('tarifas.index');
    }

    public function render()
    {
        return view('livewire.tarifas.tarifa-form')
            ->layout('components.layouts.app', [
                'title' => $this->modo === 'crear' ? 'Crear Tarifa' : 'Editar Tarifa'
            ]);
    }
}