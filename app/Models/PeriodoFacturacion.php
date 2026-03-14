<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodoFacturacion extends Model
{
    use HasFactory;

    protected $table = 'periodos_facturacion';

    protected $fillable = [
        'empresa_id',
        'año',
        'mes',
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'fecha_vencimiento',
        'fecha_facturacion',
        'estado',
        'total_facturas',
        'monto_total',
        'observaciones',
    ];

    protected $casts = [
        'año' => 'integer',
        'mes' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_facturacion' => 'date',
        'total_facturas' => 'integer',
        'monto_total' => 'decimal:2',
    ];

    // Relaciones
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function facturas()
    {
        return $this->hasMany(Factura::class, 'periodo_id');
    }

    // Scopes
    public function scopeAbiertos($query)
    {
        return $query->where('estado', 'abierto');
    }

    public function scopeFacturados($query)
    {
        return $query->where('estado', 'facturado');
    }

    public function scopeDelAño($query, $año)
    {
        return $query->where('año', $año);
    }

    // Métodos auxiliares
    public function estaAbierto()
    {
        return $this->estado == 'abierto';
    }

    public function estaFacturado()
    {
        return $this->estado == 'facturado';
    }

    public function puedeFacturar()
    {
        return in_array($this->estado, ['abierto', 'cerrado']);
    }

    public function cerrarPeriodo()
    {
        $this->estado = 'cerrado';
        $this->save();
    }

    public function reabrirPeriodo()
    {
        $this->estado = 'abierto';
        $this->save();
    }

    public function getPromedioFacturaAttribute()
    {
        return $this->total_facturas > 0 ? $this->monto_total / $this->total_facturas : 0;
    }
}
