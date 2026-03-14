<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suscripcion extends Model
{
    use HasFactory;

    protected $table = 'suscripciones';

    protected $fillable = [
        'empresa_id',
        'plan_id',
        'fecha_inicio',
        'fecha_fin',
        'precio_acordado',
        'descuento',
        'tipo_pago',
        'estado',
        'metodo_pago',
        'referencia_pago',
        'proximo_pago',
        'auto_renovar',
        'observaciones',
        'fecha_cancelacion',
        'motivo_cancelacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'precio_acordado' => 'decimal:2',
        'descuento' => 'decimal:2',
        'proximo_pago' => 'date',
        'auto_renovar' => 'boolean',
        'fecha_cancelacion' => 'datetime',
    ];

    // Relaciones
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    public function scopeVencidas($query)
    {
        return $query->where('estado', 'vencida');
    }

    public function scopeProximasAVencer($query, $dias = 7)
    {
        return $query->where('fecha_fin', '<=', now()->addDays($dias))
                    ->where('estado', 'activa');
    }

    // Métodos auxiliares
    public function estaActiva()
    {
        return $this->estado == 'activa' && $this->fecha_fin >= now();
    }

    public function estaVencida()
    {
        return $this->fecha_fin < now() || $this->estado == 'vencida';
    }

    public function diasParaVencer()
    {
        return now()->diffInDays($this->fecha_fin, false);
    }

    public function renovar($meses = 1)
    {
        $this->fecha_inicio = $this->fecha_fin;
        $this->fecha_fin = $this->fecha_fin->addMonths($meses);
        $this->proximo_pago = now()->addMonth();
        $this->estado = 'activa';
        $this->save();
    }

    public function cancelar($motivo = null)
    {
        $this->estado = 'cancelada';
        $this->fecha_cancelacion = now();
        $this->motivo_cancelacion = $motivo;
        $this->save();
    }
}
