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
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('clave', 100); // 'facturacion.dias_vencimiento'
            $table->text('valor'); // Valor de la configuración
            $table->enum('tipo', ['string', 'integer', 'decimal', 'boolean', 'json', 'date'])->default('string');
            $table->string('categoria', 50); // 'general', 'facturacion', 'notificaciones'
            $table->string('nombre', 200); // Nombre amigable
            $table->text('descripcion')->nullable();
            $table->text('valor_defecto')->nullable();
            $table->boolean('es_publica')->default(false); // Si se muestra en configuraciones de usuario
            $table->boolean('es_requerida')->default(false);
            $table->timestamps();
            
            $table->index(['empresa_id', 'categoria']);
            $table->unique(['empresa_id', 'clave']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};
