<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reconexion extends Model
{
    use HasFactory;

    protected $table = 'reconexiones';

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'corte_id',
        'usuario_id',
        'numero_orden',
        'fecha_programada',
        'fecha_reconexion',
        'hora_reconexion',
        'costo_reconexion',
        'monto_pagado_reconexion',
        'requiere_pago_previo',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_reconexion' => 'date',
        'costo_reconexion' => 'decimal:2',
        'monto_pagado_reconexion' => 'decimal:2',
        'requiere_pago_previo' => 'boolean',
    ];

    // Relaciones
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function corte()
    {
        return $this->belongsTo(CorteServicio::class, 'corte_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
