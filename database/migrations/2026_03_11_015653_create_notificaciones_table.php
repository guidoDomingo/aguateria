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
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('tipo', ['pago_vencido', 'corte_programado', 'reconexion', 'bienvenida', 'recordatorio', 'promocion', 'sistema']);
            $table->string('titulo', 200);
            $table->text('mensaje');
            $table->enum('canal', ['email', 'sms', 'whatsapp', 'sistema', 'push'])->default('sistema');
            $table->string('destinatario'); // Email, teléfono, etc.
            $table->enum('prioridad', ['baja', 'normal', 'alta', 'urgente'])->default('normal');
            $table->datetime('fecha_programada')->nullable();
            $table->datetime('fecha_enviado')->nullable();
            $table->enum('estado', ['pendiente', 'enviado', 'fallido', 'cancelado'])->default('pendiente');
            $table->integer('intentos')->default(0);
            $table->text('respuesta_servidor')->nullable(); // Respuesta del API
            $table->json('datos_adicionales')->nullable(); // Parámetros extra
            $table->timestamps();
            
            $table->index(['empresa_id', 'estado']);
            $table->index(['cliente_id']);
            $table->index(['tipo', 'canal']);
            $table->index(['fecha_programada']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
