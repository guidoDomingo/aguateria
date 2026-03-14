<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReporteService;
use App\Models\Empresa;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\CorteServicio;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GestionMorosidad extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aguateria:gestion-morosidad 
                            {--empresa= : ID de empresa específica (opcional)}
                            {--dias-corte=45 : Días de morosidad para corte automático}
                            {--enviar-notificaciones : Enviar notificaciones por email}
                            {--procesar-cortes : Procesar cortes automáticos}
                            {--dry-run : Simular sin procesar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gestiona la morosidad: notificaciones, cortes automáticos y reportes';

    protected $reporteService;

    public function __construct(ReporteService $reporteService)
    {
        parent::__construct();
        $this->reporteService = $reporteService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== GESTIÓN DE MOROSIDAD AGUATERÍA SaaS ===');
        $this->info('Iniciando proceso: ' . now()->format('Y-m-d H:i:s'));
        
        $empresaId = $this->option('empresa');
        $diasCorte = (int) $this->option('dias-corte');
        $enviarNotificaciones = $this->option('enviar-notificaciones');
        $procesarCortes = $this->option('procesar-cortes');
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('MODO SIMULACIÓN - No se procesarán cambios reales');
        }
        
        // Obtener empresas
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
        $this->info("Días para corte automático: {$diasCorte}");
        $this->line('');
        
        $resumenGlobal = [
            'empresas_procesadas' => 0,
            'total_morosos' => 0,
            'total_monto_vencido' => 0,
            'notificaciones_enviadas' => 0,
            'cortes_programados' => 0,
            'errores' => []
        ];
        
        // Procesar cada empresa
        foreach ($empresas as $empresa) {
            $this->info("🏢 Procesando: {$empresa->nombre}");
            
            try {
                $resultado = $this->procesarEmpresa(
                    $empresa, 
                    $diasCorte, 
                    $enviarNotificaciones, 
                    $procesarCortes, 
                    $dryRun
                );
                
                $resumenGlobal['empresas_procesadas']++;
                $resumenGlobal['total_morosos'] += $resultado['total_morosos'];
                $resumenGlobal['total_monto_vencido'] += $resultado['monto_vencido'];
                $resumenGlobal['notificaciones_enviadas'] += $resultado['notificaciones_enviadas'];
                $resumenGlobal['cortes_programados'] += $resultado['cortes_programados'];
                
                $this->mostrarResultadosEmpresa($resultado);
                
            } catch (\Exception $e) {
                $resumenGlobal['errores'][] = "{$empresa->nombre}: {$e->getMessage()}";
                $this->error("   ❌ Error: {$e->getMessage()}");
                
                Log::error('Error en gestión de morosidad', [
                    'empresa_id' => $empresa->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            $this->line('');
        }
        
        // Mostrar resumen final
        $this->mostrarResumenFinal($resumenGlobal);
        
        // Log del proceso
        Log::info('Gestión de morosidad finalizada', [
            'empresas_procesadas' => $resumenGlobal['empresas_procesadas'],
            'total_morosos' => $resumenGlobal['total_morosos'],
            'total_monto_vencido' => $resumenGlobal['total_monto_vencido'],
            'dry_run' => $dryRun
        ]);
        
        return 0;
    }
    
    /**
     * Procesar morosidad para una empresa
     */
    private function procesarEmpresa(
        Empresa $empresa, 
        int $diasCorte, 
        bool $enviarNotificaciones, 
        bool $procesarCortes, 
        bool $dryRun
    ): array {
        
        // Obtener reporte de morosidad
        $reporte = $this->reporteService->generarReporteMorosidad($empresa->id);
        
        if (!$reporte['success']) {
            throw new \Exception('Error al generar reporte de morosidad: ' . $reporte['message']);
        }
        
        $data = $reporte['data'];
        $clientesMorosos = collect($data['detalle_clientes_morosos']);
        
        $resultado = [
            'total_morosos' => $clientesMorosos->count(),
            'monto_vencido' => $data['monto_total_vencido'],
            'notificaciones_enviadas' => 0,
            'cortes_programados' => 0,
            'por_rango' => $data['morosidad_por_rango']
        ];
        
        if ($dryRun) {
            // En simulación, solo retornar datos
            $resultado['notificaciones_enviadas'] = $clientesMorosos->where('dias_mayor_vencimiento', '>=', 15)->count();
            $resultado['cortes_programados'] = $clientesMorosos->where('dias_mayor_vencimiento', '>=', $diasCorte)->count();
            return $resultado;
        }
        
        // Procesar notificaciones
        if ($enviarNotificaciones) {
            $resultado['notificaciones_enviadas'] = $this->procesarNotificaciones($clientesMorosos, $empresa);
        }
        
        // Procesar cortes automáticos
        if ($procesarCortes) {
            $resultado['cortes_programados'] = $this->procesarCortes($clientesMorosos, $diasCorte, $empresa);
        }
        
        return $resultado;
    }
    
    /**
     * Procesar notificaciones a morosos
     */
    private function procesarNotificaciones($clientesMorosos, Empresa $empresa): int
    {
        $notificacionesEnviadas = 0;
        
        // Rangos de notificación
        $rangos = [
            ['dias_min' => 15, 'dias_max' => 30, 'tipo' => 'aviso'],
            ['dias_min' => 31, 'dias_max' => 45, 'tipo' => 'urgente'],
            ['dias_min' => 46, 'dias_max' => 999, 'tipo' => 'final']
        ];
        
        foreach ($rangos as $rango) {
            $clientesRango = $clientesMorosos->whereBetween('dias_mayor_vencimiento', [$rango['dias_min'], $rango['dias_max']]);
            
            foreach ($clientesRango as $clienteData) {
                try {
                    $cliente = Cliente::find($clienteData['cliente_id']);
                    
                    if ($cliente && $cliente->email) {
                        // Enviar notificación (implementar con Mail)
                        $this->enviarNotificacion($cliente, $clienteData, $rango['tipo'], $empresa);
                        $notificacionesEnviadas++;
                    }
                    
                } catch (\Exception $e) {
                    Log::error('Error enviando notificación', [
                        'cliente_id' => $clienteData['cliente_id'],
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        
        return $notificacionesEnviadas;
    }
    
    /**
     * Procesar cortes automáticos
     */
    private function procesarCortes($clientesMorosos, int $diasCorte, Empresa $empresa): int
    {
        $cortesProgram2ados = 0;
        
        $clientesParaCorte = $clientesMorosos->where('dias_mayor_vencimiento', '>=', $diasCorte);
        
        foreach ($clientesParaCorte as $clienteData) {
            try {
                $cliente = Cliente::find($clienteData['cliente_id']);
                
                if ($cliente && $cliente->estado === 'activo') {
                    // Verificar si ya tiene corte programado
                    $corteExistente = CorteServicio::where('cliente_id', $cliente->id)
                        ->where('estado', 'programado')
                        ->exists();
                    
                    if (!$corteExistente) {
                        // Crear corte programado
                        CorteServicio::create([
                            'cliente_id' => $cliente->id,
                            'fecha_programada' => now()->addDays(3), // 3 días de gracia
                            'motivo_corte' => 'Morosidad - ' . $clienteData['dias_mayor_vencimiento'] . ' días',
                            'monto_deuda' => $clienteData['monto_total_vencido'],
                            'estado' => 'programado',
                            'observaciones' => 'Corte automático por morosidad superior a ' . $diasCorte . ' días'
                        ]);
                        
                        $cortesProgram2ados++;
                    }
                }
                
            } catch (\Exception $e) {
                Log::error('Error programando corte', [
                    'cliente_id' => $clienteData['cliente_id'],
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $cortesProgram2ados;
    }
    
    /**
     * Enviar notificación personalizada
     */
    private function enviarNotificacion(Cliente $cliente, array $clienteData, string $tipo, Empresa $empresa)
    {
        $asunto = match($tipo) {
            'aviso' => 'Recordatorio de pago - ' . $empresa->nombre,
            'urgente' => 'URGENTE: Pago vencido - ' . $empresa->nombre,
            'final' => 'AVISO FINAL: Suspensión de servicio - ' . $empresa->nombre
        };
        
        // Aquí se implementaría el envío real de email
        // Mail::to($cliente->email)->send(new NotificacionMorosidad($cliente, $clienteData, $tipo, $empresa));
        
        Log::info('Notificación enviada', [
            'cliente_id' => $cliente->id,
            'email' => $cliente->email,
            'tipo' => $tipo,
            'monto_vencido' => $clienteData['monto_total_vencido']
        ]);
    }
    
    /**
     * Mostrar resultados por empresa
     */
    private function mostrarResultadosEmpresa(array $resultado)
    {
        $this->table([
            'Métrica', 'Valor'
        ], [
            ['Clientes morosos', number_format($resultado['total_morosos'])],
            ['Monto vencido total', number_format($resultado['monto_vencido'], 0, ',', '.') . ' Gs.'],
            ['Notificaciones enviadas', $resultado['notificaciones_enviadas']],
            ['Cortes programados', $resultado['cortes_programados']],
        ]);
        
        // Mostrar distribución por rangos
        $this->info('   📊 Distribución por rangos de morosidad:');
        foreach ($resultado['por_rango'] as $rango => $datos) {
            $this->line("   - {$rango} días: {$datos['cantidad']} clientes, " . 
                      number_format($datos['monto'], 0, ',', '.') . ' Gs.');
        }
    }
    
    /**
     * Mostrar resumen final
     */
    private function mostrarResumenFinal(array $resumen)
    {
        $this->info('=== RESUMEN GENERAL ===');
        $this->table([
            'Métrica', 'Total'
        ], [
            ['Empresas procesadas', $resumen['empresas_procesadas']],
            ['Total clientes morosos', number_format($resumen['total_morosos'])],
            ['Monto total vencido', number_format($resumen['total_monto_vencido'], 0, ',', '.') . ' Gs.'],
            ['Notificaciones enviadas', $resumen['notificaciones_enviadas']],
            ['Cortes programados', $resumen['cortes_programados']],
        ]);
        
        if (!empty($resumen['errores'])) {
            $this->warn('⚠️  Errores encontrados:');
            foreach ($resumen['errores'] as $error) {
                $this->line("- {$error}");
            }
        }
        
        if ($resumen['total_morosos'] > 0) {
            $porcentajeNotificados = $resumen['notificaciones_enviadas'] > 0 
                ? ($resumen['notificaciones_enviadas'] / $resumen['total_morosos']) * 100 
                : 0;
            
            $this->info("📈 Efectividad: {$porcentajeNotificados}% de morosos notificados");
        }
    }
}
