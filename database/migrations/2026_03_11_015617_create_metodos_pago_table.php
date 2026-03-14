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
        Schema::create('metodos_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('codigo', 10); // EFE, TRA, QR, TAR
            $table->string('nombre', 100); // Efectivo, Transferencia, QR, Tarjeta
            $table->text('descripcion')->nullable();
            $table->boolean('requiere_referencia')->default(false);
            $table->boolean('requiere_comprobante')->default(false);
            $table->decimal('comision_porcentaje', 5, 2)->default(0);
            $table->decimal('comision_fija', 10, 2)->default(0);
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->integer('orden')->default(0);
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
        Schema::dropIfExists('metodos_pago');
    }
};
