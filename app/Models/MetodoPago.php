<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model
{
    use HasFactory;

    protected $table = 'metodos_pago';

    protected $fillable = [
        'empresa_id',
        'codigo',
        'nombre',
        'descripcion',
        'requiere_referencia',
        'requiere_comprobante',
        'comision_porcentaje',
        'comision_fija',
        'estado',
        'orden',
    ];

    protected $casts = [
        'requiere_referencia' => 'boolean',
        'requiere_comprobante' => 'boolean',
        'comision_porcentaje' => 'decimal:2',
        'comision_fija' => 'decimal:2',
        'orden' => 'integer',
    ];

    // Relaciones
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
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

    // Métodos auxiliares
    public function estaActivo()
    {
        return $this->estado == 'activo';
    }

    public function calcularComision($monto)
    {
        $comision = 0;
        
        if ($this->comision_porcentaje > 0) {
            $comision += ($monto * $this->comision_porcentaje) / 100;
        }
        
        if ($this->comision_fija > 0) {
            $comision += $this->comision_fija;
        }
        
        return $comision;
    }
}
