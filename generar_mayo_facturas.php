<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\FacturacionService;
use App\Services\PeriodoFacturacionService;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

// Simular usuario autenticado
$user = User::first();
Auth::login($user);

echo "\n=== GENERANDO FACTURAS PARA MAYO 2026 CON ACUMULACION ===\n";

$facturacionService = app(FacturacionService::class);
$periodoService = app(PeriodoFacturacionService::class);

try {
    // Generar facturas para Mayo 2026 (mes siguiente)
    $resultado = $facturacionService->generarFacturasMensuales(2026, 5, $user->empresa_id);
    
    if ($resultado['success']) {
        echo "✅ " . $resultado['message'] . "\n";
        echo sprintf("📊 Facturas generadas: %d\n", $resultado['facturas_generadas']);
        echo sprintf("💰 Monto total: Gs. %s\n", number_format($resultado['monto_total'], 0, ',', '.'));
        
        if (!empty($resultado['errores'])) {
            echo "\n❌ ERRORES:\n";
            foreach ($resultado['errores'] as $error) {
                echo "- " . $error['cliente_nombre'] . ": " . $error['error'] . "\n";
            }
        }
    } else {
        echo "❌ " . $resultado['message'] . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== VERIFICANDO FACTURAS GENERADAS ===\n";

use App\Models\Factura;
$facturasNuevas = Factura::whereHas('periodo', function($q) {
    $q->where('año', 2026)->where('mes', 5);
})->with(['cliente', 'detalles'])->get();

foreach ($facturasNuevas as $factura) {
    echo sprintf("\nFactura %s - Cliente: %s\n", 
        $factura->numero_factura, 
        $factura->cliente->nombre
    );
    echo sprintf("  Total: Gs. %s\n", number_format($factura->total, 0, ',', '.'));
    
    echo "  Detalles:\n";
    foreach ($factura->detalles as $detalle) {
        echo sprintf("    - %s: Gs. %s\n", 
            $detalle->concepto,
            number_format($detalle->subtotal, 0, ',', '.')
        );
    }
}

echo "\n=== PROCESO COMPLETADO ===\n";