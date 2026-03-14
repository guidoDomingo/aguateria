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
        Schema::create('avisos_mora', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('factura_id')->nullable()->constrained('facturas')->onDelete('set null');
            $table->integer('dias_vencido');
            $table->decimal('monto_deuda', 10, 2);
            $table->integer('facturas_pendientes')->default(1);
            $table->date('fecha_aviso');
            $table->enum('tipo_aviso', ['primer_aviso', 'segundo_aviso', 'ultimo_aviso', 'aviso_corte']);
            $table->enum('estado', ['generado', 'enviado', 'entregado', 'fallido'])->default('generado');
            $table->enum('metodo_envio', ['fisico', 'email', 'whatsapp', 'sms', 'llamada']);
            $table->text('mensaje_personalizado')->nullable();
            $table->datetime('fecha_envio')->nullable();
            $table->string('respuesta_envio')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->index(['empresa_id', 'estado']);
            $table->index(['cliente_id']);
            $table->index(['fecha_aviso']);
            $table->index(['tipo_aviso']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avisos_mora');
    }
};
