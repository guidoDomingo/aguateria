<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'codigo_cliente',
        'nombre',
        'apellido',
        'razon_social',
        'tipo_persona',
        'cedula',
        'ruc',
        'telefono',
        'telefono_alternativo',
        'email',
        'direccion',
        'numero_casa',
        'referencia',
        'barrio_id',
        'zona_id',
        'tarifa_id',
        'cobrador_id',
        'tipo_cliente',
        'fecha_alta',
        'fecha_baja',
        'motivo_baja',
        'estado',
        'exento_mora',
        'descuento_especial',
        'dia_vencimiento_personalizado',
        'observaciones',
        'datos_adicionales',
    ];

    protected $casts = [
        'fecha_alta' => 'date',
        'fecha_baja' => 'date',
        'exento_mora' => 'boolean',
        'descuento_especial' => 'decimal:2',
        'dia_vencimiento_personalizado' => 'integer',
        'datos_adicionales' => 'array',
    ];

    // Relaciones
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function barrio()
    {
        return $this->belongsTo(Barrio::class);
    }

    public function zona()
    {
        return $this->belongsTo(Zona::class);
    }

    public function tarifa()
    {
        return $this->belongsTo(Tarifa::class);
    }

    public function cobrador()
    {
        return $this->belongsTo(Cobrador::class);
    }

    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function cortesServicio()
    {
        return $this->hasMany(CorteServicio::class);
    }

    public function reconexiones()
    {
        return $this->hasMany(Reconexion::class);
    }

    public function historial()
    {
        return $this->hasMany(HistorialCliente::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeMorosos($query)
    {
        return $query->whereHas('facturas', function ($q) {
            $q->where('estado', 'vencido');
        });
    }

    public function scopePorBarrio($query, $barrioId)
    {
        return $query->where('barrio_id', $barrioId);
    }

    public function scopePorCobrador($query, $cobradorId)
    {
        return $query->where('cobrador_id', $cobradorId);
    }

    // Métodos auxiliares
    public function getNombreCompletoAttribute()
    {
        return $this->tipo_persona == 'juridica' 
            ? $this->razon_social 
            : trim($this->nombre . ' ' . $this->apellido);
    }

    public function getUltimaFacturaAttribute()
    {
        return $this->facturas()->latest()->first();
    }

    public function getDeudaTotalAttribute()
    {
        return $this->facturas()->whereIn('estado', ['pendiente', 'vencido', 'parcial'])->sum('saldo_pendiente');
    }

    public function estaMoroso()
    {
        return $this->facturas()->where('estado', 'vencido')->exists();
    }

    public function puedeGenerarMora()
    {
        return !$this->exento_mora && $this->tarifa->genera_mora;
    }
}
