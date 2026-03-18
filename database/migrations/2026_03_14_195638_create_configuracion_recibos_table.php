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
        Schema::create('configuracion_recibos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            
            // Configuración de tamaño y formato
            $table->enum('tamaño_papel', ['A4', '80mm', '58mm', 'carta', 'oficio', 'personalizado'])->default('80mm');
            $table->integer('ancho_personalizado')->nullable(); // en mm
            $table->integer('alto_personalizado')->nullable(); // en mm
            $table->enum('orientacion', ['portrait', 'landscape'])->default('portrait');
            
            // Configuración de diseño
            $table->string('plantilla')->default('standard'); // standard, modern, classic, minimal
            $table->json('colores')->nullable(); // {header: '#color', text: '#color', background: '#color'}
            $table->string('fuente')->default('Arial'); // Arial, Times, Courier
            $table->integer('tamaño_fuente')->default(12);
            
            // Logo y encabezado
            $table->boolean('mostrar_logo')->default(true);
            $table->string('posicion_logo')->default('center'); // left, center, right
            $table->integer('tamaño_logo')->default(100); // altura en px
            
            // Información a mostrar
            $table->boolean('mostrar_fecha')->default(true);
            $table->boolean('mostrar_hora')->default(true);
            $table->boolean('mostrar_direccion_empresa')->default(true);
            $table->boolean('mostrar_telefono_empresa')->default(true);
            $table->boolean('mostrar_email_empresa')->default(true);
            $table->boolean('mostrar_descripcion_detallada')->default(true);
            $table->boolean('mostrar_codigo_qr')->default(false);
            
            // Pie de página personalizable
            $table->text('mensaje_superior')->nullable();
            $table->text('mensaje_inferior')->nullable();
            $table->text('terminos_condiciones')->nullable();
            
            // Configuración de impresión
            $table->boolean('impresion_automatica')->default(false);
            $table->integer('margenes_superior')->default(10); // en mm
            $table->integer('margenes_inferior')->default(10);
            $table->integer('margenes_izquierdo')->default(10);
            $table->integer('margenes_derecho')->default(10);
            
            $table->timestamps();
            
            // Constraint: una configuración por empresa
            $table->unique('empresa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_recibos');
    }
};
