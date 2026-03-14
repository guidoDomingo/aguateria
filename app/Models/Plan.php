<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'planes';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_mensual',
        'max_clientes',
        'max_usuarios',
        'max_cobradores',
        'caracteristicas',
        'facturacion_automatica',
        'reportes_avanzados',
        'api_acceso',
        'soporte_prioritario',
        'estado',
        'orden',
    ];

    protected $casts = [
        'precio_mensual' => 'decimal:2',
        'max_clientes' => 'integer',
        'max_usuarios' => 'integer',
        'max_cobradores' => 'integer',
        'caracteristicas' => 'array',
        'facturacion_automatica' => 'boolean',
        'reportes_avanzados' => 'boolean',
        'api_acceso' => 'boolean',
        'soporte_prioritario' => 'boolean',
        'orden' => 'integer',
    ];

    // Relaciones
    public function suscripciones()
    {
        return $this->hasMany(Suscripcion::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden', 'asc');
    }
}
