<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'empresa_id',
        'name',
        'apellido',
        'email',
        'password',
        'telefono',
        'cedula',
        'direccion',
        'tipo_usuario',
        'estado',
        'last_login_at',
        'avatar',
        'preferencias',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'preferencias' => 'array',
        'password' => 'hashed',
    ];

    // Relaciones
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cobrador()
    {
        return $this->hasOne(Cobrador::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function cortesServicio()
    {
        return $this->hasMany(CorteServicio::class, 'usuario_id');
    }

    public function reconexiones()
    {
        return $this->hasMany(Reconexion::class, 'usuario_id');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopePorEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_usuario', $tipo);
    }

    // Métodos auxiliares
    public function esAdmin()
    {
        return in_array($this->tipo_usuario, ['super_admin', 'admin_empresa']);
    }

    public function esSuperAdmin()
    {
        return $this->tipo_usuario == 'super_admin';
    }

    public function esCobrador()
    {
        return $this->tipo_usuario == 'cobrador';
    }

    // Alias para compatibilidad
    public function isAdmin()
    {
        return $this->esAdmin();
    }

    public function isSuperAdmin()
    {
        return $this->esSuperAdmin();
    }

    public function getNombreCompletoAttribute()
    {
        return trim($this->name . ' ' . $this->apellido);
    }

    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }
}
