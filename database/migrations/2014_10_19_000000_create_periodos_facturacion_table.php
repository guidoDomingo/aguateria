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
        Schema::create('periodos_facturacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->year('año');
            $table->tinyInteger('mes'); // 1-12
            $table->string('nombre', 50); // "Enero 2024"
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->date('fecha_vencimiento');
            $table->date('fecha_facturacion')->nullable(); // Cuándo se generaron las facturas
            $table->enum('estado', ['abierto', 'cerrado', 'facturado', 'anulado'])->default('abierto');
            $table->integer('total_facturas')->default(0);
            $table->decimal('monto_total', 12, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->index(['empresa_id', 'estado']);
            $table->index(['año', 'mes']);
            $table->unique(['empresa_id', 'año', 'mes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodos_facturacion');
    }
};
