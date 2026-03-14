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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->onDelete('cascade');
            $table->string('name');
            $table->string('apellido')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('telefono', 15)->nullable();
            $table->string('cedula', 20)->nullable();
            $table->string('direccion')->nullable();
            $table->enum('tipo_usuario', ['super_admin', 'admin_empresa', 'cajero', 'cobrador', 'supervisor'])->default('cajero');
            $table->enum('estado', ['activo', 'inactivo', 'suspendido'])->default('activo');
            $table->timestamp('last_login_at')->nullable();
            $table->string('avatar')->nullable();
            $table->json('preferencias')->nullable(); // Configuraciones de usuario
            $table->rememberToken();
            $table->timestamps();
            
            $table->index(['empresa_id', 'estado']);
            $table->index(['tipo_usuario']);
            $table->index(['cedula']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
