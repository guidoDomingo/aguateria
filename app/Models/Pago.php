<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'factura_id',
        'cobrador_id',
        'metodo_pago_id',
        'user_id',
        'numero_recibo',
        'monto_pagado',
        'vuelto',
        'referencia',
        'comprobante',
        'fecha_pago',
        'hora_pago',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'monto_pagado' => 'decimal:2',
        'vuelto' => 'decimal:2',
        'fecha_pago' => 'date',
        'hora_pago' => 'datetime',
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

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    public function cobrador()
    {
        return $this->belongsTo(Cobrador::class);
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function recibo()
    {
        return $this->hasOne(Recibo::class, 'numero_recibo', 'numero_recibo')
            ->latest('id');
    }

    // Scopes
    public function scopeConfirmados($query)
    {
        return $query->where('estado', 'confirmado');
    }

    public function scopeDelDia($query, $fecha = null)
    {
        $fecha = $fecha ?: now()->format('Y-m-d');
        return $query->where('fecha_pago', $fecha);
    }

    public function scopeDelMes($query, $año, $mes)
    {
        return $query->whereYear('fecha_pago', $año)
                    ->whereMonth('fecha_pago', $mes);
    }

    public function scopePorCobrador($query, $cobradorId)
    {
        return $query->where('cobrador_id', $cobradorId);
    }

    // Métodos auxiliares
    public function estaConfirmado()
    {
        return $this->estado == 'confirmado';
    }

    public function anular($motivo = null)
    {
        $this->estado = 'anulado';
        $this->observaciones = $motivo;
        $this->save();

        // Revertir el pago en la factura si existe
        if ($this->factura) {
            $this->factura->saldo_pendiente += $this->monto_pagado;
            
            if ($this->factura->saldo_pendiente >= $this->factura->total) {
                $this->factura->estado = 'pendiente';
                $this->factura->fecha_pago = null;
            } elseif ($this->factura->saldo_pendiente > 0) {
                $this->factura->estado = 'parcial';
            }
            
            $this->factura->save();
        }
    }

    public function getComprobanteUrlAttribute()
    {
        return $this->comprobante ? asset('storage/' . $this->comprobante) : null;
    }
}
