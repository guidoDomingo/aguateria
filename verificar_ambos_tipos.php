<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Cliente;
use Carbon\Carbon;

echo "=== PROBANDO AMBOS TIPOS DE VENCIMIENTO ===\n\n";

$clientes = Cliente::with('tarifa')->get();

$fechaEmision = Carbon::parse('2026-04-15'); // 15 de abril

foreach ($clientes as $cliente) {
    echo "Cliente: {$cliente->nombre_completo}\n";
    echo "Tarifa: {$cliente->tarifa->nombre} - Tipo: {$cliente->tarifa->tipo_vencimiento}\n";
    
    $tarifa = $cliente->tarifa;
    
    if ($tarifa->tipo_vencimiento === 'dia_fijo' && $tarifa->dia_fijo_vencimiento) {
        echo "Día fijo: {$tarifa->dia_fijo_vencimiento}\n";
        $fechaVencimiento = $fechaEmision->copy()->addMonth()->day($tarifa->dia_fijo_vencimiento);
    } else {
        echo "Días corridos: {$tarifa->dias_vencimiento}\n";
        $diasVencimiento = $tarifa->dias_vencimiento ?? 30;
        $fechaVencimiento = $fechaEmision->copy()->addDays($diasVencimiento);
    }
    
    echo "Emisión: {$fechaEmision->format('d/m/Y')} → Vencimiento: {$fechaVencimiento->format('d/m/Y')}\n";
    echo "---\n\n";
}

echo "=== PROCESO COMPLETADO ===\n";