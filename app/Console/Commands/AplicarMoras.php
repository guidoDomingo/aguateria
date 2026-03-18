<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Empresa;
use App\Models\Factura;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AplicarMoras extends Command
{
    protected $signature = 'aguateria:aplicar-moras
                            {--empresa= : ID de empresa específica (opcional)}
                            {--dry-run : Simulación sin guardar cambios}';

    protected $description = 'Aplica moras y actualiza avisos (último aviso / desconexión) en facturas vencidas';

    public function handle()
    {
        $this->info('=== APLICAR MORAS Y AVISOS ===');
        $this->info('Inicio: ' . now()->format('Y-m-d H:i:s'));

        $empresaId = $this->option('empresa');
        $dryRun    = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('MODO SIMULACIÓN - No se guardarán cambios');
        }

        $empresas = $empresaId
            ? Empresa::where('id', $empresaId)->get()
            : Empresa::whereIn('estado', ['activa', 'trial'])->get();

        foreach ($empresas as $empresa) {
            $this->procesarEmpresa($empresa, $dryRun);
        }

        $this->info('Proceso finalizado.');
        return 0;
    }

    private function procesarEmpresa(Empresa $empresa, bool $dryRun): void
    {
        $config = $empresa->configuraciones ?? [];

        $moraTipo      = $config['mora_tipo']          ?? 'fijo';
        $moraValor     = (float) ($config['mora_valor']        ?? 0);
        $diasGracia    = (int)   ($config['mora_dias_gracia']  ?? 0);
        $mesesAviso    = (int)   ($config['meses_ultimo_aviso'] ?? 2);
        $mesesDesconex = (int)   ($config['meses_desconexion']  ?? 3);

        $hoy = Carbon::today();
        $fechaLimite = $hoy->copy()->subDays($diasGracia);

        // Limpiar aviso en facturas ya pagadas/anuladas
        Factura::where('empresa_id', $empresa->id)
            ->whereIn('estado', ['pagado', 'anulado'])
            ->where('aviso', '!=', 'ninguno')
            ->update(['aviso' => 'ninguno']);

        // Facturas vencidas y no pagadas
        $facturas = Factura::where('empresa_id', $empresa->id)
            ->whereIn('estado', ['pendiente', 'parcial', 'vencido'])
            ->where('fecha_vencimiento', '<', $fechaLimite)
            ->get();

        $actualizadas = 0;

        foreach ($facturas as $factura) {
            // Calcular mora
            $nuevaMora = 0;
            if ($moraValor > 0) {
                if ($moraTipo === 'porcentaje') {
                    $nuevaMora = round($factura->subtotal * $moraValor / 100);
                } else {
                    $nuevaMora = $moraValor;
                }
            }

            // Contar meses impagos del cliente (facturas vencidas no pagadas)
            $mesesImpagos = Factura::where('empresa_id', $empresa->id)
                ->where('cliente_id', $factura->cliente_id)
                ->whereIn('estado', ['pendiente', 'parcial', 'vencido'])
                ->where('fecha_vencimiento', '<', $hoy)
                ->count();

            // Determinar aviso
            $aviso = 'ninguno';
            if ($mesesImpagos >= $mesesDesconex) {
                $aviso = 'desconexion';
            } elseif ($mesesImpagos >= $mesesAviso) {
                $aviso = 'ultimo_aviso';
            }

            $cambio = false;

            if ($factura->mora != $nuevaMora) {
                $cambio = true;
            }
            if ($factura->aviso !== $aviso) {
                $cambio = true;
            }
            if ($factura->estado === 'pendiente' && $factura->fecha_vencimiento->lt($hoy)) {
                $cambio = true; // marcar como vencido
            }

            if ($cambio) {
                $this->line("  {$empresa->nombre} | Factura #{$factura->numero_factura} | Mora: {$nuevaMora} | Aviso: {$aviso} | Meses impagos: {$mesesImpagos}");

                if (!$dryRun) {
                    $nuevoTotal = $factura->subtotal - $factura->descuento + $nuevaMora + $factura->impuesto;
                    $factura->update([
                        'mora'            => $nuevaMora,
                        'total'           => $nuevoTotal,
                        'saldo_pendiente' => $nuevoTotal - ($factura->total - $factura->saldo_pendiente),
                        'aviso'           => $aviso,
                        'estado'          => 'vencido',
                    ]);
                }

                $actualizadas++;
            }
        }

        $this->info("  {$empresa->nombre}: {$actualizadas} facturas actualizadas.");

        Log::info('Moras aplicadas', [
            'empresa_id'  => $empresa->id,
            'actualizadas' => $actualizadas,
            'dry_run'     => $dryRun,
        ]);
    }
}
