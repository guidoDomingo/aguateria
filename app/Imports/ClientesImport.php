<?php

namespace App\Imports;

use App\Models\Cliente;
use App\Models\Barrio;
use App\Models\Tarifa;
use App\Models\Cobrador;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClientesImport implements ToCollection, WithHeadingRow
{
    private int $empresaId;
    public array $resultados = ['creados' => 0, 'omitidos' => 0, 'errores' => []];

    public function __construct(int $empresaId)
    {
        $this->empresaId = $empresaId;
    }

    public function collection(Collection $rows)
    {
        $barrios   = Barrio::where('empresa_id', $this->empresaId)->pluck('id', 'nombre');
        $tarifas   = Tarifa::where('empresa_id', $this->empresaId)->pluck('id', 'nombre');
        $cobradores = Cobrador::where('empresa_id', $this->empresaId)->pluck('id', 'nombre');

        // Ciudad por defecto para barrios nuevos (ciudades es global, sin empresa_id)
        $ciudadDefault = \App\Models\Ciudad::value('id');

        foreach ($rows as $index => $row) {
            $fila = $index + 2; // fila real en Excel (con encabezado en fila 1)

            $nombre   = trim($row['nombre'] ?? '');
            $apellido = trim($row['apellido'] ?? '');
            $cedula   = trim($row['cedula'] ?? '');

            if (empty($nombre) && empty($cedula)) continue;

            if (empty($nombre)) {
                $this->resultados['errores'][] = "Fila {$fila}: nombre vacío.";
                continue;
            }

            // Si ya existe por cédula en la empresa, omitir
            if ($cedula && Cliente::where('empresa_id', $this->empresaId)->where('cedula', $cedula)->exists()) {
                $this->resultados['omitidos']++;
                continue;
            }

            try {
                // Resolver barrio
                $barrioNombre = trim($row['barrio'] ?? '');
                $barrioId = null;
                if ($barrioNombre) {
                    // Crear barrio si no existe
                    $barrio = Barrio::firstOrCreate(
                        ['nombre' => $barrioNombre, 'empresa_id' => $this->empresaId],
                        ['empresa_id' => $this->empresaId, 'ciudad_id' => $ciudadDefault, 'activo' => true]
                    );
                    $barrioId = $barrio->id;
                }

                // Resolver tarifa por nombre o monto_mensual
                $tarifaRef = trim($row['tarifa'] ?? '');
                $tarifaId = null;
                if ($tarifaRef) {
                    $tarifa = Tarifa::where('empresa_id', $this->empresaId)
                        ->where(function($q) use ($tarifaRef) {
                            $q->where('nombre', $tarifaRef)
                              ->orWhere('monto_mensual', (float) str_replace(['.', ','], ['', '.'], $tarifaRef));
                        })->first();
                    $tarifaId = $tarifa?->id;
                }

                // Generar código cliente
                $ultimo = Cliente::where('empresa_id', $this->empresaId)->max('id') ?? 0;
                $codigo = 'CLI' . str_pad($ultimo + 1, 6, '0', STR_PAD_LEFT);

                Cliente::create([
                    'empresa_id'        => $this->empresaId,
                    'nombre'            => $nombre,
                    'apellido'          => $apellido ?: '',
                    'cedula'            => $cedula ?: null,
                    'telefono'          => trim($row['telefono'] ?? '') ?: null,
                    'email'             => trim($row['email'] ?? '') ?: null,
                    'direccion'         => trim($row['direccion'] ?? '') ?: null,
                    'barrio_id'         => $barrioId,
                    'tarifa_id'         => $tarifaId,
                    'cobrador_id'       => null,
                    'descuento_especial'=> 0,
                    'estado'            => 'activo',
                    'codigo_cliente'    => $codigo,
                    'fecha_alta'        => now(),
                ]);

                $this->resultados['creados']++;
            } catch (\Exception $e) {
                $this->resultados['errores'][] = "Fila {$fila}: " . $e->getMessage();
            }
        }
    }
}
