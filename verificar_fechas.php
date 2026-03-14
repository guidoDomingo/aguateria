<?php

// Script para verificar fechas de vencimiento y estados de facturas
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Factura;
use App\Models\Cliente;
use App\Models\Tarifa;
use Carbon\Carbon;

echo "=== VERIFICANDO FECHAS DE VENCIMIENTO Y ESTADOS ===\n\n";

// Primero verificar configuración de tarifas
echo "--- CONFIGURACION DE TARIFAS ---\n";
foreach (Tarifa::all() as $tarifa) {
    echo "Tarifa: {$tarifa->nombre}\n";
    echo "  Tipo vencimiento: {$tarifa->tipo_vencimiento}\n";
    echo "  Día fijo: {$tarifa->dia_fijo_vencimiento}\n";
    echo "  Días corridos: {$tarifa->dias_vencimiento}\n\n";
}

// Verificar facturas recientes
echo "--- FACTURAS RECIENTES ---\n";
$facturas = Factura::with('cliente', 'cliente.tarifa')
    ->orderBy('id', 'desc')
    ->limit(10)
    ->get();

foreach ($facturas as $factura) {
    $periodo = $factura->periodo ? $factura->periodo->nombre : 'N/A';
    echo "Factura #{$factura->numero_factura} - Cliente: {$factura->cliente->nombre_completo}\n";
    echo "  Período: {$periodo}\n";
    echo "  Estado: {$factura->estado}\n";
    echo "  Total: Gs. " . number_format($factura->total, 0, ',', '.') . "\n";
    echo "  Emisión: {$factura->fecha_emision->format('d/m/Y')}\n";
    echo "  Vencimiento: {$factura->fecha_vencimiento->format('d/m/Y')}\n";
    
    if ($factura->cliente && $factura->cliente->tarifa) {
        echo "  Tarifa: {$factura->cliente->tarifa->nombre} - Tipo: {$factura->cliente->tarifa->tipo_vencimiento} - Día: {$factura->cliente->tarifa->dia_fijo_vencimiento}\n";
    }
    
    if ($factura->observaciones) {
        echo "  Observaciones: {$factura->observaciones}\n";
    }
    echo "\n";
}

echo "=== PROCESO COMPLETADO ===\n";