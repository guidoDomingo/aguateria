<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PeriodoFacturacionService;
use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FacturacionAutomatica extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aguateria:facturacion-automatica 
                            {--empresa= : ID de empresa específica (opcional)}
                            {--mes= : Mes a facturar (formato: YYYY-MM)}
                            {--dry-run : Simulación sin procesar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesa la facturación automática mensual para todas las empresas o una específica';

    protected $periodoService;

    /**
     * Create a new command instance.
     */
    public function __construct(PeriodoFacturacionService $periodoService)
    {
        parent::__construct();
        $this->periodoService = $periodoService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== FACTURACIÓN AUTOMÁTICA AGUATERÍA SaaS ===');
        $this->info('Iniciando proceso: ' . now()->format('Y-m-d H:i:s'));
        
        $empresaId = $this->option('empresa');
        $mes = $this->option('mes');
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('MODO SIMULACIÓN - No se procesarán las transacciones');
        }
        
        // Determinar período a procesar
        if ($mes) {
            $fechaProceso = Carbon::createFromFormat('Y-m', $mes);
        } else {
            $fechaProceso = now();
        }
        
        $año = $fechaProceso->year;
        $mesNumero = $fechaProceso->month;
        
        $this->info("Procesando período: {$fechaProceso->format('F Y')} ({$año}-{$mesNumero})");
        
        // Obtener empresas a procesar
        if ($empresaId) {
            $empresas = Empresa::where('id', $empresaId)->get();
            if ($empresas->isEmpty()) {
                $this->error("Empresa con ID {$empresaId} no encontrada");
                return 1;
            }
        } else {
            $empresas = Empresa::where('estado', 'activa')->get();
        }
        
        $this->info("Empresas a procesar: {$empresas->count()}");
        $this->line('');
        
        $resumenGlobal = [
            'empresas_procesadas' => 0,
            'empresas_con_errores' => 0,
            'total_facturas' => 0,
            'monto_total' => 0,
            'errores' => []
        ];
        
        // Procesar cada empresa
        foreach ($empresas as $empresa) {
            $this->info("📊 Procesando: {$empresa->nombre}");
            
            try {
                $resultado = $this->procesarEmpresa($empresa, $año, $mesNumero, $dryRun);
                
                if ($resultado['success']) {
                    $resumenGlobal['empresas_procesadas']++;
                    $resumenGlobal['total_facturas'] += $resultado['facturas_generadas'];
                    $resumenGlobal['monto_total'] += $resultado['monto_total'];
                    
                    $this->info("   ✅ Facturas generadas: {$resultado['facturas_generadas']}");
                    $this->info("   💰 Monto total: " . number_format($resultado['monto_total'], 0, ',', '.') . ' Gs.');
                    
                    if (!empty($resultado['errores'])) {
                        $this->warn("   ⚠️  Errores individuales: " . count($resultado['errores']));
                    }
                } else {
                    $resumenGlobal['empresas_con_errores']++;
                    $resumenGlobal['errores'][] = "{$empresa->nombre}: {$resultado['message']}";
                    $this->error("   ❌ Error: {$resultado['message']}");
                }
                
            } catch (\Exception $e) {
                $resumenGlobal['empresas_con_errores']++;
                $resumenGlobal['errores'][] = "{$empresa->nombre}: {$e->getMessage()}";
                $this->error("   ❌ Excepción: {$e->getMessage()}");
                
                Log::error('Error en facturación automática', [
                    'empresa_id' => $empresa->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            $this->line('');
        }
        
        // Mostrar resumen final
        $this->info('=== RESUMEN FINAL ===');
        $this->table([
            'Métrica', 'Valor'
        ], [
            ['Empresas procesadas exitosamente', $resumenGlobal['empresas_procesadas']],
            ['Empresas con errores', $resumenGlobal['empresas_con_errores']],
            ['Total facturas generadas', number_format($resumenGlobal['total_facturas'])],
            ['Monto total facturado', number_format($resumenGlobal['monto_total'], 0, ',', '.') . ' Gs.'],
        ]);
        
        if (!empty($resumenGlobal['errores'])) {
            $this->warn('Errores encontrados:');
            foreach ($resumenGlobal['errores'] as $error) {
                $this->line("- {$error}");
            }
        }
        
        // Log del proceso
        Log::info('Facturación automática finalizada', [
            'periodo' => "{$año}-{$mesNumero}",
            'empresas_procesadas' => $resumenGlobal['empresas_procesadas'],
            'total_facturas' => $resumenGlobal['total_facturas'],
            'monto_total' => $resumenGlobal['monto_total'],
            'dry_run' => $dryRun
        ]);
        
        return 0;
    }
    
    /**
     * Procesar facturación para una empresa
     */
    private function procesarEmpresa(Empresa $empresa, int $año, int $mes, bool $dryRun): array
    {
        if ($dryRun) {
            // En modo simulación, solo mostrar lo que se haría
            return [
                'success' => true,
                'message' => 'Simulación exitosa',
                'facturas_generadas' => rand(50, 200), // Simulado
                'monto_total' => rand(5000000, 20000000), // Simulado
                'errores' => []
            ];
        }
        
        // Crear período si no existe
        $resultadoPeriodo = $this->periodoService->crearPeriodo($empresa->id, $año, $mes);
        
        if (!$resultadoPeriodo['success']) {
            // Si ya existe, buscar el período
            $periodo = \App\Models\PeriodoFacturacion::where('empresa_id', $empresa->id)
                ->where('año', $año)
                ->where('mes', $mes)
                ->first();
                
            if (!$periodo) {
                return [
                    'success' => false,
                    'message' => $resultadoPeriodo['message']
                ];
            }
        } else {
            $periodo = $resultadoPeriodo['periodo'];
        }
        
        // Procesar facturación masiva
        $resultadoFacturacion = $this->periodoService->procesarFacturacionMasiva($periodo->id);
        
        return [
            'success' => $resultadoFacturacion['success'],
            'message' => $resultadoFacturacion['message'],
            'facturas_generadas' => $resultadoFacturacion['estadisticas']['facturas_generadas'] ?? 0,
            'monto_total' => $resultadoFacturacion['estadisticas']['monto_total'] ?? 0,
            'errores' => $resultadoFacturacion['errores'] ?? []
        ];
    }
}
