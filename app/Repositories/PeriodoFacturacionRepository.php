<?php

namespace App\Repositories;

use App\Models\PeriodoFacturacion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PeriodoFacturacionRepository extends BaseRepository
{
    protected function getModel(): Model
    {
        return new PeriodoFacturacion();
    }

    /**
     * Obtener el período activo (abierto o cerrado) para facturación
     */
    public function getPeriodoActivo(int $empresaId): ?PeriodoFacturacion
    {
        return $this->model
            ->where('empresa_id', $empresaId)
            ->where('estado', 'abierto')
            ->orderBy('año', 'desc')
            ->orderBy('mes', 'desc')
            ->first();
    }

    /**
     * Crear período para facturación mensual
     */
    public function crearPeriodoMensual(int $empresaId, int $año, int $mes): PeriodoFacturacion
    {
        $fecha_inicio = Carbon::createFromDate($año, $mes, 1);
        $fecha_fin = $fecha_inicio->copy()->endOfMonth();
        $fecha_vencimiento = $fecha_inicio->copy()->addMonth()->day(15); // Valor por defecto para referencia
        
        $nombre_mes = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        
        return $this->model->create([
            'empresa_id' => $empresaId,
            'año' => $año,
            'mes' => $mes,
            'nombre' => $nombre_mes[$mes] . ' ' . $año,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'fecha_vencimiento' => $fecha_vencimiento,
            'estado' => 'abierto'
        ]);
    }

    /**
     * Verificar si existe período para el mes/año
     */
    public function existePeriodo(int $empresaId, int $año, int $mes): bool
    {
        return $this->model
            ->where('empresa_id', $empresaId)
            ->where('año', $año)
            ->where('mes', $mes)
            ->exists();
    }

    /**
     * Obtener período por fecha específica
     */
    public function obtenerPorFecha(int $empresaId, int $año, int $mes): ?PeriodoFacturacion
    {
        return $this->model
            ->where('empresa_id', $empresaId)
            ->where('año', $año)
            ->where('mes', $mes)
            ->first();
    }

    /**
     * Obtener períodos por año
     */
    public function getPeriodosPorAño(int $empresaId, int $año): Collection
    {
        return $this->model
            ->where('empresa_id', $empresaId)
            ->where('año', $año)
            ->orderBy('mes')
            ->get();
    }

    /**
     * Obtener períodos pendientes de facturar
     */
    public function getPeriodosPendientes(int $empresaId): Collection
    {
        return $this->model
            ->where('empresa_id', $empresaId)
            ->whereIn('estado', ['abierto', 'cerrado'])
            ->orderBy('año')
            ->orderBy('mes')
            ->get();
    }

    /**
     * Cerrar período
     */
    public function cerrarPeriodo(int $periodoId): bool
    {
        $periodo = $this->find($periodoId);
        if ($periodo && $periodo->estado === 'abierto') {
            return $periodo->update(['estado' => 'cerrado']);
        }
        
        return false;
    }

    /**
     * Marcar período como facturado
     */
    public function marcarFacturado(int $periodoId, int $totalFacturas, float $montoTotal): bool
    {
        return $this->model
            ->where('id', $periodoId)
            ->update([
                'estado' => 'facturado',
                'fecha_facturacion' => now(),
                'total_facturas' => $totalFacturas,
                'monto_total' => $montoTotal
            ]);
    }

    /**
     * Estadísticas de períodos
     */
    public function getEstadisticas(int $empresaId): array
    {
        $query = $this->model->where('empresa_id', $empresaId);
        
        return [
            'total_periodos' => $query->count(),
            'periodos_abiertos' => $query->where('estado', 'abierto')->count(),
            'periodos_cerrados' => $query->where('estado', 'cerrado')->count(),
            'periodos_facturados' => $query->where('estado', 'facturado')->count(),
            'monto_total_facturado' => $query->where('estado', 'facturado')->sum('monto_total'),
            'promedio_facturas_periodo' => $query->where('estado', 'facturado')->avg('total_facturas')
        ];
    }

    /**
     * Obtener períodos con estadísticas de facturación
     */
    public function getPeriodosConEstadisticas(int $empresaId): Collection
    {
        return $this->model
            ->where('empresa_id', $empresaId)
            ->withCount('facturas')
            ->with('facturas:periodo_id,monto_total')
            ->orderBy('año', 'desc')
            ->orderBy('mes', 'desc')
            ->get();
    }

    /**
     * Obtener próximo período a crear
     */
    public function getProximoPeriodo(int $empresaId): array
    {
        $ultimo = $this->model
            ->where('empresa_id', $empresaId)
            ->orderBy('año', 'desc')
            ->orderBy('mes', 'desc')
            ->first();
            
        if (!$ultimo) {
            return ['año' => date('Y'), 'mes' => date('n')];
        }
        
        $proximoMes = $ultimo->mes + 1;
        $proximoAño = $ultimo->año;
        
        if ($proximoMes > 12) {
            $proximoMes = 1;
            $proximoAño++;
        }
        
        return ['año' => $proximoAño, 'mes' => $proximoMes];
    }
}