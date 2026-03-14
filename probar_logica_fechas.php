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

echo "=== PROBANDO LA LÓGICA DE FECHAS CORREGIDA ===\n\n";

$facturaService = app(FacturacionService::class);

// Simular fechas de emisión en diferentes momentos
$fechasEmision = [
    Carbon::parse('2026-04-15'), // Mediados de abril
    Carbon::parse('2026-05-01'), // Inicio de mayo
    Carbon::parse('2026-06-30')  // Final de junio
];

$cliente = Cliente::first();

foreach ($fechasEmision as $fechaEmision) {
    echo "Fecha de emisión: {$fechaEmision->format('d/m/Y')}\n";
    echo "Cliente: {$cliente->nombre_completo}\n";
    echo "Tarifa: {$cliente->tarifa->nombre} - Tipo: {$cliente->tarifa->tipo_vencimiento}\n";
    
    // Calcular fecha de vencimiento usando método privado (simulado)
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
        $fechaVencimiento = $fechaEmision->copy()->addDays($diasVencimiento);
    }
    
    echo "Fecha de vencimiento calculada: {$fechaVencimiento->format('d/m/Y')}\n";
    echo "---\n\n";
}

echo "=== PROCESO COMPLETADO ===\n";