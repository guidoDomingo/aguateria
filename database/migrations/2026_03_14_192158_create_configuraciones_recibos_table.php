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
        Schema::create('configuraciones_recibos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            
            // Configuración de diseño
            $table->enum('template', ['compacto', 'standard', 'detallado'])->default('compacto');
            $table->enum('tamaño_papel', ['A4', 'Carta', 'Ticket', 'Medio_Oficio'])->default('Ticket');
            $table->enum('orientacion', ['portrait', 'landscape'])->default('portrait');
            
            // Configuración de contenido
            $table->boolean('mostrar_logo')->default(true);
            $table->boolean('mostrar_cedula_cliente')->default(true);
            $table->boolean('mostrar_direccion_cliente')->default(true);
            $table->boolean('mostrar_telefono_empresa')->default(true);
            $table->boolean('mostrar_detalle_facturas')->default(false);
            $table->boolean('mostrar_firma')->default(true);
            
            // Configuración de texto
            $table->string('titulo_personalizado', 100)->nullable();
            $table->text('mensaje_agradecimiento')->nullable();
            $table->text('pie_pagina')->nullable();
            
            // Configuración de colores (formato hex)
            $table->string('color_principal', 7)->default('#333333');
            $table->string('color_secundario', 7)->default('#666666');
            
            // Configuración de fuente
            $table->enum('fuente', ['Arial', 'Times', 'Helvetica', 'Courier'])->default('Arial');
            $table->integer('tamaño_fuente')->default(12);
            
            $table->timestamps();
            
            // Índices
            $table->unique('empresa_id'); // Una configuración por empresa
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuraciones_recibos');
    }
};
