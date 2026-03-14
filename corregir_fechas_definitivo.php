<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Factura;
use Carbon\Carbon;

echo "=== CORRIGIENDO FECHAS NUEVAMENTE ===\n\n";

// Obtener facturas de abril actuales (IDs 50-52)
$facturasApril = Factura::whereIn('id', [50, 51, 52])->get();

foreach ($facturasApril as $factura) {
    $tarifa = $factura->cliente->tarifa;
    
    echo "Factura #{$factura->numero_factura} - Cliente: {$factura->cliente->nombre_completo}\n";
    echo "  Fecha actual: {$factura->fecha_vencimiento->format('d/m/Y')}\n";
    
    // FORZAR fechas correctas para facturas de abril -> deben vencer en mayo
    $nuevaFecha = null;
    
    if ($tarifa->tipo_vencimiento === 'dia_fijo' && $tarifa->dia_fijo_vencimiento) {
        // Día fijo: MAYO + día específico
        $nuevaFecha = Carbon::parse('2026-05-' . $tarifa->dia_fijo_vencimiento);
        echo "  Tarifa: {$tarifa->nombre} - Día fijo: {$tarifa->dia_fijo_vencimiento}\n";
    } else {
        // Días corridos: MAYO + días configurados
        $diasVencimiento = $tarifa->dias_vencimiento ?? 30;
        if ($diasVencimiento == 30) {
            $nuevaFecha = Carbon::parse('2026-05-30'); // 30 de mayo
        } else {
            $nuevaFecha = Carbon::parse('2026-05-01')->addDays($diasVencimiento - 1);
        }
        echo "  Tarifa: {$tarifa->nombre} - Días corridos: {$diasVencimiento}\n";
    }
    
    echo "  Nueva fecha: {$nuevaFecha->format('d/m/Y')}\n";
    
    // Actualizar
    $factura->update(['fecha_vencimiento' => $nuevaFecha]);
    echo "  ✅ Corregida\n\n";
}

// También corregir las de marzo que tienen fechas raras
echo "--- VERIFICANDO FACTURAS DE MARZO ---\n";
$facturasMarzo = Factura::whereIn('id', [47, 48, 49])->get();
foreach ($facturasMarzo as $factura) {
    echo "#{$factura->numero_factura} - {$factura->cliente->nombre_completo} - {$factura->fecha_vencimiento->format('d/m/Y')}\n";
    
    // Si las fechas de marzo no están correctas, corregir
    $tarifa = $factura->cliente->tarifa;
    $fechaCorrectaMarzo = null;
    
    if ($tarifa->tipo_vencimiento === 'dia_fijo' && $tarifa->dia_fijo_vencimiento) {
        $fechaCorrectaMarzo = Carbon::parse('2026-04-' . $tarifa->dia_fijo_vencimiento);
    } else {
        $fechaCorrectaMarzo = Carbon::parse('2026-04-30');
    }
    
    if (!$factura->fecha_vencimiento->isSameDay($fechaCorrectaMarzo)) {
        echo "  Corrigiendo de {$factura->fecha_vencimiento->format('d/m/Y')} a {$fechaCorrectaMarzo->format('d/m/Y')}\n";
        $factura->update(['fecha_vencimiento' => $fechaCorrectaMarzo]);
    }
}

echo "\n--- RESULTADO FINAL ---\n";
$todasFacturas = Factura::with(['cliente', 'periodo'])->orderBy('id')->get();
foreach ($todasFacturas as $factura) {
    $periodo = $factura->periodo ? $factura->periodo->nombre : 'Sin período';
    echo "#{$factura->numero_factura} - {$periodo} - Vence: {$factura->fecha_vencimiento->format('d/m/Y')} - {$factura->estado}\n";
}

echo "\n=== PROCESO COMPLETADO ===\n";