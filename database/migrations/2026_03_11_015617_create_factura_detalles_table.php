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
        if (Schema::hasTable('factura_detalles')) return;
        Schema::create('factura_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->onDelete('cascade');
            $table->string('concepto', 200); // "Servicio de agua - Enero 2024"
            $table->text('descripcion')->nullable();
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->enum('tipo', ['servicio', 'mora', 'reconexion', 'descuento', 'impuesto', 'otros'])->default('servicio');
            $table->timestamps();
            
            $table->index(['factura_id']);
            $table->index(['tipo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factura_detalles');
    }
};
