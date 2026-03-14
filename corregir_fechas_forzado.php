<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Factura;
use Carbon\Carbon;

echo "=== CORRIGIENDO FECHAS EN BASE DE DATOS ===\n\n";

// Obtener facturas de abril que necesitan corrección
$facturasAbril = Factura::whereHas('periodo', function($query) {
    $query->where('año', 2026)->where('mes', 4);
})->get();

foreach ($facturasAbril as $factura) {
    $fechaActual = $factura->fecha_vencimiento;
    $tarifa = $factura->cliente->tarifa;
    
    echo "Factura #{$factura->numero_factura} - {$factura->cliente->nombre_completo}\n";
    echo "  Actual: {$fechaActual->format('d/m/Y')}\n";
    
    // Calcular fecha correcta: facturas de abril deben vencer en mayo
    $nuevaFecha = null;
    
    if ($tarifa->tipo_vencimiento === 'dia_fijo' && $tarifa->dia_fijo_vencimiento) {
        $nuevaFecha = Carbon::parse('2026-05-' . $tarifa->dia_fijo_vencimiento);
    } else {
        $nuevaFecha = Carbon::parse('2026-05-30'); // 30 de mayo para días corridos de 30
    }
    
    echo "  Corrigiendo a: {$nuevaFecha->format('d/m/Y')}\n";
    
    // Actualizar en la base de datos
    $factura->fecha_vencimiento = $nuevaFecha;
    $factura->save();
    
    echo "  ✅ GUARDADO\n\n";
}

echo "=== VERIFICACION FINAL ===\n";
$verificacion = Factura::whereHas('periodo', function($query) {
    $query->where('año', 2026)->where('mes', 4);
})->get();

foreach ($verificacion as $factura) {
    echo "#{$factura->numero_factura} - Vence: {$factura->fecha_vencimiento->format('d/m/Y')}\n";
}

echo "\n=== PROCESO COMPLETADO ===\n";