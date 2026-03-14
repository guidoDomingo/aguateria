<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Factura;

echo "=== FECHAS REALES VS INTERFAZ ===\n\n";

// Verificar facturas de abril específicamente
$facturasAbril = Factura::whereHas('periodo', function($query) {
    $query->where('año', 2026)->where('mes', 4);  // Abril 2026
})->orderBy('id')->get();

echo "FACTURAS DE ABRIL EN BASE DE DATOS:\n";
foreach ($facturasAbril as $factura) {
    echo "#{$factura->numero_factura} - {$factura->cliente->nombre_completo}\n";
    echo "  BD: {$factura->fecha_vencimiento->format('d/m/Y')} - Estado: {$factura->estado}\n";
    echo "  Total: Gs. " . number_format($factura->total, 0, ',', '.') . "\n";
    echo "---\n";
}

// Verificar si hay cache de Livewire
echo "\nLimpiando cache adicional...\n";
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPCache limpiado\n";
}

echo "\n=== FIN DE VERIFICACION ===\n";