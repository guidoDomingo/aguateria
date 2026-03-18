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
        if (Schema::hasTable('empresas')) return;
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique(); // Código único para la empresa
            $table->string('nombre', 200);
            $table->string('razon_social', 250)->nullable();
            $table->string('ruc', 20)->unique()->nullable();
            $table->string('direccion');
            $table->string('telefono', 15)->nullable();
            $table->string('email', 100)->unique();
            $table->string('logo', 255)->nullable(); // Ruta del logo
            $table->string('ciudad', 100)->default('Asunción');
            $table->string('departamento', 100)->default('Central');
            $table->string('pais', 50)->default('Paraguay');
            $table->char('moneda', 3)->default('PYG'); // Guaraníes paraguayos
            $table->string('timezone', 50)->default('America/Asuncion');
            $table->string('locale', 5)->default('es');
            $table->enum('estado', ['activa', 'suspendida', 'inactiva', 'trial'])->default('trial');
            $table->date('fecha_inicio_trial')->nullable();
            $table->date('fecha_fin_trial')->nullable();
            $table->boolean('trial_extendido')->default(false);
            $table->json('configuraciones')->nullable(); // Configuraciones específicas
            $table->timestamps();
            
            $table->index(['estado']);
            $table->index(['codigo']);
            $table->index(['email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
