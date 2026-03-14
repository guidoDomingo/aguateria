<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobrador extends Model
{
 use HasFactory;

 protected $table = 'cobradores';

 protected $fillable = [
 'empresa_id',
 'user_id',
 'codigo',
 'nombre',
 'apellido',
 'cedula',
 'telefono',
 'email',
 'direccion',
 'zona_id',
 'comision_porcentaje',
 'comision_fija',
 'fecha_ingreso',
 'fecha_salida',
 'estado',
 'observaciones',
 ];

 protected $casts = [
 'comision_porcentaje' => 'decimal:2',
 'comision_fija' => 'decimal:2',
 'fecha_ingreso' => 'date',
 'fecha_salida' => 'date',
 ];

 // Relaciones
 public function empresa()
 {
 return $this->belongsTo(Empresa::class);
 }

 public function usuario()
 {
 return $this->belongsTo(User::class, 'user_id');
 }

 public function zona()
 {
 return $this->belongsTo(Zona::class);
 }

 public function clientes()
 {
 return $this->hasMany(Cliente::class);
 }

 public function pagos()
 {
 return $this->hasMany(Pago::class);
 }

 public function metas()
 {
 return $this->hasMany(MetaCobranza::class);
 }

 // Scopes
 public function scopeActivos($query)
 {
 return $query->where('estado', 'activo');
 }

 public function scopePorZona($query, $zonaId)
 {
 return $query->where('zona_id', $zonaId);
 }

 // Métodos auxiliares
 public function getNombreCompletoAttribute()
 {
 return trim($this->nombre . ' ' . $this->apellido);
 }

 public function calcularComision($montoCobrado)
 {
 $comision = 0;
 
 if ($this->comision_porcentaje > 0) {
 $comision += ($montoCobrado * $this->comision_porcentaje) / 100;
 }
 
 if ($this->comision_fija > 0) {
 $comision += $this->comision_fija;
 }
 
 return $comision;
 }

 public function getClientesActivosAttribute()
 {
 return $this->clientes()->where('estado', 'activo')->count();
 }

 public function getTotalCobradoMesAttribute()
 {
 return $this->pagos()
 ->whereMonth('fecha_pago', now()->month)
 ->whereYear('fecha_pago', now()->year)
 ->sum('monto_pagado');
 }
}
