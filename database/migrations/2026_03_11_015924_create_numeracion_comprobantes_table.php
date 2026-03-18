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
        if (Schema::hasTable('numeracion_comprobantes')) return;
        Schema::create('numeracion_comprobantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->enum('tipo_comprobante', ['factura', 'recibo', 'nota_credito', 'nota_debito']);
            $table->string('serie', 10)->default('001');
            $table->string('prefijo', 10)->nullable(); // FAC, REC, NC, ND
            $table->integer('numero_actual')->default(0);
            $table->integer('numero_desde')->default(1);
            $table->integer('numero_hasta')->default(999999);
            $table->integer('incremento')->default(1);
            $table->boolean('activo')->default(true);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->index(['empresa_id', 'tipo_comprobante', 'activo']);
            $table->unique(['empresa_id', 'tipo_comprobante', 'serie']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('numeracion_comprobantes');
    }
};
