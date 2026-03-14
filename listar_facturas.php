<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Factura;

echo "TODAS LAS FACTURAS EN LA BASE DE DATOS:\n";
$facturas = Factura::with('periodo')->orderBy('id')->get();
foreach ($facturas as $factura) {
    $periodo = $factura->periodo ? $factura->periodo->nombre : 'Sin período';
    echo "ID: {$factura->id} - #{$factura->numero_factura} - {$periodo} - Estado: {$factura->estado} - Vencimiento: {$factura->fecha_vencimiento->format('d/m/Y')}\n";
}