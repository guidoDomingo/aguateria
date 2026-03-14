<?php

// Script para verificar el estado de consolidación de facturas
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Factura;
use App\Models\Cliente;

echo "=== VERIFICANDO ESTADO DE CONSOLIDACION DE FACTURAS ===\n\n";

// Obtener todas las facturas ordenadas por cliente
$facturas = Factura::with('cliente')
    ->orderBy('cliente_id')
    ->orderBy('created_at')
    ->get();

$clienteActual = null;
foreach ($facturas as $factura) {
    if ($clienteActual !== $factura->cliente_id) {
        $clienteActual = $factura->cliente_id;
        echo "\n--- Cliente: " . $factura->cliente->nombre_completo . " ---\n";
    }
    
    echo "  Factura #{$factura->numero_factura} - Estado: {$factura->estado} - Total: Gs. " . number_format($factura->total, 0, ',', '.') . "\n";
    echo "    Saldo pendiente: Gs. " . number_format($factura->saldo_pendiente, 0, ',', '.') . "\n";
    echo "    Creada: {$factura->created_at->format('d/m/Y H:i')}\n";
    
    if ($factura->observaciones) {
        echo "    Observaciones: {$factura->observaciones}\n";
    }
    
    echo "\n";
}

echo "=== PROCESO COMPLETADO ===\n";