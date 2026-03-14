<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Cliente;
use Carbon\Carbon;

echo "=== PROBANDO NUEVA LÓGICA DE FECHAS ===\n\n";

$clientes = Cliente::with('tarifa')->get();

// Probar con diferentes fechas de emisión
$fechasEmision = [
    Carbon::parse('2026-03-14'), // Marzo
    Carbon::parse('2026-04-15'), // Abril
    Carbon::parse('2026-05-30'), // Mayo final
];

foreach ($fechasEmision as $fechaEmision) {
    echo "=== FECHA DE EMISIÓN: {$fechaEmision->format('d/m/Y')} ===\n";
    
    foreach ($clientes as $cliente) {
        echo "Cliente: {$cliente->nombre_completo}\n";
        echo "Tarifa: {$cliente->tarifa->nombre} - Tipo: {$cliente->tarifa->tipo_vencimiento}\n";
        
        $tarifa = $cliente->tarifa;
        
        if ($tarifa->tipo_vencimiento === 'dia_fijo' && $tarifa->dia_fijo_vencimiento) {
            // Día fijo: mes siguiente + día específico
            $fechaVencimiento = $fechaEmision->copy()->addMonth();
            $diaDeseado = $tarifa->dia_fijo_vencimiento;
            $ultimoDiaDelMes = $fechaVencimiento->daysInMonth;
            
            if ($diaDeseado > $ultimoDiaDelMes) {
                $fechaVencimiento->day($ultimoDiaDelMes);
            } else {
                $fechaVencimiento->day($diaDeseado);
            }
            
            echo "Día fijo: {$tarifa->dia_fijo_vencimiento} → Vencimiento: {$fechaVencimiento->format('d/m/Y')}\n";
        } else {
            // Días corridos: mes siguiente + días desde inicio del mes
            $diasVencimiento = $tarifa->dias_vencimiento ?? 30;
            $fechaVencimiento = $fechaEmision->copy()->addMonth()->startOfMonth();
            $fechaVencimiento->addDays($diasVencimiento - 1); // -1 porque startOfMonth es día 1
            
            echo "Días corridos: {$diasVencimiento} → Vencimiento: {$fechaVencimiento->format('d/m/Y')}\n";
        }
        
        echo "---\n";
    }
    echo "\n";
}

echo "=== PROCESO COMPLETADO ===\n";