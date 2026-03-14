<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\PeriodoFacturacion;
use App\Models\Factura;
use Carbon\Carbon;

echo "=== ANALIZANDO PERÍODOS Y FACTURAS EXISTENTES ===\n\n";

echo "--- PERÍODOS DISPONIBLES ---\n";
$periodos = PeriodoFacturacion::where('empresa_id', 1)
    ->where('año', 2026)
    ->orderBy('mes')
    ->get();

foreach ($periodos as $periodo) {
    $cantFacturas = Factura::where('periodo_id', $periodo->id)->count();
    echo "- {$periodo->nombre} (ID: {$periodo->id}) - {$cantFacturas} facturas\n";
}

echo "\n--- FACTURAS POR PERÍODO ---\n";
$facturas = Factura::with(['periodo', 'cliente'])
    ->whereHas('periodo', function($query) {
        $query->where('año', 2026);
    })
    ->orderBy('created_at', 'desc')
    ->get();

$facturasPorPeriodo = [];
foreach ($facturas as $factura) {
    $periodoNombre = $factura->periodo ? $factura->periodo->nombre : 'Sin período';
    if (!isset($facturasPorPeriodo[$periodoNombre])) {
        $facturasPorPeriodo[$periodoNombre] = [];
    }
    $facturasPorPeriodo[$periodoNombre][] = $factura;
}

foreach ($facturasPorPeriodo as $periodoNombre => $facturasDelPeriodo) {
    echo "\n{$periodoNombre}:\n";
    foreach ($facturasDelPeriodo as $factura) {
        echo "  #{$factura->numero_factura} - {$factura->cliente->nombre_completo} - Vence: {$factura->fecha_vencimiento->format('d/m/Y')} - {$factura->estado}\n";
    }
}

echo "\n--- RECOMENDACIÓN ---\n";
echo "Si tienes facturas de MARZO consolidadas y quieres generar ABRIL:\n";
echo "1. Usa 'Próximo Mes' en lugar de 'Mes Actual'\n";
echo "2. O selecciona específicamente el período de Abril 2026\n";
echo "3. Las facturas de Abril deben vencer en MAYO 2026\n";

echo "\n=== FIN DEL ANÁLISIS ===\n";