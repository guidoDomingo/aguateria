<?php

// Script para regenerar facturas con las correcciones aplicadas
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Factura;
use App\Models\PeriodoFacturacion;
use App\Services\FacturacionService;
use Carbon\Carbon;

echo "=== REGENERANDO FACTURAS CON CORRECCIONES APLICADAS ===\n\n";

try {
    // Restablecer fecha actual
    Carbon::setTestNow(Carbon::parse('2026-03-14'));
    echo "Fecha actual: " . Carbon::now()->format('d/m/Y') . "\n\n";
    
    $facturaService = app(FacturacionService::class);
    
    // Eliminar facturas existentes para empezar limpio
    echo "Eliminando facturas existentes...\n";
    \DB::statement('SET FOREIGN_KEY_CHECKS = 0');
    \DB::table('factura_detalles')->delete();
    \DB::table('facturas')->delete();
    \DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    
    // Crear facturas de marzo (base)
    echo "\n--- CREANDO FACTURAS BASE (MARZO 2026) ---\n";
    $resultado = $facturaService->generarFacturasMensuales(2026, 3, 1);
    echo "Facturas marzo: {$resultado['facturas_generadas']} generadas\n";
    if (is_array($resultado['errores']) && count($resultado['errores']) > 0) {
        echo "Errores: " . count($resultado['errores']) . "\n";
    } else if (is_numeric($resultado['errores']) && $resultado['errores'] > 0) {
        echo "Errores: {$resultado['errores']}\n";
    }
    
    // Simular paso del tiempo a mayo
    Carbon::setTestNow(Carbon::parse('2026-05-15'));
    echo "\n--- SIMULANDO FECHA: " . Carbon::now()->format('d/m/Y') . " ---\n";
    
    // Crear período de mayo
    $periodoMayo = PeriodoFacturacion::firstOrCreate([
        'empresa_id' => 1,
        'año' => 2026,
        'mes' => 5
    ], [
        'nombre' => 'May 2026',
        'fecha_inicio' => Carbon::parse('2026-05-01'),
        'fecha_fin' => Carbon::parse('2026-05-31'),
        'estado' => 'activo'
    ]);
    
    echo "Período mayo creado: {$periodoMayo->nombre}\n";
    
    // Generar facturas de mayo con acumulación
    echo "\n--- GENERANDO FACTURAS CON ACUMULACIÓN (MAYO 2026) ---\n";
    $resultado = $facturaService->generarFacturasMensuales(2026, 5, 1);
    echo "Facturas mayo: {$resultado['facturas_generadas']} generadas\n";
    if (is_array($resultado['errores']) && count($resultado['errores']) > 0) {
        echo "Errores: " . count($resultado['errores']) . "\n";
        foreach ($resultado['errores'] as $error) {
            echo "  - $error\n";
        }
    }
    
    // Mostrar resultado final
    echo "\n--- FACTURAS FINALES ---\n";
    $facturas = Factura::with('cliente', 'periodo')->orderBy('id')->get();
    
    foreach ($facturas as $factura) {
        $periodo = $factura->periodo ? $factura->periodo->nombre : 'Sin período';
        $obs = $factura->observaciones ? ' - ' . $factura->observaciones : '';
        echo "#{$factura->numero_factura} - {$factura->cliente->nombre_completo} - {$periodo}\n";
        echo "  Estado: {$factura->estado} | Total: Gs. " . number_format($factura->total, 0, ',', '.') . "\n";
        echo "  Vencimiento: {$factura->fecha_vencimiento->format('d/m/Y')}{$obs}\n\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "=== PROCESO COMPLETADO ===\n";