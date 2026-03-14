<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasTenant
{
    /**
     * Boot del trait
     */
    protected static function bootHasTenant()
    {
        // Aplicar filtro global para todas las consultas
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Auth::check() && Auth::user()->empresa_id) {
                $builder->where('empresa_id', Auth::user()->empresa_id);
            }
        });

        // Asignar automáticamente empresa_id al crear
        static::creating(function ($model) {
            if (Auth::check() && Auth::user()->empresa_id && !$model->empresa_id) {
                $model->empresa_id = Auth::user()->empresa_id;
            }
        });
    }

    /**
     * Scope para filtrar por tenant específico
     */
    public function scopeForTenant(Builder $query, $tenantId)
    {
        return $query->where('empresa_id', $tenantId);
    }

    /**
     * Scope para obtener datos sin filtro de tenant (solo para super admins)
     */
    public function scopeWithoutTenantScope(Builder $query)
    {
        return $query->withoutGlobalScope('tenant');
    }

    /**
     * Relación con empresa
     */
    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class);
    }
}