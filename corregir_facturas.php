<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Factura;
use App\Models\Cliente;
use App\Services\FacturacionService;
use Carbon\Carbon;

echo "\n=== CORRIGIENDO VENCIMIENTOS DE FACTURAS EXISTENTES ===\n";

$facturas = Factura::with(['cliente.tarifa'])->get();

foreach ($facturas as $factura) {
    $cliente = $factura->cliente;
    $tarifa = $cliente->tarifa;
    $fechaEmision = $factura->fecha_emision;
    
    $vencimientoCalculado = null;
    
    // Lógica de cálculo según tipo de tarifa
    if ($cliente->dia_vencimiento_personalizado) {
        $vencimientoCalculado = $fechaEmision->copy()->addMonth()->day($cliente->dia_vencimiento_personalizado);
    } elseif ($tarifa->tipo_vencimiento === 'dia_fijo' && $tarifa->dia_fijo_vencimiento) {
        $vencimientoCalculado = $fechaEmision->copy()->addMonth()->day($tarifa->dia_fijo_vencimiento);
    } else {
        $diasVencimiento = $tarifa->dias_vencimiento ?? 30;
        $vencimientoCalculado = $fechaEmision->copy()->addDays($diasVencimiento);
    }
    
    if ($vencimientoCalculado && $factura->fecha_vencimiento->format('Y-m-d') !== $vencimientoCalculado->format('Y-m-d')) {
        echo sprintf("Corrigiendo Factura %s - Cliente: %s\n  Vencimiento anterior: %s\n  Vencimiento correcto: %s\n\n",
            $factura->numero_factura,
            $cliente->nombre,
            $factura->fecha_vencimiento->format('d/m/Y'),
            $vencimientoCalculado->format('d/m/Y')
        );
        
        $factura->update(['fecha_vencimiento' => $vencimientoCalculado]);
    }
}

echo "\n=== VERIFICANDO SALDOS PENDIENTES ===\n";

$clientes = Cliente::has('facturas')->with(['facturas' => function($query) {
    $query->whereIn('estado', ['pendiente', 'vencido', 'parcial'])->where('saldo_pendiente', '>', 0);
}])->get();

foreach ($clientes as $cliente) {
    if ($cliente->facturas->count() > 1) {
        echo sprintf("Cliente: %s tiene %d facturas pendientes por un total de Gs. %s\n",
            $cliente->nombre,
            $cliente->facturas->count(),
            number_format($cliente->facturas->sum('saldo_pendiente'), 0, ',', '.')
        );
    }
}

echo "\n=== PROCESO COMPLETADO ===\n";