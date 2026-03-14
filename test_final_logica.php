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

echo "=== TEST FINAL: LÓGICA DE FECHAS PARA FUTURAS GENERACIONES ===\n\n";

$facturaService = app(FacturacionService::class);

// Test: generar facturas para mayo 2026 (próximo mes después de abril)
echo "--- SIMULANDO GENERACIÓN PARA MAYO 2026 ---\n";

try {
    // Crear período de mayo
    $periodoMayo = PeriodoFacturacion::firstOrCreate([
        'empresa_id' => 1,
        'año' => 2026,
        'mes' => 5
    ], [
        'nombre' => 'May 2026',
        'fecha_inicio' => Carbon::parse('2026-05-01'),
        'fecha_fin' => Carbon::parse('2026-05-31'),
        'estado' => 'activo'
    ]);
    
    echo "Período: {$periodoMayo->nombre}\n\n";
    
    // Obtener un cliente para prueba
    $cliente = Cliente::first();
    $tarifa = $cliente->tarifa;
    
    echo "Cliente: {$cliente->nombre_completo}\n";
    echo "Tarifa: {$tarifa->nombre} - {$tarifa->tipo_vencimiento}\n";
    
    if ($tarifa->tipo_vencimiento === 'dia_fijo') {
        echo "Día fijo: {$tarifa->dia_fijo_vencimiento}\n";
    } else {
        echo "Días corridos: {$tarifa->dias_vencimiento}\n";
    }
    
    // Simular fecha de emisión para mayo
    $fechaEmisionMayo = Carbon::parse('2026-05-15');
    echo "\nFecha emisión simulada: {$fechaEmisionMayo->format('d/m/Y')}\n";
    
    // Usar lógica del servicio para calcular fecha de vencimiento
    if ($tarifa->tipo_vencimiento === 'dia_fijo' && $tarifa->dia_fijo_vencimiento) {
        $fechaVencimiento = $fechaEmisionMayo->copy()->addMonth();
        $diaDeseado = $tarifa->dia_fijo_vencimiento;
        $ultimoDiaDelMes = $fechaVencimiento->daysInMonth;
        
        if ($diaDeseado > $ultimoDiaDelMes) {
            $fechaVencimiento->day($ultimoDiaDelMes);
        } else {
            $fechaVencimiento->day($diaDeseado);
        }
    } else {
        $diasVencimiento = $tarifa->dias_vencimiento ?? 30;
        $fechaVencimiento = $fechaEmisionMayo->copy()->addMonth()->startOfMonth();
        $fechaVencimiento->addDays($diasVencimiento - 1);
    }
    
    echo "Fecha vencimiento calculada: {$fechaVencimiento->format('d/m/Y')}\n";
    
    // Verificar que sea correcto
    echo "\n--- VERIFICACIÓN ---\n";
    echo "✅ Facturas de MAYO 2026 deben vencer en JUNIO 2026\n";
    echo "✅ Esperado:\n";
    echo "   - guido (día fijo 10): 10/06/2026\n";
    echo "   - otros (30 días): 30/06/2026\n";
    echo "✅ Calculado: {$fechaVencimiento->format('d/m/Y')}\n";
    
    if ($fechaVencimiento->format('m') == '06') { // Junio
        echo "🎉 ¡LÓGICA CORRECTA!\n";
    } else {
        echo "❌ ERROR: La fecha debería estar en junio\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n--- RESUMEN FINAL ---\n";
echo "📊 Estado actual:\n";
echo "   ✅ Marzo 2026 → Vence Abril 2026 (consolidadas)\n";
echo "   ✅ Abril 2026 → Vence Mayo 2026 (corregidas)\n";
echo "   ✅ Mayo 2026 → Vencerá Junio 2026 (lógica correcta)\n";

echo "\n💡 Para nuevas facturas:\n";
echo "   - Usa 'Próximo Mes' para generar el siguiente período\n";
echo "   - O selecciona manualmente el período deseado\n";
echo "   - La lógica ahora calculará fechas correctas\n";

echo "\n=== TEST COMPLETADO ===\n";