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
        if (Schema::hasTable('suscripciones')) return;
        Schema::create('suscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('planes');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->decimal('precio_acordado', 10, 2); // Precio que se acordó (puede diferir del plan)
            $table->decimal('descuento', 10, 2)->default(0);
            $table->enum('tipo_pago', ['mensual', 'anual', 'unico'])->default('mensual');
            $table->enum('estado', ['activa', 'vencida', 'suspendida', 'cancelada'])->default('activa');
            $table->string('metodo_pago', 50)->nullable(); // efectivo, transferencia, etc.
            $table->string('referencia_pago', 100)->nullable();
            $table->date('proximo_pago')->nullable();
            $table->boolean('auto_renovar')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_cancelacion')->nullable();
            $table->string('motivo_cancelacion')->nullable();
            $table->timestamps();
            
            $table->index(['empresa_id', 'estado']);
            $table->index(['fecha_fin']);
            $table->index(['proximo_pago']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suscripciones');
    }
};
