<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->decimal('mora_exonerada', 12, 2)->default(0)->after('monto_pagado');
            $table->decimal('descuento', 12, 2)->default(0)->after('mora_exonerada');
            $table->decimal('porcentaje_descuento', 5, 2)->default(0)->after('descuento');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn(['mora_exonerada', 'descuento', 'porcentaje_descuento']);
        });
    }
};
