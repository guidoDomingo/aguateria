<?php

namespace App\Livewire\Configuracion;

use Livewire\Component;
use App\Models\ConfiguracionRecibo;
use Illuminate\Support\Facades\Auth;

class ConfiguracionRecibos extends Component
{
    // Configuración de tamaño y formato
    public $tamaño_papel = '80mm';
    public $ancho_personalizado;
    public $alto_personalizado;
    public $orientacion = 'portrait';
    
    // Configuración de diseño
    public $plantilla = 'standard';
    public $color_header = '#2563eb';
    public $color_text = '#1f2937';
    public $color_background = '#ffffff';
    public $fuente = 'Arial';
    public $tamaño_fuente = 12;
    
    // Logo y encabezado
    public $mostrar_logo = true;
    public $posicion_logo = 'center';
    public $tamaño_logo = 100;
    
    // Información a mostrar
    public $mostrar_fecha = true;
    public $mostrar_hora = true;
    public $mostrar_direccion_empresa = true;
    public $mostrar_telefono_empresa = true;
    public $mostrar_email_empresa = true;
    public $mostrar_descripcion_detallada = true;
    public $mostrar_codigo_qr = false;
    
    // Mensajes personalizables
    public $mensaje_superior = '';
    public $mensaje_inferior = 'Gracias por su preferencia';
    public $terminos_condiciones = '';
    
    // Configuración de impresión
    public $impresion_automatica = false;
    public $margenes_superior = 10;
    public $margenes_inferior = 10;
    public $margenes_izquierdo = 10;
    public $margenes_derecho = 10;
    
    public $configuracion;
    public $guardando = false;
    public $mostrarPreview = false;

    protected $rules = [
        'tamaño_papel' => 'required|in:A4,80mm,58mm,carta,oficio,personalizado',
        'ancho_personalizado' => 'nullable|integer|min:50|max:500',
        'alto_personalizado' => 'nullable|integer|min:100|max:1000',
        'orientacion' => 'required|in:portrait,landscape',
        'plantilla' => 'required|in:standard,modern,classic,minimal',
        'fuente' => 'required|in:Arial,Times,Courier',
        'tamaño_fuente' => 'required|integer|min:8|max:24',
        'posicion_logo' => 'required|in:left,center,right',
        'tamaño_logo' => 'required|integer|min:50|max:200',
        'margenes_superior' => 'required|integer|min:0|max:50',
        'margenes_inferior' => 'required|integer|min:0|max:50',
        'margenes_izquierdo' => 'required|integer|min:0|max:50',
        'margenes_derecho' => 'required|integer|min:0|max:50',
        'mensaje_superior' => 'nullable|string|max:255',
        'mensaje_inferior' => 'nullable|string|max:255',
        'terminos_condiciones' => 'nullable|string|max:500',
    ];

    public function mount()
    {
        $this->cargarConfiguracion();
    }

    public function cargarConfiguracion()
    {
        $empresaId = Auth::user()->empresa_id;
        $this->configuracion = ConfiguracionRecibo::getConfiguracionParaEmpresa($empresaId);
        
        // Cargar datos en las propiedades del componente
        $this->fill($this->configuracion->only([
            'tamaño_papel', 'ancho_personalizado', 'alto_personalizado', 'orientacion',
            'plantilla', 'fuente', 'tamaño_fuente',
            'mostrar_logo', 'posicion_logo', 'tamaño_logo',
            'mostrar_fecha', 'mostrar_hora', 'mostrar_direccion_empresa',
            'mostrar_telefono_empresa', 'mostrar_email_empresa', 'mostrar_descripcion_detallada',
            'mostrar_codigo_qr', 'mensaje_superior', 'mensaje_inferior', 'terminos_condiciones',
            'impresion_automatica', 'margenes_superior', 'margenes_inferior',
            'margenes_izquierdo', 'margenes_derecho'
        ]));
        
        // Cargar colores
        $colores = $this->configuracion->colores;
        $this->color_header = $colores['header'] ?? '#2563eb';
        $this->color_text = $colores['text'] ?? '#1f2937';
        $this->color_background = $colores['background'] ?? '#ffffff';
    }

    public function guardarConfiguracion()
    {
        $this->validate();
        
        $this->guardando = true;
        
        try {
            $this->configuracion->update([
                'tamaño_papel' => $this->tamaño_papel,
                'ancho_personalizado' => $this->ancho_personalizado,
                'alto_personalizado' => $this->alto_personalizado,
                'orientacion' => $this->orientacion,
                'plantilla' => $this->plantilla,
                'colores' => [
                    'header' => $this->color_header,
                    'text' => $this->color_text,
                    'background' => $this->color_background,
                ],
                'fuente' => $this->fuente,
                'tamaño_fuente' => $this->tamaño_fuente,
                'mostrar_logo' => (bool) $this->mostrar_logo,
                'posicion_logo' => $this->posicion_logo,
                'tamaño_logo' => $this->tamaño_logo,
                'mostrar_fecha' => (bool) $this->mostrar_fecha,
                'mostrar_hora' => (bool) $this->mostrar_hora,
                'mostrar_direccion_empresa' => (bool) $this->mostrar_direccion_empresa,
                'mostrar_telefono_empresa' => (bool) $this->mostrar_telefono_empresa,
                'mostrar_email_empresa' => (bool) $this->mostrar_email_empresa,
                'mostrar_descripcion_detallada' => (bool) $this->mostrar_descripcion_detallada,
                'mostrar_codigo_qr' => (bool) $this->mostrar_codigo_qr,
                'mensaje_superior' => $this->mensaje_superior,
                'mensaje_inferior' => $this->mensaje_inferior,
                'terminos_condiciones' => $this->terminos_condiciones,
                'impresion_automatica' => (bool) $this->impresion_automatica,
                'margenes_superior' => $this->margenes_superior,
                'margenes_inferior' => $this->margenes_inferior,
                'margenes_izquierdo' => $this->margenes_izquierdo,
                'margenes_derecho' => $this->margenes_derecho,
            ]);
            
            session()->flash('message', 'Configuración de recibos guardada exitosamente');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar configuración: ' . $e->getMessage());
        } finally {
            $this->guardando = false;
        }
    }

    public function restaurarDefecto()
    {
        $this->tamaño_papel = '80mm';
        $this->orientacion = 'portrait';
        $this->plantilla = 'standard';
        $this->color_header = '#2563eb';
        $this->color_text = '#1f2937';
        $this->color_background = '#ffffff';
        $this->fuente = 'Arial';
        $this->tamaño_fuente = 12;
        $this->mostrar_logo = true;
        $this->posicion_logo = 'center';
        $this->tamaño_logo = 100;
        $this->mostrar_fecha = true;
        $this->mostrar_hora = true;
        $this->mostrar_direccion_empresa = true;
        $this->mostrar_telefono_empresa = true;
        $this->mostrar_email_empresa = true;
        $this->mostrar_descripcion_detallada = true;
        $this->mostrar_codigo_qr = false;
        $this->mensaje_superior = '';
        $this->mensaje_inferior = 'Gracias por su preferencia';
        $this->terminos_condiciones = '';
        $this->impresion_automatica = false;
        $this->margenes_superior = 10;
        $this->margenes_inferior = 10;
        $this->margenes_izquierdo = 10;
        $this->margenes_derecho = 10;
        
        session()->flash('message', 'Configuración restaurada a valores por defecto');
    }

    public function togglePreview()
    {
        $this->mostrarPreview = !$this->mostrarPreview;
    }

    public function getTamañosPapel()
    {
        return [
            'A4' => 'A4 (210 x 297 mm)',
            '80mm' => 'Ticket 80mm',
            '58mm' => 'Ticket 58mm', 
            'carta' => 'Carta (216 x 279 mm)',
            'oficio' => 'Oficio (216 x 330 mm)',
            'personalizado' => 'Tamaño personalizado'
        ];
    }

    public function getPlantillas()
    {
        return [
            'standard' => 'Estándar',
            'modern' => 'Moderno',
            'classic' => 'Clásico',
            'minimal' => 'Minimalista'
        ];
    }

    public function render()
    {
        return view('livewire.configuracion.configuracion-recibos')
            ->layout('layouts.app');
    }
}
