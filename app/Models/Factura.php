<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'periodo_id',
        'numero_factura',
        'serie',
        'numero',
        'subtotal',
        'mora',
        'descuento',
        'impuesto',
        'total',
        'saldo_pendiente',
        'fecha_emision',
        'fecha_vencimiento',
        'fecha_pago',
        'estado',
        'tipo_factura',
        'observaciones',
        'datos_cliente',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'mora' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_pago' => 'date',
        'datos_cliente' => 'array',
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

    public function periodo()
    {
        return $this->belongsTo(PeriodoFacturacion::class, 'periodo_id');
    }

    // Alias para compatibilidad
    public function periodoFacturacion()
    {
        return $this->periodo();
    }

    public function detalles()
    {
        return $this->hasMany(FacturaDetalle::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeVencidas($query)
    {
        return $query->where('estado', 'vencido');
    }

    public function scopePagadas($query)
    {
        return $query->where('estado', 'pagado');
    }

    public function scopeDelMes($query, $año, $mes)
    {
        return $query->whereHas('periodo', function ($q) use ($año, $mes) {
            $q->where('año', $año)->where('mes', $mes);
        });
    }

    // Métodos auxiliares
    public function estaVencida()
    {
        return $this->fecha_vencimiento < now() && !$this->estaPagada();
    }

    public function estaPagada()
    {
        return $this->estado == 'pagado';
    }

    public function diasVencido()
    {
        if (!$this->estaVencida()) {
            return 0;
        }
        return now()->diffInDays($this->fecha_vencimiento);
    }

    public function aplicarPago($monto)
    {
        $this->saldo_pendiente = max(0, $this->saldo_pendiente - $monto);
        
        if ($this->saldo_pendiente == 0) {
            $this->estado = 'pagado';
            $this->fecha_pago = now();
        } elseif ($this->saldo_pendiente < $this->total) {
            $this->estado = 'parcial';
        }
        
        $this->save();
    }

    public function calcularMora()
    {
        if (!$this->estaVencida() || !$this->cliente->puedeGenerarMora()) {
            return 0;
        }

        return $this->cliente->tarifa->calcularMora($this->subtotal, $this->diasVencido());
    }
}
