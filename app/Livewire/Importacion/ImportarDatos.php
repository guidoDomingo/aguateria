<?php

namespace App\Livewire\Importacion;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Imports\ClientesImport;
use App\Imports\DeudasImport;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\PeriodoFacturacion;
use App\Repositories\FacturaRepository;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ImportarDatos extends Component
{
    use WithFileUploads;

    public string $tab = 'clientes'; // 'clientes' | 'deudas'

    // Importación Excel
    public $archivoClientes;
    public $archivoDeudas;

    // Resultados
    public ?array $resultadoClientes = null;
    public ?array $resultadoDeudas   = null;

    // Carga manual de deuda
    public array $filasDeuda = [];
    public ?array $resultadoManual = null;

    // Clientes para el selector manual
    public array $clientes = [];

    public function mount()
    {
        $this->clientes = Cliente::where('empresa_id', auth()->user()->empresa_id)
            ->where('estado', 'activo')
            ->orderBy('apellido')->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido', 'cedula'])
            ->map(fn($c) => ['id' => $c->id, 'label' => $c->apellido . ' ' . $c->nombre . ' (' . $c->cedula . ')'])
            ->toArray();

        $this->agregarFila();
    }

    public function agregarFila()
    {
        $this->filasDeuda[] = [
            'cliente_id'        => '',
            'mes'               => now()->subMonth()->month,
            'anio'              => now()->subMonth()->year,
            'monto'             => '',
            'fecha_vencimiento' => '',
            'observaciones'     => '',
        ];
    }

    public function eliminarFila(int $index)
    {
        array_splice($this->filasDeuda, $index, 1);
        if (empty($this->filasDeuda)) {
            $this->agregarFila();
        }
    }

    public function importarClientes()
    {
        $this->validate(['archivoClientes' => 'required|file|mimes:xlsx,xls,csv']);

        $import = new ClientesImport(auth()->user()->empresa_id);
        Excel::import($import, $this->archivoClientes->getRealPath());

        $this->resultadoClientes = $import->resultados;
        $this->archivoClientes   = null;

        // Refrescar lista de clientes para el selector manual
        $this->clientes = Cliente::where('empresa_id', auth()->user()->empresa_id)
            ->where('estado', 'activo')
            ->orderBy('apellido')->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido', 'cedula'])
            ->map(fn($c) => ['id' => $c->id, 'label' => $c->apellido . ' ' . $c->nombre . ' (' . $c->cedula . ')'])
            ->toArray();
    }

    public function importarDeudas()
    {
        $this->validate(['archivoDeudas' => 'required|file|mimes:xlsx,xls,csv']);

        $import = new DeudasImport(auth()->user()->empresa_id);
        Excel::import($import, $this->archivoDeudas->getRealPath());

        $this->resultadoDeudas = $import->resultados;
        $this->archivoDeudas   = null;
    }

    public function guardarDeudasManuales()
    {
        $empresaId = auth()->user()->empresa_id;
        $creados   = 0;
        $errores   = [];

        foreach ($this->filasDeuda as $i => $fila) {
            $fila_num = $i + 1;

            if (empty($fila['cliente_id']) || empty($fila['monto'])) continue;

            $monto = (float) str_replace(['.', ','], ['', '.'], $fila['monto']);
            if ($monto <= 0) {
                $errores[] = "Fila {$fila_num}: monto inválido.";
                continue;
            }

            $cliente = Cliente::where('empresa_id', $empresaId)->find($fila['cliente_id']);
            if (!$cliente) {
                $errores[] = "Fila {$fila_num}: cliente no encontrado.";
                continue;
            }

            try {
                $mes  = (int) $fila['mes'];
                $anio = (int) $fila['anio'];

                if ($mes >= 1 && $mes <= 12 && $anio >= 2000) {
                    $periodo = PeriodoFacturacion::where('empresa_id', $empresaId)
                        ->where('mes', $mes)
                        ->where('año', $anio)
                        ->first();

                    if (!$periodo) {
                        $periodo = PeriodoFacturacion::create([
                            'empresa_id'        => $empresaId,
                            'mes'               => $mes,
                            'año'               => $anio,
                            'nombre'            => Carbon::createFromDate($anio, $mes, 1)->locale('es')->isoFormat('MMMM YYYY'),
                            'fecha_inicio'      => Carbon::createFromDate($anio, $mes, 1)->startOfMonth(),
                            'fecha_fin'         => Carbon::createFromDate($anio, $mes, 1)->endOfMonth(),
                            'fecha_vencimiento' => Carbon::createFromDate($anio, $mes, 15)->addMonth(),
                            'estado'            => 'cerrado',
                        ]);
                    }
                } else {
                    $periodo = PeriodoFacturacion::where('empresa_id', $empresaId)
                        ->where('estado', 'activo')
                        ->first()
                        ?? PeriodoFacturacion::where('empresa_id', $empresaId)->latest()->first();
                }

                $fechaVenc = null;
                if (!empty($fila['fecha_vencimiento'])) {
                    try {
                        $fechaVenc = Carbon::createFromFormat('d/m/Y', $fila['fecha_vencimiento']);
                    } catch (\Exception) {
                        $fechaVenc = Carbon::parse($fila['fecha_vencimiento']);
                    }
                } elseif ($mes && $anio) {
                    $fechaVenc = Carbon::createFromDate($anio, $mes, 15)->addMonth();
                } else {
                    $fechaVenc = now()->subMonth();
                }

                $repo = new FacturaRepository(new Factura());
                $numeroInfo = $repo->siguienteNumeroFactura();

                Factura::create([
                    'empresa_id'        => $empresaId,
                    'cliente_id'        => $cliente->id,
                    'periodo_id'        => $periodo?->id,
                    'numero_factura'    => $numeroInfo['numero_factura'],
                    'serie'             => $numeroInfo['serie'],
                    'numero'            => $numeroInfo['numero'],
                    'fecha_emision'     => $fechaVenc->copy()->subMonth(),
                    'fecha_vencimiento' => $fechaVenc,
                    'subtotal'          => $monto,
                    'descuento'         => 0,
                    'mora'              => 0,
                    'total'             => $monto,
                    'saldo_pendiente'   => $monto,
                    'monto_pagado'      => 0,
                    'estado'            => 'vencido',
                    'observaciones'     => $fila['observaciones'] ?: 'Deuda retroactiva',
                    'datos_cliente'     => [
                        'nombre'    => $cliente->nombre . ' ' . $cliente->apellido,
                        'cedula'    => $cliente->cedula,
                        'direccion' => $cliente->direccion,
                    ],
                ]);

                $creados++;
            } catch (\Exception $e) {
                $errores[] = "Fila {$fila_num}: " . $e->getMessage();
            }
        }

        $this->resultadoManual = ['creados' => $creados, 'errores' => $errores];

        if ($creados > 0) {
            $this->filasDeuda = [];
            $this->agregarFila();
        }
    }

    public function descargarPlantillaClientes()
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
            fputcsv($handle, ['nombre', 'apellido', 'cedula', 'telefono', 'email', 'direccion', 'barrio', 'tarifa']);
            fputcsv($handle, ['Juan', 'Pérez', '1234567', '0981111222', 'juan@email.com', 'Calle 123', 'Centro', 'Residencial']);
            fputcsv($handle, ['María', 'González', '7654321', '0982333444', '', 'Av. Principal 456', 'Norte', 'Comercial']);
            fclose($handle);
        }, 'plantilla_clientes.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function descargarPlantillaDeudas()
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['cedula_cliente', 'mes', 'año', 'monto', 'fecha_vencimiento', 'observaciones']);
            fputcsv($handle, ['1234567', '1', '2025', '25000', '15/02/2025', 'Deuda enero 2025']);
            fputcsv($handle, ['1234567', '2', '2025', '25000', '15/03/2025', 'Deuda febrero 2025']);
            fputcsv($handle, ['7654321', '3', '2025', '30000', '15/04/2025', '']);
            fclose($handle);
        }, 'plantilla_deudas.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function render()
    {
        return view('livewire.importacion.importar-datos')
            ->layout('layouts.app');
    }
}
