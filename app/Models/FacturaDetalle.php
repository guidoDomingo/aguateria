<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaDetalle extends Model
{
    use HasFactory;

    protected $table = 'factura_detalles';

    protected $fillable = [
        'factura_id',
        'concepto',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'tipo',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Relaciones
    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    // Scopes
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeServicios($query)
    {
        return $query->where('tipo', 'servicio');
    }

    public function scopeMoras($query)
    {
        return $query->where('tipo', 'mora');
    }

    // Métodos auxiliares
    public function esMora()
    {
        return $this->tipo == 'mora';
    }

    public function esServicio()
    {
        return $this->tipo == 'servicio';
    }

    public function esDescuento()
    {
        return $this->tipo == 'descuento';
    }
}
