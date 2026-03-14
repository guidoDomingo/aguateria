<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tarifa;
use App\Models\Factura;

echo "\n=== CONFIGURACION ACTUAL DE TARIFAS ===\n";
foreach (Tarifa::all() as $tarifa) {
    echo sprintf("%s - Tipo: %s - Día fijo: %s - Días corridos: %d\n",
        $tarifa->nombre,
        $tarifa->tipo_vencimiento ?? 'null',
        $tarifa->dia_fijo_vencimiento ?? 'null',
        $tarifa->dias_vencimiento
    );
}

echo "\n=== FACTURAS EXISTENTES ===\n";
foreach (Factura::with(['cliente.tarifa'])->get() as $factura) {
    echo sprintf("Factura %s - Cliente: %s - Tarifa: %s - Vencimiento: %s\n",
        $factura->numero_factura,
        $factura->cliente->nombre,
        $factura->cliente->tarifa->nombre,
        $factura->fecha_vencimiento->format('d/m/Y')
    );
}