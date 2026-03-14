<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarifa extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'codigo',
        'nombre',
        'descripcion',
        'monto_mensual',
        'genera_mora',
        'monto_mora',
        'tipo_mora',
        'dias_vencimiento',
        'tipo_vencimiento',
        'dia_fijo_vencimiento',
        'dias_gracia',
        'costo_reconexion',
        'corte_automatico',
        'dias_corte',
        'estado',
    ];

    protected $casts = [
        'monto_mensual' => 'decimal:2',
        'genera_mora' => 'boolean',
        'monto_mora' => 'decimal:2',
        'dias_vencimiento' => 'integer',
        'dias_gracia' => 'integer',
        'costo_reconexion' => 'decimal:2',
        'corte_automatico' => 'boolean',
        'dias_corte' => 'integer',
    ];

    // Relaciones
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    public function scopeConMora($query)
    {
        return $query->where('genera_mora', true);
    }

    // Métodos auxiliares
    public function calcularMora($montoPrincipal, $diasVencido)
    {
        if (!$this->genera_mora || $diasVencido <= $this->dias_gracia) {
            return 0;
        }

        if ($this->tipo_mora == 'porcentaje') {
            return ($montoPrincipal * $this->monto_mora) / 100;
        }

        return $this->monto_mora;
    }

    public function debeGenerarCorte($diasVencido)
    {
        return $this->corte_automatico && $this->dias_corte && $diasVencido >= $this->dias_corte;
    }
}
