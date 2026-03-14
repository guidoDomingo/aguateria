<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Services\FacturacionService;
use App\Models\Cliente;
use Carbon\Carbon;

echo "=== TESTING CALCULO DE FECHAS DIRECTO ===\n\n";

$facturaService = app(FacturacionService::class);

$cliente = Cliente::first();
echo "Cliente de prueba: {$cliente->nombre_completo}\n";
echo "Tarifa: {$cliente->tarifa->nombre}\n";
echo "Tipo: {$cliente->tarifa->tipo_vencimiento}\n";

if ($cliente->tarifa->tipo_vencimiento === 'dia_fijo') {
    echo "Día fijo: {$cliente->tarifa->dia_fijo_vencimiento}\n";
} else {
    echo "Días corridos: {$cliente->tarifa->dias_vencimiento}\n";
}

// Test con diferentes fechas de emisión
$fechasTest = [
    Carbon::parse('2026-04-15'), // Abril
    Carbon::parse('2026-05-20'), // Mayo  
    Carbon::parse('2026-06-10'), // Junio
];

foreach ($fechasTest as $fechaEmision) {
    echo "\n--- FECHA EMISIÓN: {$fechaEmision->format('d/m/Y')} ---\n";
    
    // Simular el cálculo manual que debería hacer el servicio
    $tarifa = $cliente->tarifa;
    
    if ($tarifa->tipo_vencimiento === 'dia_fijo' && $tarifa->dia_fijo_vencimiento) {
        $fechaVencimiento = $fechaEmision->copy()->addMonth();
        $diaDeseado = $tarifa->dia_fijo_vencimiento;
        $ultimoDiaDelMes = $fechaVencimiento->daysInMonth;
        
        if ($diaDeseado > $ultimoDiaDelMes) {
            $fechaVencimiento->day($ultimoDiaDelMes);
        } else {
            $fechaVencimiento->day($diaDeseado);
        }
    } else {
        $diasVencimiento = $tarifa->dias_vencimiento ?? 30;
        $fechaVencimiento = $fechaEmision->copy()->addMonth()->startOfMonth();
        $fechaVencimiento->addDays($diasVencimiento - 1);
    }
    
    echo "Fecha vencimiento calculada: {$fechaVencimiento->format('d/m/Y')}\n";
    
    // Comparar con lo que debería ser
    $mesEsperado = $fechaEmision->copy()->addMonth()->format('F Y');
    echo "Mes esperado: {$mesEsperado}\n";
}

echo "\n=== FIN DEL TEST ===\n";