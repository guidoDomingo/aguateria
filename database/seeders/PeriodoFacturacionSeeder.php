<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PeriodoFacturacion;
use App\Models\Empresa;
use Carbon\Carbon;

class PeriodoFacturacionSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $empresas = Empresa::all();
        
        foreach ($empresas as $empresa) {
            // Crear períodos del año actual y próximo
            $años = [now()->year - 1, now()->year, now()->year + 1];
            
            foreach ($años as $año) {
                for ($mes = 1; $mes <= 12; $mes++) {
                    $fechaInicio = Carbon::createFromDate($año, $mes, 1)->startOfMonth();
                    $fechaFin = $fechaInicio->copy()->endOfMonth();
                    $fechaVencimiento = $fechaFin->copy()->addDays(10);
                    $fechaFacturacion = $fechaFin->copy()->subDays(5);
                    
                    // Determinar estado según el mes
                    $fechaActual = now();
                    $estado = 'abierto';
                    
                    if ($fechaInicio->isPast() && $fechaFin->isPast()) {
                        $estado = 'cerrado';
                    } elseif ($fechaInicio->isPast() && $fechaFin->isFuture()) {
                        $estado = 'abierto';
                    } else {
                        $estado = 'abierto'; // Períodos futuros también como abiertos
                    }
                    
                    $nombreMes = $fechaInicio->locale('es')->isoFormat('MMMM');
                    
                    PeriodoFacturacion::updateOrCreate([
                        'empresa_id' => $empresa->id,
                        'año' => $año,
                        'mes' => $mes,
                    ], [
                        'nombre' => ucfirst($nombreMes) . ' ' . $año,
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin,
                        'fecha_vencimiento' => $fechaVencimiento,
                        'fecha_facturacion' => $fechaFacturacion,
                        'estado' => $estado,
                        'total_facturas' => 0,
                        'monto_total' => 0,
                        'observaciones' => "Período generado automáticamente"
                    ]);
                }
            }
        }
    }
}