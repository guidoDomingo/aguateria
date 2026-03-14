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
        Schema::create('cortes_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('usuario_id')->constrained('users'); // Quien autorizó el corte
            $table->string('numero_orden', 50)->unique();
            $table->enum('motivo', ['mora', 'solicitud_cliente', 'mantenimiento', 'falta_pago', 'otros'])->default('mora');
            $table->text('detalle_motivo')->nullable();
            $table->date('fecha_programada');
            $table->date('fecha_corte')->nullable(); // Cuándo se ejecutó realmente
            $table->time('hora_corte')->nullable();
            $table->decimal('deuda_total', 10, 2)->nullable(); // Monto adeudado al momento del corte
            $table->integer('facturas_pendientes')->default(0);
            $table->enum('estado', ['programado', 'ejecutado', 'cancelado'])->default('programado');
            $table->text('observaciones')->nullable();
            $table->json('datos_cliente')->nullable(); // Snapshot del cliente
            $table->timestamps();
            
            $table->index(['empresa_id', 'estado']);
            $table->index(['cliente_id']);
            $table->index(['fecha_programada']);
            $table->index(['numero_orden']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cortes_servicio');
    }
};
