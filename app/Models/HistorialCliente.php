<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialCliente extends Model
{
    use HasFactory;

    protected $table = 'historial_clientes';

    protected $fillable = [
        'cliente_id',
        'usuario_id',
        'tipo_evento',
        'campo_modificado',
        'valor_anterior',
        'valor_nuevo',
        'motivo',
        'observaciones',
        'datos_completos',
    ];

    protected $casts = [
        'datos_completos' => 'array',
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Scopes
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_evento', $tipo);
    }

    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($dias));
    }

    public function scopeCreaciones($query)
    {
        return $query->where('tipo_evento', 'creacion');
    }

    public function scopeModificaciones($query)
    {
        return $query->where('tipo_evento', 'modificacion');
    }

    // Métodos auxiliares
    public function esCreacion()
    {
        return $this->tipo_evento === 'creacion';
    }

    public function esModificacion()
    {
        return $this->tipo_evento === 'modificacion';
    }

    public function esSuspension()
    {
        return $this->tipo_evento === 'suspension';
    }

    public function esActivacion()
    {
        return $this->tipo_evento === 'activacion';
    }

    public function getDescripcionEventoAttribute()
    {
        return match($this->tipo_evento) {
            'creacion' => 'Cliente creado',
            'modificacion' => 'Datos modificados',
            'suspension' => 'Cliente suspendido',
            'activacion' => 'Cliente activado',
            'baja' => 'Cliente dado de baja',
            'cambio_tarifa' => 'Tarifa cambiada',
            'cambio_cobrador' => 'Cobrador cambiado',
            default => 'Evento no definido'
        };
    }

    public function getUsuarioNombreAttribute()
    {
        return $this->usuario ? $this->usuario->name : 'Sistema';
    }
}
