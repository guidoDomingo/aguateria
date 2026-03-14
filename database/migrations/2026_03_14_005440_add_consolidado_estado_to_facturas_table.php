<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            // Modificar el enum para incluir 'consolidado'
            DB::statement("ALTER TABLE facturas MODIFY COLUMN estado ENUM('pendiente', 'pagado', 'vencido', 'parcial', 'anulado', 'consolidado') DEFAULT 'pendiente'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            // Revertir el enum al estado original
            DB::statement("ALTER TABLE facturas MODIFY COLUMN estado ENUM('pendiente', 'pagado', 'vencido', 'parcial', 'anulado') DEFAULT 'pendiente'");
        });
    }
};
