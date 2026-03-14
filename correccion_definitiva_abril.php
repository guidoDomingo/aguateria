<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Factura;
use Carbon\Carbon;

echo "=== CORRECCIÓN DEFINITIVA DE FECHAS ABRIL → MAYO ===\n\n";

// Obtener facturas de abril con fechas incorrectas
$facturasAbril = Factura::whereHas('periodo', function($query) {
    $query->where('año', 2026)->where('mes', 4); // Abril 2026
})->get();

echo "Facturas de April 2026 encontradas: {$facturasAbril->count()}\n\n";

foreach ($facturasAbril as $factura) {
    $tarifa = $factura->cliente->tarifa;
    
    echo "Factura #{$factura->numero_factura} - {$factura->cliente->nombre_completo}\n";
    echo "  Fecha ACTUAL: {$factura->fecha_vencimiento->format('d/m/Y')}\n";
    echo "  Tarifa: {$tarifa->nombre} - {$tarifa->tipo_vencimiento}\n";
    
    // Calcular fecha CORRECTA para facturas de abril (deben vencer en MAYO)
    $nuevaFecha = null;
    
    if ($tarifa->tipo_vencimiento === 'dia_fijo' && $tarifa->dia_fijo_vencimiento) {
        // Día fijo: 10 de mayo
        $nuevaFecha = Carbon::parse('2026-05-' . $tarifa->dia_fijo_vencimiento);
        echo "  Día fijo: {$tarifa->dia_fijo_vencimiento} → 2026-05-{$tarifa->dia_fijo_vencimiento}\n";
    } else {
        // Días corridos: 30 días desde inicio de mayo
        $diasVencimiento = $tarifa->dias_vencimiento ?? 30;
        $nuevaFecha = Carbon::parse('2026-05-01')->addDays($diasVencimiento - 1);
        echo "  Días corridos: {$diasVencimiento} → {$nuevaFecha->format('d/m/Y')}\n";
    }
    
    echo "  Fecha CORREGIDA: {$nuevaFecha->format('d/m/Y')}\n";
    
    // Usar update con array para asegurar guardado
    Factura::where('id', $factura->id)->update([
        'fecha_vencimiento' => $nuevaFecha->format('Y-m-d')
    ]);
    
    echo "  ✅ GUARDADO EN BD\n\n";
}

// Verificación inmediata
echo "=== VERIFICACIÓN INMEDIATA ===\n";
$verificacion = Factura::whereHas('periodo', function($query) {
    $query->where('año', 2026)->where('mes', 4);
})->get();

foreach ($verificacion as $factura) {
    echo "#{$factura->numero_factura} - {$factura->cliente->nombre_completo} - Vence: {$factura->fecha_vencimiento->format('d/m/Y')}\n";
}

echo "\n=== RESULTADO ESPERADO ===\n";
echo "Todas las facturas de April 2026 ahora deben vencer en Mayo 2026:\n";
echo "- guido: 10/05/2026\n";
echo "- dsfds: 30/05/2026\n";  
echo "- sadfsf: 30/05/2026\n";

echo "\n=== PROCESO COMPLETADO ===\n";