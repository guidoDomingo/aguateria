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
        if (Schema::hasTable('metas_cobranza')) return;
        Schema::create('metas_cobranza', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('cobrador_id')->constrained('cobradores');
            $table->year('año');
            $table->tinyInteger('mes'); // 1-12
            $table->decimal('meta_monto', 12, 2); // Meta en dinero
            $table->integer('meta_clientes')->default(0); // Meta en cantidad de clientes
            $table->decimal('monto_cobrado', 12, 2)->default(0); // Lo que realmente cobró
            $table->integer('clientes_cobrados')->default(0); // Clientes que pagó
            $table->decimal('porcentaje_cumplimiento', 5, 2)->default(0); // % de cumplimiento
            $table->decimal('comision_calculada', 10, 2)->default(0);
            $table->boolean('meta_alcanzada')->default(false);
            $table->decimal('bono_adicional', 10, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->index(['empresa_id', 'año', 'mes']);
            $table->index(['cobrador_id']);
            $table->unique(['empresa_id', 'cobrador_id', 'año', 'mes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metas_cobranza');
    }
};
