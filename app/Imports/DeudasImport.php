<?php

namespace App\Imports;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\PeriodoFacturacion;
use App\Repositories\FacturaRepository;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DeudasImport implements ToCollection, WithHeadingRow
{
    private int $empresaId;
    public array $resultados = ['creados' => 0, 'omitidos' => 0, 'errores' => []];

    public function __construct(int $empresaId)
    {
        $this->empresaId = $empresaId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $fila = $index + 2;

            $cedula = trim($row['cedula_cliente'] ?? '');
            $monto  = (float) str_replace(['.', ','], ['', '.'], $row['monto'] ?? 0);
            $mes    = (int) ($row['mes'] ?? 0);
            // El encabezado 'año' puede transformarse de distintas formas al leer el Excel
            $anio   = (int) ($row['año'] ?? $row['anio'] ?? $row['ano'] ?? $row['a_o'] ?? $row['year'] ?? 0);

            if (empty($cedula) || $monto <= 0) {
                if (!empty($cedula) || $monto > 0) {
                    $this->resultados['errores'][] = "Fila {$fila}: cedula o monto inválido.";
                }
                continue;
            }

            // Buscar cliente
            $cliente = Cliente::where('empresa_id', $this->empresaId)
                ->where('cedula', $cedula)
                ->first();

            if (!$cliente) {
                $this->resultados['errores'][] = "Fila {$fila}: cliente con cédula {$cedula} no encontrado.";
                continue;
            }

            try {
                // Resolver o crear período
                if ($mes >= 1 && $mes <= 12 && $anio >= 2000) {
                    $periodo = PeriodoFacturacion::where('empresa_id', $this->empresaId)
                        ->where('mes', $mes)
                        ->where('año', $anio)
                        ->first();

                    if (!$periodo) {
                        $periodo = PeriodoFacturacion::create([
                            'empresa_id'       => $this->empresaId,
                            'mes'              => $mes,
                            'año'              => $anio,
                            'nombre'           => \Carbon\Carbon::createFromDate($anio, $mes, 1)->locale('es')->isoFormat('MMMM YYYY'),
                            'fecha_inicio'     => \Carbon\Carbon::createFromDate($anio, $mes, 1)->startOfMonth(),
                            'fecha_fin'        => \Carbon\Carbon::createFromDate($anio, $mes, 1)->endOfMonth(),
                            'fecha_vencimiento'=> \Carbon\Carbon::createFromDate($anio, $mes, 15)->addMonth(),
                            'estado'           => 'cerrado',
                        ]);
                    }
                } else {
                    // Usar período activo como fallback
                    $periodo = PeriodoFacturacion::where('empresa_id', $this->empresaId)
                        ->where('estado', 'activo')
                        ->first()
                        ?? PeriodoFacturacion::where('empresa_id', $this->empresaId)
                            ->latest()
                            ->first();
                }

                // Fecha vencimiento
                $fechaVenc = null;
                $fechaVencStr = trim($row['fecha_vencimiento'] ?? '');
                if ($fechaVencStr) {
                    try {
                        $fechaVenc = Carbon::createFromFormat('d/m/Y', $fechaVencStr);
                    } catch (\Exception) {
                        try {
                            $fechaVenc = Carbon::parse($fechaVencStr);
                        } catch (\Exception) {}
                    }
                }
                if (!$fechaVenc && $mes && $anio) {
                    $fechaVenc = Carbon::createFromDate($anio, $mes, 15)->addMonth();
                }
                $fechaVenc ??= now()->subMonth();

                // Número de factura
                $repo = new FacturaRepository(new Factura());
                $numeroInfo = $repo->siguienteNumeroFactura();

                Factura::create([
                    'empresa_id'      => $this->empresaId,
                    'cliente_id'      => $cliente->id,
                    'periodo_id'      => $periodo?->id,
                    'numero_factura'  => $numeroInfo['numero_factura'],
                    'serie'           => $numeroInfo['serie'],
                    'numero'          => $numeroInfo['numero'],
                    'fecha_emision'   => $fechaVenc->copy()->subMonth(),
                    'fecha_vencimiento' => $fechaVenc,
                    'subtotal'        => $monto,
                    'descuento'       => 0,
                    'mora'            => 0,
                    'total'           => $monto,
                    'saldo_pendiente' => $monto,
                    'monto_pagado'    => 0,
                    'estado'          => 'vencido',
                    'observaciones'   => trim($row['observaciones'] ?? '') ?: 'Deuda retroactiva',
                    'datos_cliente'   => [
                        'nombre'   => $cliente->nombre . ' ' . $cliente->apellido,
                        'cedula'   => $cliente->cedula,
                        'direccion'=> $cliente->direccion,
                    ],
                ]);

                $this->resultados['creados']++;
            } catch (\Exception $e) {
                $this->resultados['errores'][] = "Fila {$fila}: " . $e->getMessage();
            }
        }
    }
}
