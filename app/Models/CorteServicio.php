<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorteServicio extends Model
{
    use HasFactory;

    protected $table = 'cortes_servicio';

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'usuario_id',
        'numero_orden',
        'motivo',
        'detalle_motivo',
        'fecha_programada',
        'fecha_corte',
        'hora_corte',
        'deuda_total',
        'facturas_pendientes',
        'estado',
        'observaciones',
        'datos_cliente',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_corte' => 'date',
        'hora_corte' => 'datetime',
        'deuda_total' => 'decimal:2',
        'facturas_pendientes' => 'integer',
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

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function reconexiones()
    {
        return $this->hasMany(Reconexion::class, 'corte_id');
    }

    // Scopes
    public function scopeProgramados($query)
    {
        return $query->where('estado', 'programado');
    }

    public function scopeEjecutados($query)
    {
        return $query->where('estado', 'ejecutado');
    }

    public function scopeDelDia($query, $fecha = null)
    {
        $fecha = $fecha ?: now()->format('Y-m-d');
        return $query->where('fecha_programada', $fecha);
    }

    // Métodos auxiliares
    public function ejecutar($observaciones = null)
    {
        $this->estado = 'ejecutado';
        $this->fecha_corte = now()->format('Y-m-d');
        $this->hora_corte = now();
        if ($observaciones) {
            $this->observaciones = $observaciones;
        }
        $this->save();

        // Cambiar estado del cliente
        $this->cliente->update(['estado' => 'cortado']);
    }

    public function cancelar($motivo = null)
    {
        $this->estado = 'cancelado';
        $this->observaciones = $motivo;
        $this->save();
    }

    public function tieneReconexion()
    {
        return $this->reconexiones()->where('estado', 'ejecutado')->exists();
    }
}
