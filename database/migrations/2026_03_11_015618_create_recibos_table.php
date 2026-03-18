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
        if (Schema::hasTable('recibos')) return;
        Schema::create('recibos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->constrained('pagos')->onDelete('cascade');
            $table->string('numero_recibo', 50);
            $table->string('cliente_nombre');
            $table->string('cliente_cedula')->nullable();
            $table->text('cliente_direccion');
            $table->decimal('monto_pagado', 10, 2);
            $table->date('fecha_pago');
            $table->string('periodo_pagado', 50); // "Enero 2024"
            $table->string('metodo_pago', 50);
            $table->string('referencia')->nullable();
            $table->text('observaciones')->nullable();
            $table->json('datos_empresa')->nullable(); // Logo, dirección, etc.
            $table->timestamps();
            
            $table->index(['pago_id']);
            $table->index(['numero_recibo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recibos');
    }
};
