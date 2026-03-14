<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reconexiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('corte_id')->constrained('cortes_servicio');
            $table->foreignId('usuario_id')->constrained('users'); // Quien autorizó la reconexión
            $table->string('numero_orden', 50)->unique();
            $table->date('fecha_programada');
            $table->date('fecha_reconexion')->nullable(); // Cuándo se ejecutó realmente
            $table->time('hora_reconexion')->nullable();
            $table->decimal('costo_reconexion', 10, 2)->default(0);
            $table->decimal('monto_pagado_reconexion', 10, 2)->default(0);
            $table->boolean('requiere_pago_previo')->default(true);
            $table->enum('estado', ['programado', 'ejecutado', 'cancelado'])->default('programado');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->index(['empresa_id', 'estado']);
            $table->index(['cliente_id']);
            $table->index(['corte_id']);
            $table->index(['fecha_programada']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconexiones');
    }
};
