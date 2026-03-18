<?php

namespace App\Livewire\ConfiguracionesRecibos;

use Livewire\Component;
use App\Models\ConfiguracionRecibo;
use Illuminate\Support\Facades\Auth;

class ConfiguracionRecibos extends Component
{
    // Propiedades del modelo
    public $template = 'compacto';
    public $tamaño_papel = 'Ticket';
    public $orientacion = 'portrait';
    public $mostrar_logo = true;
    public $mostrar_cedula_cliente = true;
    public $mostrar_direccion_cliente = true;
    public $mostrar_telefono_empresa = true;
    public $mostrar_detalle_facturas = false;
    public $mostrar_firma = true;
    public $titulo_personalizado = '';
    public $mensaje_agradecimiento = 'Gracias por su pago';
    public $pie_pagina = '';
    public $color_principal = '#333333';
    public $color_secundario = '#666666';
    public $fuente = 'Arial';
    public $tamaño_fuente = 12;

    // Control de UI
    public $configuracion;
    public $mostrarPreview = false;

    protected $rules = [
        'template' => 'required|in:compacto,standard,detallado',
        'tamaño_papel' => 'required|in:A4,Carta,Ticket,Medio_Oficio',
        'orientacion' => 'required|in:portrait,landscape',
        'mostrar_logo' => 'boolean',
        'mostrar_cedula_cliente' => 'boolean',
        'mostrar_direccion_cliente' => 'boolean',
        'mostrar_telefono_empresa' => 'boolean',
        'mostrar_detalle_facturas' => 'boolean',
        'mostrar_firma' => 'boolean',
        'titulo_personalizado' => 'nullable|string|max:100',
        'mensaje_agradecimiento' => 'nullable|string|max:500',
        'pie_pagina' => 'nullable|string|max:500',
        'color_principal' => 'required|regex:/^#[a-fA-F0-9]{6}$/',
        'color_secundario' => 'required|regex:/^#[a-fA-F0-9]{6}$/',
        'fuente' => 'required|in:Arial,Times,Helvetica,Courier',
        'tamaño_fuente' => 'required|integer|min:8|max:20'
    ];

    public function mount()
    {
        $this->cargarConfiguracion();
    }

    public function cargarConfiguracion()
    {
        $this->configuracion = ConfiguracionRecibo::getConfiguracionEmpresa(Auth::user()->empresa_id);
        
        // Asignar valores a las propiedades
        $this->template = $this->configuracion->template;
        $this->tamaño_papel = $this->configuracion->tamaño_papel;
        $this->orientacion = $this->configuracion->orientacion;
        $this->mostrar_logo = $this->configuracion->mostrar_logo;
        $this->mostrar_cedula_cliente = $this->configuracion->mostrar_cedula_cliente;
        $this->mostrar_direccion_cliente = $this->configuracion->mostrar_direccion_cliente;
        $this->mostrar_telefono_empresa = $this->configuracion->mostrar_telefono_empresa;
        $this->mostrar_detalle_facturas = $this->configuracion->mostrar_detalle_facturas;
        $this->mostrar_firma = $this->configuracion->mostrar_firma;
        $this->titulo_personalizado = $this->configuracion->titulo_personalizado ?? '';
        $this->mensaje_agradecimiento = $this->configuracion->mensaje_agradecimiento ?? 'Gracias por su pago';
        $this->pie_pagina = $this->configuracion->pie_pagina ?? '';
        $this->color_principal = $this->configuracion->color_principal;
        $this->color_secundario = $this->configuracion->color_secundario;
        $this->fuente = $this->configuracion->fuente;
        $this->tamaño_fuente = $this->configuracion->tamaño_fuente;
    }

    public function guardarConfiguracion()
    {
        $this->validate();

        $datos = [
            'empresa_id' => Auth::user()->empresa_id,
            'template' => $this->template,
            'tamaño_papel' => $this->tamaño_papel,
            'orientacion' => $this->orientacion,
            'mostrar_logo' => $this->mostrar_logo,
            'mostrar_cedula_cliente' => $this->mostrar_cedula_cliente,
            'mostrar_direccion_cliente' => $this->mostrar_direccion_cliente,
            'mostrar_telefono_empresa' => $this->mostrar_telefono_empresa,
            'mostrar_detalle_facturas' => $this->mostrar_detalle_facturas,
            'mostrar_firma' => $this->mostrar_firma,
            'titulo_personalizado' => $this->titulo_personalizado ?: null,
            'mensaje_agradecimiento' => $this->mensaje_agradecimiento,
            'pie_pagina' => $this->pie_pagina ?: null,
            'color_principal' => $this->color_principal,
            'color_secundario' => $this->color_secundario,
            'fuente' => $this->fuente,
            'tamaño_fuente' => $this->tamaño_fuente,
        ];

        ConfiguracionRecibo::updateOrCreate(
            ['empresa_id' => Auth::user()->empresa_id],
            $datos
        );

        session()->flash('message', 'Configuración guardada exitosamente');
        $this->cargarConfiguracion();
    }

    public function restaurarDefecto()
    {
        $this->configuracion = ConfiguracionRecibo::getConfiguracionDefecto();
        $this->cargarConfiguracion();
        session()->flash('message', 'Configuración restaurada a valores predeterminados');
    }

    public function togglePreview()
    {
        $this->mostrarPreview = !$this->mostrarPreview;
    }

    // Propiedades computadas
    public function getTemplatesDisponiblesProperty()
    {
        return [
            'compacto' => 'Compacto (350px)',
            'standard' => 'Estándar (500px)',
            'detallado' => 'Detallado (600px)'
        ];
    }

    public function getTamañosPapelProperty()
    {
        return [
            'Ticket' => 'Ticket (80mm)',
            'A4' => 'A4 (210x297mm)',
            'Carta' => 'Carta (216x279mm)',
            'Medio_Oficio' => 'Medio Oficio (216x140mm)'
        ];
    }

    public function getFuentesDisponiblesProperty()
    {
        return [
            'Arial' => 'Arial',
            'Times' => 'Times New Roman',
            'Helvetica' => 'Helvetica',
            'Courier' => 'Courier New'
        ];
    }

    public function render()
    {
        return view('livewire.configuraciones-recibos.configuracion-recibos');
    }
}
