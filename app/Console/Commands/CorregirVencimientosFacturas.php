<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Factura;
use App\Services\FacturacionService;
use Carbon\Carbon;

class CorregirVencimientosFacturas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facturas:corregir-vencimientos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige las fechas de vencimiento de facturas según la configuración actual de tarifas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando corrección de vencimientos de facturas...');
        
        // Obtener facturas pendientes y vencidas (no consolidadas ni pagadas)
        $facturas = Factura::with(['cliente.tarifa', 'periodo'])
                          ->whereIn('estado', ['pendiente', 'vencido'])
                          ->get();
        
        $this->info("Encontradas {$facturas->count()} facturas para revisar");
        
        $corregidas = 0;
        $errores = 0;
        
        foreach ($facturas as $factura) {
            try {
                $fechaVencimientoCorrect = $this->calcularFechaVencimiento($factura->fecha_emision, $factura->cliente);
                
                if (!$factura->fecha_vencimiento->equalTo($fechaVencimientoCorrect)) {
                    $this->line("Corrigiendo factura #{$factura->numero_factura}:");
                    $this->line("  Cliente: {$factura->cliente->nombre} {$factura->cliente->apellido}");
                    $this->line("  Vencimiento anterior: {$factura->fecha_vencimiento->format('d/m/Y')}");
                    $this->line("  Vencimiento correcto: {$fechaVencimientoCorrect->format('d/m/Y')}");
                    
                    $factura->update(['fecha_vencimiento' => $fechaVencimientoCorrect]);
                    $corregidas++;
                }
                
            } catch (\Exception $e) {
                $this->error("Error en factura #{$factura->numero_factura}: " . $e->getMessage());
                $errores++;
            }
        }
        
        $this->info("\n=== RESUMEN ===");
        $this->info("Facturas revisadas: {$facturas->count()}");
        $this->info("Facturas corregidas: {$corregidas}");
        $this->info("Errores: {$errores}");
        
        return 0;
    }
    
    /**
     * Calcular fecha de vencimiento según configuración de tarifa
     */
    private function calcularFechaVencimiento(Carbon $fechaEmision, $cliente): Carbon
    {
        $tarifa = $cliente->tarifa;
        
        // Si el cliente tiene día personalizado, siempre usarlo
        if ($cliente->dia_vencimiento_personalizado) {
            return $fechaEmision->copy()->addMonth()->day($cliente->dia_vencimiento_personalizado);
        }
        
        // Usar configuración de la tarifa
        if ($tarifa->tipo_vencimiento === 'dia_fijo' && $tarifa->dia_fijo_vencimiento) {
            // Día fijo: SIEMPRE usar el día configurado del MES SIGUIENTE al de emisión
            $fechaVencimiento = $fechaEmision->copy()->addMonth();
            
            // Ajustar al día correcto del mes
            $diaDeseado = $tarifa->dia_fijo_vencimiento;
            $ultimoDiaDelMes = $fechaVencimiento->daysInMonth;
            
            // Si el día deseado no existe en el mes, usar el último día del mes
            if ($diaDeseado > $ultimoDiaDelMes) {
                $fechaVencimiento->day($ultimoDiaDelMes);
            } else {
                $fechaVencimiento->day($diaDeseado);
            }
            
            return $fechaVencimiento;
        } else {
            // Días corridos: usar los días de vencimiento configurados desde la fecha de emisión
            $diasVencimiento = $tarifa->dias_vencimiento ?? 30;
            return $fechaEmision->copy()->addDays($diasVencimiento);
        }
    }
}
