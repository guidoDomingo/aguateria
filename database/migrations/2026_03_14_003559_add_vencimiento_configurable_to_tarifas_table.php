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
        Schema::table('tarifas', function (Blueprint $table) {
            $table->enum('tipo_vencimiento', ['dias_corridos', 'dia_fijo'])->default('dias_corridos')->after('dias_vencimiento');
            $table->tinyInteger('dia_fijo_vencimiento')->nullable()->after('tipo_vencimiento')->comment('Día del mes para vencimiento fijo (1-31)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarifas', function (Blueprint $table) {
            $table->dropColumn(['tipo_vencimiento', 'dia_fijo_vencimiento']);
        });
    }
};
