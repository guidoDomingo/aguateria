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
        Schema::create('tarifas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('codigo', 20); // RES001, COM001
            $table->string('nombre', 100); // Residencial, Comercial, Industrial
            $table->text('descripcion')->nullable();
            $table->decimal('monto_mensual', 10, 2);
            $table->boolean('genera_mora')->default(true);
            $table->decimal('monto_mora', 10, 2)->default(0);
            $table->enum('tipo_mora', ['fijo', 'porcentaje'])->default('fijo');
            $table->integer('dias_vencimiento')->default(30);
            $table->integer('dias_gracia')->default(5); // Días antes de aplicar mora
            $table->decimal('costo_reconexion', 10, 2)->default(0);
            $table->boolean('corte_automatico')->default(false);
            $table->integer('dias_corte')->nullable(); // Días después del vencimiento
            $table->enum('estado', ['activa', 'inactiva'])->default('activa');
            $table->timestamps();
            
            $table->index(['empresa_id', 'estado']);
            $table->unique(['empresa_id', 'codigo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifas');
    }
};
