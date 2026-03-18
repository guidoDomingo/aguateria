<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionRecibo extends Model
{
    use HasFactory;

    protected $table = 'configuracion_recibos';

    protected $fillable = [
        'empresa_id',
        'tamaño_papel',
        'ancho_personalizado',
        'alto_personalizado',
        'orientacion',
        'plantilla',
        'colores',
        'fuente',
        'tamaño_fuente',
        'mostrar_logo',
        'posicion_logo',
        'tamaño_logo',
        'mostrar_fecha',
        'mostrar_hora',
        'mostrar_direccion_empresa',
        'mostrar_telefono_empresa',
        'mostrar_email_empresa',
        'mostrar_descripcion_detallada',
        'mostrar_codigo_qr',
        'mensaje_superior',
        'mensaje_inferior',
        'terminos_condiciones',
        'impresion_automatica',
        'margenes_superior',
        'margenes_inferior',
        'margenes_izquierdo',
        'margenes_derecho'
    ];

    protected $casts = [
        'mostrar_logo' => 'boolean',
        'mostrar_fecha' => 'boolean',
        'mostrar_hora' => 'boolean',
        'mostrar_direccion_empresa' => 'boolean',
        'mostrar_telefono_empresa' => 'boolean',
        'mostrar_email_empresa' => 'boolean',
        'mostrar_descripcion_detallada' => 'boolean',
        'mostrar_codigo_qr' => 'boolean',
        'impresion_automatica' => 'boolean',
        'colores' => 'array',
        'tamaño_fuente' => 'integer',
        'tamaño_logo' => 'integer',
        'ancho_personalizado' => 'integer',
        'alto_personalizado' => 'integer',
        'margenes_superior' => 'integer',
        'margenes_inferior' => 'integer',
        'margenes_izquierdo' => 'integer',
        'margenes_derecho' => 'integer',
    ];

    // Relaciones
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    // Métodos de utilidad
    public static function getConfiguracionParaEmpresa($empresaId)
    {
        return static::firstOrCreate(
            ['empresa_id' => $empresaId],
            [
                'tamaño_papel' => '80mm',
                'orientacion' => 'portrait',
                'plantilla' => 'standard',
                'fuente' => 'Arial',
                'tamaño_fuente' => 12,
                'mostrar_logo' => true,
                'posicion_logo' => 'center',
                'tamaño_logo' => 100,
                'mostrar_fecha' => true,
                'mostrar_hora' => true,
                'mostrar_direccion_empresa' => true,
                'mostrar_telefono_empresa' => true,
                'mostrar_email_empresa' => true,
                'mostrar_descripcion_detallada' => true,
                'mostrar_codigo_qr' => false,
                'mensaje_inferior' => 'Gracias por su preferencia',
                'impresion_automatica' => false,
                'margenes_superior' => 10,
                'margenes_inferior' => 10,
                'margenes_izquierdo' => 10,
                'margenes_derecho' => 10,
            ]
        );
    }

    public function getColoresAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true);
        }
        return $value ?: [
            'header' => '#2563eb',
            'text' => '#1f2937',
            'background' => '#ffffff'
        ];
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

    public static function getConfiguracionEmpresa($empresaId)
    {
        return static::where('empresa_id', $empresaId)->first() ?? static::getConfiguracionDefecto();
    }

    public static function getConfiguracionDefecto()
    {
        return new static([
            'template' => 'compacto',
            'tamaño_papel' => 'Ticket',
            'orientacion' => 'portrait',
            'mostrar_logo' => true,
            'mostrar_cedula_cliente' => true,
            'mostrar_direccion_cliente' => true,
            'mostrar_telefono_empresa' => true,
            'mostrar_detalle_facturas' => false,
            'mostrar_firma' => true,
            'titulo_personalizado' => null,
            'mensaje_agradecimiento' => 'Gracias por su pago',
            'pie_pagina' => null,
            'color_principal' => '#333333',
            'color_secundario' => '#666666',
            'fuente' => 'Arial',
            'tamaño_fuente' => 12,
        ]);
    }

    public function getTemplatesDisponibles()
    {
        return [
            'compacto' => 'Compacto (350px)',
            'standard' => 'Estándar (500px)',
            'detallado' => 'Detallado (600px)'
        ];
    }

    public function getTamañosPapelDisponibles()
    {
        return [
            'Ticket' => 'Ticket (80mm)',
            'A4' => 'A4 (210x297mm)',
            'Carta' => 'Carta (216x279mm)',
            'Medio_Oficio' => 'Medio Oficio (216x140mm)'
        ];
    }

    public function getFuentesDisponibles()
    {
        return [
            'Arial' => 'Arial',
            'Times' => 'Times New Roman',
            'Helvetica' => 'Helvetica',
            'Courier' => 'Courier New'
        ];
    }
}
