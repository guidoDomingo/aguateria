<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'razon_social',
        'ruc',
        'direccion',
        'telefono',
        'email',
        'logo',
        'ciudad',
        'departamento',
        'pais',
        'moneda',
        'timezone',
        'locale',
        'estado',
        'fecha_inicio_trial',
        'fecha_fin_trial',
        'trial_extendido',
        'configuraciones',
    ];

    protected $casts = [
        'fecha_inicio_trial' => 'date',
        'fecha_fin_trial' => 'date',
        'trial_extendido' => 'boolean',
        'configuraciones' => 'array',
    ];

    // Relaciones principales
    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function suscripcion()
    {
        return $this->hasOne(Suscripcion::class)->latest();
    }

    public function suscripciones()
    {
        return $this->hasMany(Suscripcion::class);
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }

    public function cobradores()
    {
        return $this->hasMany(Cobrador::class);
    }

    public function tarifas()
    {
        return $this->hasMany(Tarifa::class);
    }

    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function barrios()
    {
        return $this->hasMany(Barrio::class);
    }

    public function zonas()
    {
        return $this->hasMany(Zona::class);
    }

    public function cortesServicio()
    {
        return $this->hasMany(CorteServicio::class);
    }

    public function configuracionesEmpresa()
    {
        return $this->hasMany(Configuracion::class);
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    public function scopeTrial($query)
    {
        return $query->where('estado', 'trial');
    }

    // Métodos auxiliares
    public function estaEnTrial()
    {
        return $this->estado == 'trial' && $this->fecha_fin_trial >= now();
    }

    public function planActual()
    {
        return $this->suscripcion?->plan;
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }
}
