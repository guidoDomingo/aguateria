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
        Schema::table('configuracion_recibos', function (Blueprint $table) {
            $table->unsignedTinyInteger('copias')->default(1)->after('impresion_automatica');
            $table->boolean('copia_cliente')->default(true)->after('copias');
            $table->boolean('copia_empresa')->default(false)->after('copia_cliente');
            $table->string('etiqueta_copia_cliente', 50)->default('ORIGINAL - CLIENTE')->after('copia_empresa');
            $table->string('etiqueta_copia_empresa', 50)->default('COPIA - EMPRESA')->after('etiqueta_copia_cliente');
        });
    }

    public function down(): void
    {
        Schema::table('configuracion_recibos', function (Blueprint $table) {
            $table->dropColumn(['copias', 'copia_cliente', 'copia_empresa', 'etiqueta_copia_cliente', 'etiqueta_copia_empresa']);
        });
    }
};
