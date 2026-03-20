<?php

namespace App\Livewire\Configuracion;

use Livewire\Component;
use App\Models\ConfiguracionRecibo;
use Illuminate\Support\Facades\Auth;

class ConfiguracionRecibos extends Component
{
    public string $tamaño_papel         = '80mm';
    public ?int   $ancho_personalizado  = null;
    public ?int   $alto_personalizado   = null;
    public string $orientacion          = 'portrait';
    public string $plantilla            = 'standard';
    public string $color_header         = '#2563eb';
    public string $color_text           = '#1f2937';
    public string $color_background     = '#ffffff';
    public string $fuente               = 'Arial';
    public int    $tamaño_fuente        = 12;
    public bool   $mostrar_logo         = true;
    public string $posicion_logo        = 'center';
    public int    $tamaño_logo          = 100;
    public bool   $mostrar_fecha                = true;
    public bool   $mostrar_hora                 = true;
    public bool   $mostrar_direccion_empresa    = true;
    public bool   $mostrar_telefono_empresa     = true;
    public bool   $mostrar_email_empresa        = true;
    public bool   $mostrar_descripcion_detallada = true;
    public bool   $mostrar_codigo_qr            = false;
    public string $mensaje_superior     = '';
    public string $mensaje_inferior     = 'Gracias por su preferencia';
    public string $terminos_condiciones = '';
    public bool   $impresion_automatica = false;
    public int    $margenes_superior    = 10;
    public int    $margenes_inferior    = 10;
    public int    $margenes_izquierdo   = 10;
    public int    $margenes_derecho     = 10;
    public int    $copias                  = 1;
    public bool   $copia_cliente           = true;
    public bool   $copia_empresa           = false;
    public string $etiqueta_copia_cliente  = 'ORIGINAL - CLIENTE';
    public string $etiqueta_copia_empresa  = 'COPIA - EMPRESA';
    public bool   $guardando     = false;
    public string $pestañaActiva = 'formato';
    public $configuracion;

    protected $rules = [
        'tamaño_papel'           => 'required|in:A4,80mm,58mm,carta,oficio,personalizado',
        'ancho_personalizado'    => 'nullable|integer|min:50|max:500',
        'alto_personalizado'     => 'nullable|integer|min:100|max:1000',
        'orientacion'            => 'required|in:portrait,landscape',
        'plantilla'              => 'required|in:standard,modern,classic,minimal,recibo_dinero',
        'fuente'                 => 'required|in:Arial,Times,Courier',
        'tamaño_fuente'          => 'required|integer|min:8|max:24',
        'posicion_logo'          => 'required|in:left,center,right',
        'tamaño_logo'            => 'required|integer|min:50|max:200',
        'margenes_superior'      => 'required|integer|min:0|max:50',
        'margenes_inferior'      => 'required|integer|min:0|max:50',
        'margenes_izquierdo'     => 'required|integer|min:0|max:50',
        'margenes_derecho'       => 'required|integer|min:0|max:50',
        'mensaje_superior'       => 'nullable|string|max:255',
        'mensaje_inferior'       => 'nullable|string|max:255',
        'terminos_condiciones'   => 'nullable|string|max:500',
        'copias'                 => 'required|integer|min:1|max:5',
        'etiqueta_copia_cliente' => 'nullable|string|max:50',
        'etiqueta_copia_empresa' => 'nullable|string|max:50',
    ];

    public function mount(): void
    {
        $this->cargarConfiguracion();
    }

    public function cargarConfiguracion(): void
    {
        $this->configuracion = ConfiguracionRecibo::getConfiguracionParaEmpresa(Auth::user()->empresa_id);
        $c = $this->configuracion;

        $this->tamaño_papel              = $c->tamaño_papel         ?? '80mm';
        $this->ancho_personalizado       = $c->ancho_personalizado  ?? null;
        $this->alto_personalizado        = $c->alto_personalizado   ?? null;
        $this->orientacion               = $c->orientacion          ?? 'portrait';
        $this->plantilla                 = $c->plantilla            ?? 'standard';
        $this->fuente                    = $c->fuente               ?? 'Arial';
        $this->tamaño_fuente             = (int) ($c->tamaño_fuente ?? 12);
        $this->mostrar_logo              = (bool) ($c->mostrar_logo ?? true);
        $this->posicion_logo             = $c->posicion_logo        ?? 'center';
        $this->tamaño_logo               = (int) ($c->tamaño_logo   ?? 100);
        $this->mostrar_fecha             = (bool) ($c->mostrar_fecha ?? true);
        $this->mostrar_hora              = (bool) ($c->mostrar_hora  ?? true);
        $this->mostrar_direccion_empresa = (bool) ($c->mostrar_direccion_empresa  ?? true);
        $this->mostrar_telefono_empresa  = (bool) ($c->mostrar_telefono_empresa   ?? true);
        $this->mostrar_email_empresa     = (bool) ($c->mostrar_email_empresa      ?? true);
        $this->mostrar_descripcion_detallada = (bool) ($c->mostrar_descripcion_detallada ?? true);
        $this->mostrar_codigo_qr         = (bool) ($c->mostrar_codigo_qr ?? false);
        $this->mensaje_superior          = $c->mensaje_superior     ?? '';
        $this->mensaje_inferior          = $c->mensaje_inferior     ?? 'Gracias por su preferencia';
        $this->terminos_condiciones      = $c->terminos_condiciones ?? '';
        $this->impresion_automatica      = (bool) ($c->impresion_automatica ?? false);
        $this->margenes_superior         = (int) ($c->margenes_superior  ?? 10);
        $this->margenes_inferior         = (int) ($c->margenes_inferior  ?? 10);
        $this->margenes_izquierdo        = (int) ($c->margenes_izquierdo ?? 10);
        $this->margenes_derecho          = (int) ($c->margenes_derecho   ?? 10);
        $this->copias                    = (int) ($c->copias              ?? 1);
        $this->copia_cliente             = (bool) ($c->copia_cliente      ?? true);
        $this->copia_empresa             = (bool) ($c->copia_empresa      ?? false);
        $this->etiqueta_copia_cliente    = $c->etiqueta_copia_cliente ?? 'ORIGINAL - CLIENTE';
        $this->etiqueta_copia_empresa    = $c->etiqueta_copia_empresa ?? 'COPIA - EMPRESA';

        $colores = $c->colores ?? [];
        $this->color_header     = $colores['header']     ?? '#2563eb';
        $this->color_text       = $colores['text']       ?? '#1f2937';
        $this->color_background = $colores['background'] ?? '#ffffff';
    }

    public function guardarConfiguracion(): void
    {
        $this->validate();
        $this->guardando = true;

        try {
            $this->configuracion->update([
                'tamaño_papel'      => $this->tamaño_papel,
                'ancho_personalizado' => $this->ancho_personalizado,
                'alto_personalizado'  => $this->alto_personalizado,
                'orientacion'       => $this->orientacion,
                'plantilla'         => $this->plantilla,
                'colores'           => [
                    'header'     => $this->color_header,
                    'text'       => $this->color_text,
                    'background' => $this->color_background,
                ],
                'fuente'            => $this->fuente,
                'tamaño_fuente'     => $this->tamaño_fuente,
                'mostrar_logo'      => $this->mostrar_logo,
                'posicion_logo'     => $this->posicion_logo,
                'tamaño_logo'       => $this->tamaño_logo,
                'mostrar_fecha'     => $this->mostrar_fecha,
                'mostrar_hora'      => $this->mostrar_hora,
                'mostrar_direccion_empresa'      => $this->mostrar_direccion_empresa,
                'mostrar_telefono_empresa'       => $this->mostrar_telefono_empresa,
                'mostrar_email_empresa'          => $this->mostrar_email_empresa,
                'mostrar_descripcion_detallada'  => $this->mostrar_descripcion_detallada,
                'mostrar_codigo_qr'  => $this->mostrar_codigo_qr,
                'mensaje_superior'   => $this->mensaje_superior,
                'mensaje_inferior'   => $this->mensaje_inferior,
                'terminos_condiciones' => $this->terminos_condiciones,
                'impresion_automatica' => $this->impresion_automatica,
                'margenes_superior'  => $this->margenes_superior,
                'margenes_inferior'  => $this->margenes_inferior,
                'margenes_izquierdo' => $this->margenes_izquierdo,
                'margenes_derecho'   => $this->margenes_derecho,
                'copias'             => $this->copias,
                'copia_cliente'      => $this->copia_cliente,
                'copia_empresa'      => $this->copia_empresa,
                'etiqueta_copia_cliente' => $this->etiqueta_copia_cliente,
                'etiqueta_copia_empresa' => $this->etiqueta_copia_empresa,
            ]);

            $this->dispatch('toast', message: 'Configuración guardada correctamente', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Error al guardar: ' . $e->getMessage(), type: 'error');
        } finally {
            $this->guardando = false;
        }
    }

    public function restaurarDefecto(): void
    {
        $this->tamaño_papel     = '80mm';
        $this->orientacion      = 'portrait';
        $this->plantilla        = 'standard';
        $this->color_header     = '#2563eb';
        $this->color_text       = '#1f2937';
        $this->color_background = '#ffffff';
        $this->fuente           = 'Arial';
        $this->tamaño_fuente    = 12;
        $this->mostrar_logo     = true;
        $this->posicion_logo    = 'center';
        $this->tamaño_logo      = 100;
        $this->mostrar_fecha    = true;
        $this->mostrar_hora     = true;
        $this->mostrar_direccion_empresa    = true;
        $this->mostrar_telefono_empresa     = true;
        $this->mostrar_email_empresa        = true;
        $this->mostrar_descripcion_detallada = true;
        $this->mostrar_codigo_qr = false;
        $this->mensaje_superior  = '';
        $this->mensaje_inferior  = 'Gracias por su preferencia';
        $this->terminos_condiciones = '';
        $this->impresion_automatica = false;
        $this->margenes_superior  = 10;
        $this->margenes_inferior  = 10;
        $this->margenes_izquierdo = 10;
        $this->margenes_derecho   = 10;
        $this->copias                 = 1;
        $this->copia_cliente          = true;
        $this->copia_empresa          = false;
        $this->etiqueta_copia_cliente = 'ORIGINAL - CLIENTE';
        $this->etiqueta_copia_empresa = 'COPIA - EMPRESA';
        $this->dispatch('toast', message: 'Configuración restaurada a valores por defecto', type: 'info');
    }

    public function getDimensionesPapel(): array
    {
        return [
            'A4'            => ['nombre' => 'A4',           'w' => 210, 'h' => 297, 'tipo' => 'hoja'],
            '80mm'          => ['nombre' => 'Ticket 80mm',  'w' => 80,  'h' => 200, 'tipo' => 'ticket'],
            '58mm'          => ['nombre' => 'Ticket 58mm',  'w' => 58,  'h' => 200, 'tipo' => 'ticket'],
            'carta'         => ['nombre' => 'Carta',        'w' => 216, 'h' => 279, 'tipo' => 'hoja'],
            'oficio'        => ['nombre' => 'Oficio',       'w' => 216, 'h' => 330, 'tipo' => 'hoja'],
            'personalizado' => ['nombre' => 'Personalizado','w' => 0,   'h' => 0,   'tipo' => 'hoja'],
        ];
    }

    public function render()
    {
        return view('livewire.configuracion.configuracion-recibos', [
            'dimensionesPapel' => $this->getDimensionesPapel(),
            'empresa'          => Auth::user()->empresa,
        ])->layout('layouts.app');
    }
}
