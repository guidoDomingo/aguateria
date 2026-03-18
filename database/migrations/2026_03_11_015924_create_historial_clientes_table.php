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
        if (Schema::hasTable('historial_clientes')) return;
        Schema::create('historial_clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('users');
            $table->enum('tipo_evento', ['creacion', 'modificacion', 'suspension', 'activacion', 'baja', 'cambio_tarifa', 'cambio_cobrador']);
            $table->string('campo_modificado', 100)->nullable();
            $table->text('valor_anterior')->nullable();
            $table->text('valor_nuevo')->nullable();
            $table->text('motivo')->nullable();
            $table->text('observaciones')->nullable();
            $table->json('datos_completos')->nullable(); // Snapshot completo para eventos importantes
            $table->timestamps();
            
            $table->index(['cliente_id', 'created_at']);
            $table->index(['usuario_id']);
            $table->index(['tipo_evento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_clientes');
    }
};
