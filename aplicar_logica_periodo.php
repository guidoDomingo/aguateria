<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Factura;
use App\Models\PeriodoFacturacion;
use Carbon\Carbon;

echo "=== APLICANDO NUEVA LÓGICA BASADA EN PERÍODO ===\n\n";

// Obtener todas las facturas de 2026
$facturas = Factura::with(['periodo', 'cliente', 'cliente.tarifa'])
    ->whereHas('periodo', function($query) {
        $query->where('año', 2026);
    })
    ->get();

foreach ($facturas as $factura) {
    $periodo = $factura->periodo;
    $tarifa = $factura->cliente->tarifa;
    
    echo "Factura #{$factura->numero_factura} - {$factura->cliente->nombre_completo}\n";
    echo "  Período: {$periodo->nombre} (año: {$periodo->año}, mes: {$periodo->mes})\n";
    echo "  Fecha ACTUAL: {$factura->fecha_vencimiento->format('d/m/Y')}\n";
    
    // Calcular fecha CORRECTA usando el período
    $nuevaFecha = null;
    
    if ($tarifa->tipo_vencimiento === 'dia_fijo' && $tarifa->dia_fijo_vencimiento) {
        // Día fijo: mes siguiente del período + día específico
        $fechaVencimiento = Carbon::create($periodo->año, $periodo->mes, 1)->addMonth();
        $diaDeseado = $tarifa->dia_fijo_vencimiento;
        $ultimoDiaDelMes = $fechaVencimiento->daysInMonth;
        
        if ($diaDeseado > $ultimoDiaDelMes) {
            $fechaVencimiento->day($ultimoDiaDelMes);
        } else {
            $fechaVencimiento->day($diaDeseado);
        }
        
        $nuevaFecha = $fechaVencimiento;
        echo "  Tarifa: {$tarifa->nombre} - Día fijo: {$tarifa->dia_fijo_vencimiento}\n";
    } else {
        // Días corridos: mes siguiente del período + días configurados
        $diasVencimiento = $tarifa->dias_vencimiento ?? 30;
        $fechaVencimiento = Carbon::create($periodo->año, $periodo->mes, 1)->addMonth();
        $fechaVencimiento->addDays($diasVencimiento - 1);
        
        $nuevaFecha = $fechaVencimiento;
        echo "  Tarifa: {$tarifa->nombre} - Días corridos: {$diasVencimiento}\n";
    }
    
    echo "  Fecha CORREGIDA: {$nuevaFecha->format('d/m/Y')}\n";
    
    // Solo actualizar si la fecha cambió
    if (!$factura->fecha_vencimiento->isSameDay($nuevaFecha)) {
        Factura::where('id', $factura->id)->update([
            'fecha_vencimiento' => $nuevaFecha->format('Y-m-d')
        ]);
        echo "  ✅ ACTUALIZADA\n";
    } else {
        echo "  ✓ Ya estaba correcta\n";
    }
    
    echo "---\n";
}

echo "\n=== VERIFICACIÓN FINAL ===\n";
$verificacion = Factura::with(['periodo', 'cliente'])
    ->whereHas('periodo', function($query) {
        $query->where('año', 2026);
    })
    ->orderBy('id')
    ->get();

foreach ($verificacion as $factura) {
    $periodo = $factura->periodo ? $factura->periodo->nombre : 'Sin período';
    echo "#{$factura->numero_factura} - {$periodo} → Vence: {$factura->fecha_vencimiento->format('d/m/Y')} - {$factura->estado}\n";
}

echo "\n--- REGLA APLICADA ---\n";
echo "✅ MARZO 2026 → Vence ABRIL 2026\n";
echo "✅ ABRIL 2026 → Vence MAYO 2026\n";
echo "✅ MAYO 2026 → Vencerá JUNIO 2026 (cuando se genere)\n";

echo "\n=== PROCESO COMPLETADO ===\n";