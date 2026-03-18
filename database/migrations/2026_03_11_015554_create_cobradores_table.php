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
        if (Schema::hasTable('cobradores')) return;
        Schema::create('cobradores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('codigo', 20); // COB001, COB002
            $table->string('nombre');
            $table->string('apellido')->nullable();
            $table->string('cedula', 20)->unique();
            $table->string('telefono', 15);
            $table->string('email', 100)->nullable();
            $table->text('direccion')->nullable();
            $table->foreignId('zona_id')->nullable()->constrained('zonas')->onDelete('set null');
            $table->decimal('comision_porcentaje', 5, 2)->default(0); // % de comisión
            $table->decimal('comision_fija', 10, 2)->default(0); // Monto fijo por cobro
            $table->date('fecha_ingreso');
            $table->date('fecha_salida')->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'suspendido'])->default('activo');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->index(['empresa_id', 'estado']);
            $table->index(['zona_id']);
            $table->unique(['empresa_id', 'codigo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobradores');
    }
};
