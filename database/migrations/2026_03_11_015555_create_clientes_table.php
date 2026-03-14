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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('codigo_cliente', 20); // Código único por empresa
            $table->string('nombre');
            $table->string('apellido')->nullable();
            $table->string('razon_social')->nullable(); // Para empresas
            $table->enum('tipo_persona', ['fisica', 'juridica'])->default('fisica');
            $table->string('cedula', 20)->nullable();
            $table->string('ruc', 20)->nullable();
            $table->string('telefono', 15)->nullable();
            $table->string('telefono_alternativo', 15)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('direccion');
            $table->string('numero_casa', 10)->nullable();
            $table->text('referencia')->nullable(); // Referencia de ubicación
            $table->foreignId('barrio_id')->constrained('barrios');
            $table->foreignId('zona_id')->nullable()->constrained('zonas')->onDelete('set null');
            $table->foreignId('tarifa_id')->constrained('tarifas');
            $table->foreignId('cobrador_id')->nullable()->constrained('cobradores')->onDelete('set null');
            $table->enum('tipo_cliente', ['residencial', 'comercial', 'industrial', 'especial'])->default('residencial');
            $table->date('fecha_alta');
            $table->date('fecha_baja')->nullable();
            $table->string('motivo_baja')->nullable();
            $table->enum('estado', ['activo', 'suspendido', 'retirado', 'cortado'])->default('activo');
            $table->boolean('exento_mora')->default(false); // Algunos clientes no generan mora
            $table->decimal('descuento_especial', 5, 2)->default(0); // % de descuento
            $table->integer('dia_vencimiento_personalizado')->nullable(); // Día específico de vencimiento
            $table->text('observaciones')->nullable();
            $table->json('datos_adicionales')->nullable(); // Campos personalizables
            $table->timestamps();
            
            $table->index(['empresa_id', 'estado']);
            $table->index(['barrio_id']);
            $table->index(['zona_id']);
            $table->index(['tarifa_id']);
            $table->index(['cobrador_id']);
            $table->index(['codigo_cliente']);
            $table->index(['cedula']);
            $table->index(['ruc']);
            $table->unique(['empresa_id', 'codigo_cliente']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
