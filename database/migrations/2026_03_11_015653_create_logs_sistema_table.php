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
        Schema::create('logs_sistema', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('nivel', ['debug', 'info', 'warning', 'error', 'critical'])->default('info');
            $table->string('accion', 100); // 'crear_cliente', 'generar_factura'
            $table->string('modulo', 50); // 'clientes', 'facturas', 'pagos'
            $table->text('descripcion');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('metodo_http', 10)->nullable(); // GET, POST, PUT, DELETE
            $table->json('datos_anteriores')->nullable(); // Estado anterior
            $table->json('datos_nuevos')->nullable(); // Estado nuevo
            $table->json('datos_request')->nullable(); // Datos de la petición
            $table->string('modelo_tipo', 100)->nullable(); // App\Models\Cliente
            $table->unsignedBigInteger('modelo_id')->nullable(); // ID del modelo
            $table->timestamps();
            
            $table->index(['empresa_id', 'created_at']);
            $table->index(['user_id']);
            $table->index(['accion', 'modulo']);
            $table->index(['nivel']);
            $table->index(['modelo_tipo', 'modelo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs_sistema');
    }
};
