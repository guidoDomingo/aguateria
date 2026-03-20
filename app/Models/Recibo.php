<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recibo extends Model
{
    use HasFactory;

    protected $fillable = [
        'pago_id',
        'numero_recibo',
        'cliente_nombre',
        'cliente_cedula',
        'cliente_direccion',
        'monto_pagado',
        'fecha_pago',
        'periodo_pagado',
        'metodo_pago',
        'referencia',
        'observaciones',
        'datos_empresa',
        'datos_descuento',
    ];

    protected $casts = [
        'monto_pagado'    => 'decimal:2',
        'fecha_pago'      => 'date',
        'datos_empresa'   => 'array',
        'datos_descuento' => 'array',
    ];

    // Relaciones
    public function pago()
    {
        return $this->belongsTo(Pago::class);
    }

    // Métodos auxiliares
    public function getEmpresaNombreAttribute()
    {
        return $this->datos_empresa['nombre'] ?? 'N/A';
    }

    public function getEmpresaDireccionAttribute()
    {
        return $this->datos_empresa['direccion'] ?? 'N/A';
    }

    public function getEmpresaTelefonoAttribute()
    {
        return $this->datos_empresa['telefono'] ?? 'N/A';
    }

    public function getEmpresaLogoAttribute()
    {
        return $this->datos_empresa['logo'] ?? null;
    }

    public function getMontoFormateadoAttribute()
    {
        return number_format($this->monto_pagado, 0, ',', '.') . ' Gs.';
    }

    /**
     * Generar URL para descargar el recibo en PDF
     */
    public function getPdfUrlAttribute()
    {
        return route('recibos.pdf', $this->id);
    }

    /**
     * Generar datos para impresión
     */
    public function getDatosImpresionAttribute()
    {
        return [
            'numero_recibo' => $this->numero_recibo,
            'fecha' => $this->fecha_pago->format('d/m/Y'),
            'cliente' => [
                'nombre' => $this->cliente_nombre,
                'cedula' => $this->cliente_cedula,
                'direccion' => $this->cliente_direccion,
            ],
            'pago' => [
                'monto' => $this->monto_pagado,
                'monto_formateado' => $this->monto_formateado,
                'metodo' => $this->metodo_pago,
                'referencia' => $this->referencia,
                'periodo' => $this->periodo_pagado,
            ],
            'empresa' => $this->datos_empresa,
            'observaciones' => $this->observaciones,
        ];
    }
}
