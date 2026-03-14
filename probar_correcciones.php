<?php

// Script para probar las correcciones de fecha y estado
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Cliente;
use App\Models\PeriodoFacturacion;
use App\Services\FacturacionService;
use Carbon\Carbon;

echo "=== PROBANDO CORRECCIONES DE FECHA Y ESTADO ===\n\n";

// Configurar fecha actual para junio 2026
Carbon::setTestNow(Carbon::parse('2026-06-15'));
echo "Fecha simulada: " . Carbon::now()->format('d/m/Y') . "\n\n";

try {
    $facturaService = app(FacturacionService::class);
    
    // Crear período para junio 2026
    $periodo = PeriodoFacturacion::firstOrCreate([
        'empresa_id' => 1,
        'año' => 2026,
        'mes' => 6
    ], [
        'nombre' => 'June 2026',
        'fecha_inicio' => Carbon::parse('2026-06-01'),
        'fecha_fin' => Carbon::parse('2026-06-30'),
        'estado' => 'activo'
    ]);
    
    echo "Período creado/encontrado: {$periodo->nombre}\n\n";
    
    // Obtener cliente
    $cliente = Cliente::first();
    if (!$cliente) {
        echo "No se encontraron clientes\n";
        exit;
    }
    
    echo "Cliente: {$cliente->nombre_completo}\n";
    echo "Tarifa: {$cliente->tarifa->nombre} - Tipo: {$cliente->tarifa->tipo_vencimiento}\n";
    
    if ($cliente->tarifa->tipo_vencimiento === 'dia_fijo') {
        echo "Día fijo: {$cliente->tarifa->dia_fijo_vencimiento}\n";
    } else {
        echo "Días corridos: {$cliente->tarifa->dias_vencimiento}\n";
    }
    
    // Generar factura con acumulación
    $factura = $facturaService->generarFacturaParaClienteConAcumulacion($cliente, $periodo);
    
    echo "\n--- FACTURA GENERADA ---\n";
    echo "Número: {$factura->numero_factura}\n";
    echo "Cliente: {$factura->cliente->nombre_completo}\n";
    echo "Estado: {$factura->estado}\n";
    echo "Emisión: {$factura->fecha_emision->format('d/m/Y')}\n";
    echo "Vencimiento: {$factura->fecha_vencimiento->format('d/m/Y')}\n";
    echo "Total: Gs. " . number_format($factura->total, 0, ',', '.') . "\n";
    
    if ($factura->observaciones) {
        echo "Observaciones: {$factura->observaciones}\n";
    }
    
    // Verificar facturas consolidadas
    echo "\n--- VERIFICANDO FACTURAS CONSOLIDADAS ---\n";
    $facturasConsolidadas = \App\Models\Factura::where('cliente_id', $cliente->id)
        ->where('estado', 'consolidado')
        ->get();
    
    foreach ($facturasConsolidadas as $consolidada) {
        echo "#{$consolidada->numero_factura} - Estado: {$consolidada->estado} - Total: Gs. " . number_format($consolidada->total, 0, ',', '.') . "\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== PROCESO COMPLETADO ===\n";