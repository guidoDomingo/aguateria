<?php

namespace App\Livewire\Configuracion;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ConfiguracionMoras extends Component
{
    public string $mora_tipo       = 'fijo';
    public float  $mora_valor      = 0;
    public int    $mora_dias_gracia = 0;
    public int    $meses_ultimo_aviso = 2;
    public int    $meses_desconexion  = 3;

    public function mount()
    {
        $config = Auth::user()->empresa->configuraciones ?? [];

        $this->mora_tipo          = $config['mora_tipo']           ?? 'fijo';
        $this->mora_valor         = (float) ($config['mora_valor']         ?? 0);
        $this->mora_dias_gracia   = (int)   ($config['mora_dias_gracia']   ?? 0);
        $this->meses_ultimo_aviso = (int)   ($config['meses_ultimo_aviso'] ?? 2);
        $this->meses_desconexion  = (int)   ($config['meses_desconexion']  ?? 3);
    }

    public function rules()
    {
        return [
            'mora_tipo'          => 'required|in:fijo,porcentaje',
            'mora_valor'         => 'required|numeric|min:0',
            'mora_dias_gracia'   => 'required|integer|min:0|max:30',
            'meses_ultimo_aviso' => 'required|integer|min:1|max:12',
            'meses_desconexion'  => 'required|integer|min:1|max:12',
        ];
    }

    public function guardar()
    {
        $this->validate();

        if ($this->meses_desconexion <= $this->meses_ultimo_aviso) {
            $this->addError('meses_desconexion', 'Los meses de desconexión deben ser mayores a los de último aviso.');
            return;
        }

        $empresa = Auth::user()->empresa;
        $config  = $empresa->configuraciones ?? [];

        $config['mora_tipo']          = $this->mora_tipo;
        $config['mora_valor']         = $this->mora_valor;
        $config['mora_dias_gracia']   = $this->mora_dias_gracia;
        $config['meses_ultimo_aviso'] = $this->meses_ultimo_aviso;
        $config['meses_desconexion']  = $this->meses_desconexion;

        $empresa->update(['configuraciones' => $config]);

        $this->dispatch('toast', [
            'message' => 'Configuración de moras guardada.',
            'type'    => 'success',
        ]);
    }

    public function render()
    {
        return view('livewire.configuracion.configuracion-moras')
            ->layout('layouts.app', [
                'titulo'    => 'Configuración de Moras y Avisos',
                'subtitulo' => 'Define cómo se aplican moras y cuándo se emiten avisos de corte',
            ]);
    }
}
