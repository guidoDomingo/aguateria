<?php

namespace App\Livewire\Configuracion;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

class ConfiguracionFacturacion extends Component
{
    public int $dia_facturacion = 1;
    public string $hora = '00';
    public string $minuto = '05';
    public string $tipo_comprobante = 'factura';

    public function mount()
    {
        $empresa = Auth::user()->empresa;
        $config  = $empresa->configuraciones ?? [];

        $this->dia_facturacion  = (int) ($config['dia_facturacion'] ?? 1);
        $this->tipo_comprobante = $config['tipo_comprobante'] ?? 'factura';

        $hora_facturacion = $config['hora_facturacion'] ?? '00:05';
        [$this->hora, $this->minuto] = explode(':', $hora_facturacion);
    }

    public function rules()
    {
        return [
            'dia_facturacion'  => 'required|integer|min:1|max:28',
            'hora'             => 'required|integer|min:0|max:23',
            'minuto'           => 'required|in:00,05,10,15,20,25,30,35,40,45,50,55',
            'tipo_comprobante' => 'required|in:factura,recibo',
        ];
    }

    public function messages()
    {
        return [
            'dia_facturacion.min' => 'El día mínimo es 1.',
            'dia_facturacion.max' => 'El día máximo es 28.',
            'hora.required'  => 'La hora es obligatoria.',
            'minuto.required'=> 'Los minutos son obligatorios.',
        ];
    }

    public function guardar()
    {
        $this->validate();

        $empresa = Auth::user()->empresa;
        $configuraciones = $empresa->configuraciones ?? [];
        $configuraciones['dia_facturacion']  = $this->dia_facturacion;
        $configuraciones['hora_facturacion'] = str_pad($this->hora, 2, '0', STR_PAD_LEFT) . ':' . $this->minuto;
        $configuraciones['tipo_comprobante'] = $this->tipo_comprobante;

        $empresa->update(['configuraciones' => $configuraciones]);

        $this->dispatch('toast', [
            'message' => "Configuración guardada: día {$this->dia_facturacion} a las {$configuraciones['hora_facturacion']} hs.",
            'type' => 'success',
        ]);
    }

    public ?string $outputCron = null;

    public function ejecutarAhora()
    {
        $empresa = Auth::user()->empresa;

        try {
            // Resetear período 'facturado' del mes actual para permitir re-ejecución manual
            $año = now()->year;
            $mes = now()->month;
            \App\Models\PeriodoFacturacion::where('empresa_id', $empresa->id)
                ->where('año', $año)
                ->where('mes', $mes)
                ->where('estado', 'facturado')
                ->update(['estado' => 'abierto']);

            Artisan::call('aguateria:facturacion-automatica', [
                '--empresa' => $empresa->id,
                '--force'   => true,
            ]);

            $this->outputCron = Artisan::output();

            $this->dispatch('toast', [
                'message' => 'Facturación ejecutada manualmente.',
                'type'    => 'success',
            ]);
        } catch (\Exception $e) {
            $this->outputCron = 'ERROR: ' . $e->getMessage();
            $this->dispatch('toast', [
                'message' => 'Error al ejecutar: ' . $e->getMessage(),
                'type'    => 'error',
            ]);
        }
    }

    public function render()
    {
        $empresa = Auth::user()->empresa;
        $config  = $empresa->configuraciones ?? [];

        $diaActual  = (int) ($config['dia_facturacion'] ?? 1);
        $horaActual = $config['hora_facturacion'] ?? '00:05';

        $hoy = now();
        $proximaFecha = $hoy->day <= $diaActual
            ? $hoy->copy()->setDay($diaActual)
            : $hoy->copy()->addMonth()->setDay($diaActual);

        $horas   = range(0, 23);
        $minutos = ['00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55'];

        return view('livewire.configuracion.configuracion-facturacion', [
            'diaActual'    => $diaActual,
            'horaActual'   => $horaActual,
            'proximaFecha' => $proximaFecha,
            'horas'        => $horas,
            'minutos'      => $minutos,
        ])->layout('layouts.app', [
            'titulo'    => 'Configuración de Facturación',
            'subtitulo' => 'Define cuándo se genera la facturación automática mensual',
        ]);
    }
}
