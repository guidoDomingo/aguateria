<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Factura;
use Carbon\Carbon;

echo "=== CORRIGIENDO FECHAS DE VENCIMIENTO DE ABRIL ===\n\n";

// Obtener facturas de abril que tienen fechas de vencimiento incorrectas
$facturasAbril = Factura::whereHas('periodo', function($query) {
    $query->where('año', 2026)->where('mes', 4);
})->get();

echo "Facturas de abril encontradas: " . $facturasAbril->count() . "\n\n";

foreach ($facturasAbril as $factura) {
    $fechaActual = $factura->fecha_vencimiento;
    
    // La fecha de vencimiento debe ser en mayo, no abril
    $nuevaFecha = null;
    $tarifa = $factura->cliente->tarifa;
    
    if ($tarifa->tipo_vencimiento === 'dia_fijo' && $tarifa->dia_fijo_vencimiento) {
        // Para día fijo: mayo + día específico
        $nuevaFecha = Carbon::parse('2026-05-' . $tarifa->dia_fijo_vencimiento);
    } else {
        // Para días corridos: mayo + días configurados
        $diasVencimiento = $tarifa->dias_vencimiento ?? 30;
        $nuevaFecha = Carbon::parse('2026-05-01')->addDays($diasVencimiento - 1);
    }
    
    echo "Factura #{$factura->numero_factura} - Cliente: {$factura->cliente->nombre_completo}\n";
    echo "  Fecha actual: {$fechaActual->format('d/m/Y')}\n";
    echo "  Nueva fecha:  {$nuevaFecha->format('d/m/Y')}\n";
    
    // Actualizar fecha
    $factura->update(['fecha_vencimiento' => $nuevaFecha]);
    echo "  ✅ Actualizada\n\n";
}

echo "=== PROCESO COMPLETADO ===\n";