<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Services\FacturacionService;
use App\Models\Cliente;
use App\Models\PeriodoFacturacion;
use Carbon\Carbon;

echo "=== SIMULANDO FACTURACIÓN AUTOMÁTICA EXACTA ===\n\n";

// Simular exactamente lo que hace la facturación automática
$facturaService = app(FacturacionService::class);

echo "Fecha actual del sistema: " . Carbon::now()->format('d/m/Y H:i:s') . "\n\n";

try {
    // Esto es lo que hace generarFacturasPeriodoActual()
    $fechaActual = Carbon::now();
    echo "generarFacturasPeriodoActual() usaría: {$fechaActual->year}-{$fechaActual->month}\n";
    
    // Simular generarFacturasMensuales para el mes actual
    $año = $fechaActual->year; // 2026
    $mes = $fechaActual->month; // 3 (marzo)
    
    echo "Parámetros: año={$año}, mes={$mes}\n\n";
    
    // Crear período como lo hace el servicio
    $periodo = PeriodoFacturacion::firstOrCreate([
        'empresa_id' => 1,
        'año' => $año,
        'mes' => $mes
    ], [
        'nombre' => Carbon::create($año, $mes, 1)->locale('es')->format('F Y'),
        'fecha_inicio' => Carbon::create($año, $mes, 1),
        'fecha_fin' => Carbon::create($año, $mes, 1)->endOfMonth(),
        'estado' => 'activo'
    ]);
    
    echo "Período: {$periodo->nombre}\n";
    echo "Fecha inicio: {$periodo->fecha_inicio->format('d/m/Y')}\n";
    echo "Fecha fin: {$periodo->fecha_fin->format('d/m/Y')}\n\n";
    
    // Probar con un cliente
    $cliente = Cliente::first();
    echo "Cliente: {$cliente->nombre_completo}\n";
    echo "Tarifa: {$cliente->tarifa->nombre} - {$cliente->tarifa->tipo_vencimiento}\n\n";
    
    // Simular lo que hace generarFacturaParaClienteConAcumulacion
    echo "--- SIMULANDO GENERACIÓN DE FACTURA ---\n";
    
    // Fecha de emisión (esto es lo que usa el servicio)
    $fechaEmision = Carbon::now(); // now()
    echo "Fecha emisión: {$fechaEmision->format('d/m/Y')}\n";
    
    // Calcular fecha de vencimiento usando la lógica del servicio
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
    
    // ¿Cuál debería ser?
    echo "\n--- ANÁLISIS ---\n";
    echo "Si genero facturas para MARZO 2026:\n";
    echo "- Deberían vencer en ABRIL 2026\n";
    echo "- guido (día fijo 10): 10/04/2026\n";
    echo "- otros (30 días): 30/04/2026\n";
    
    echo "\nPero si genero para ABRIL 2026:\n";
    echo "- Deberían vencer en MAYO 2026\n";
    echo "- guido (día fijo 10): 10/05/2026\n";
    echo "- otros (30 días): 30/05/2026\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DE SIMULACIÓN ===\n";