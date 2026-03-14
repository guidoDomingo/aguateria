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
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('periodo_id')->constrained('periodos_facturacion');
            $table->string('numero_factura', 50)->unique();
            $table->string('serie', 10)->default('001');
            $table->integer('numero')->unsigned();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('mora', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('impuesto', 10, 2)->default(0); // IVA u otros
            $table->decimal('total', 10, 2);
            $table->decimal('saldo_pendiente', 10, 2); // Monto que falta pagar
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->date('fecha_pago')->nullable(); // Cuándo se pagó completamente
            $table->enum('estado', ['pendiente', 'pagado', 'vencido', 'parcial', 'anulado'])->default('pendiente');
            $table->enum('tipo_factura', ['mensual', 'reconexion', 'otros'])->default('mensual');
            $table->text('observaciones')->nullable();
            $table->json('datos_cliente')->nullable(); // Snapshot de datos del cliente al facturar
            $table->timestamps();
            
            $table->index(['empresa_id', 'estado']);
            $table->index(['cliente_id', 'estado']);
            $table->index(['periodo_id']);
            $table->index(['fecha_vencimiento']);
            $table->index(['numero_factura']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
