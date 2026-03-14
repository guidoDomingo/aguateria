<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ciudad extends Model
{
    use HasFactory;

    protected $table = 'ciudades';

    protected $fillable = [
        'nombre',
        'departamento',
        'pais',
        'codigo_postal',
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
    public function barrios()
    {
        return $this->hasMany(Barrio::class);
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorDepartamento($query, $departamento)
    {
        return $query->where('departamento', $departamento);
    }

    // Métodos auxiliares
    public function getNombreCompletoAttribute()
    {
        return $this->nombre . ', ' . $this->departamento;
    }
}
