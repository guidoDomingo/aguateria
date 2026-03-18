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
        if (Schema::hasTable('pagos')) return;
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('factura_id')->nullable()->constrained('facturas')->onDelete('set null');
            $table->foreignId('cobrador_id')->nullable()->constrained('cobradores')->onDelete('set null');
            $table->foreignId('metodo_pago_id')->constrained('metodos_pago');
            $table->foreignId('user_id')->constrained('users'); // Quien registró el pago
            $table->string('numero_recibo', 50)->unique();
            $table->decimal('monto_pagado', 10, 2);
            $table->decimal('vuelto', 10, 2)->default(0);
            $table->string('referencia', 100)->nullable(); // Número de transferencia, etc.
            $table->text('comprobante')->nullable(); // Ruta del archivo
            $table->date('fecha_pago');
            $table->timestamp('hora_pago')->useCurrent();
            $table->enum('estado', ['confirmado', 'pendiente', 'anulado'])->default('confirmado');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->index(['empresa_id', 'fecha_pago']);
            $table->index(['cliente_id']);
            $table->index(['factura_id']);
            $table->index(['cobrador_id']);
            $table->index(['numero_recibo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
