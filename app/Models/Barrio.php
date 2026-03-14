<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barrio extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'ciudad_id',
        'nombre',
        'descripcion',
        'referencia',
        'latitud',
        'longitud',
        'activo',
    ];

    protected $casts = [
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
        'activo' => 'boolean',
    ];

    // Relaciones
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function ciudad()
    {
        return $this->belongsTo(Ciudad::class);
    }

    public function zonas()
    {
        return $this->hasMany(Zona::class);
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorCiudad($query, $ciudadId)
    {
        return $query->where('ciudad_id', $ciudadId);
    }

    // Métodos auxiliares
    public function getClientesActivosCountAttribute()
    {
        return $this->clientes()->where('estado', 'activo')->count();
    }
}
