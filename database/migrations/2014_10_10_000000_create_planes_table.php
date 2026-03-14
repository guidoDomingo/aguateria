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
        Schema::create('planes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100); // Basic, Pro, Premium
            $table->string('descripcion')->nullable();
            $table->decimal('precio_mensual', 10, 2);
            $table->integer('max_clientes')->default(100);
            $table->integer('max_usuarios')->default(5);
            $table->integer('max_cobradores')->default(3);
            $table->json('caracteristicas')->nullable(); // Features específicas
            $table->boolean('facturacion_automatica')->default(true);
            $table->boolean('reportes_avanzados')->default(false);
            $table->boolean('api_acceso')->default(false);
            $table->boolean('soporte_prioritario')->default(false);
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->integer('orden')->default(0); // Para mostrar orden en frontend
            $table->timestamps();
            
            $table->index(['estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes');
    }
};
