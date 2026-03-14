<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Factura;
use Carbon\Carbon;

echo "=== CORRIGIENDO FECHAS MAL GENERADAS ===\n\n";

// Obtener las facturas con fechas incorrectas (las más recientes)
$facturasIncorrectas = Factura::whereIn('id', [44, 45, 46])->get();

foreach ($facturasIncorrectas as $factura) {
    $tarifa = $factura->cliente->tarifa;
    
    echo "Corrigiendo Factura #{$factura->numero_factura} - Cliente: {$factura->cliente->nombre_completo}\n";
    echo "  Actual: {$factura->fecha_vencimiento->format('d/m/Y')}\n";
    
    // Recalcular fecha correcta - FACTURAS DE ABRIL DEBEN VENCER EN MAYO
    $nuevaFecha = null;
    
    if ($tarifa->tipo_vencimiento === 'dia_fijo' && $tarifa->dia_fijo_vencimiento) {
        // Día fijo: MAYO 2026 + día específico
        $nuevaFecha = Carbon::parse('2026-05-' . $tarifa->dia_fijo_vencimiento);
    } else {
        // Días corridos: MAYO 2026 + días desde inicio del mes
        $diasVencimiento = $tarifa->dias_vencimiento ?? 30;
        $nuevaFecha = Carbon::parse('2026-05-01')->addDays($diasVencimiento - 1);
    }
    
    echo "  Corregida: {$nuevaFecha->format('d/m/Y')}\n";
    
    // Actualizar
    $factura->update(['fecha_vencimiento' => $nuevaFecha]);
    echo "  ✅ Actualizada\n\n";
}

echo "=== VERIFICANDO TODAS LAS FACTURAS ===\n";
$todasFacturas = Factura::with(['cliente', 'periodo'])->orderBy('id')->get();
foreach ($todasFacturas as $factura) {
    $periodo = $factura->periodo ? $factura->periodo->nombre : 'Sin período';
    echo "#{$factura->numero_factura} - {$factura->cliente->nombre_completo} - {$periodo} - {$factura->fecha_vencimiento->format('d/m/Y')} - {$factura->estado}\n";
}

echo "\n=== PROCESO COMPLETADO ===\n";