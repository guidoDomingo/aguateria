<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\ConfiguracionRecibo;
use Carbon\Carbon;

class FacturaController extends Controller
{
    private function cargarDatos($facturaId): array
    {
        $factura = Factura::with(['cliente', 'cliente.barrio', 'periodo', 'detalles', 'empresa', 'pagos'])
            ->findOrFail($facturaId);

        if ($factura->empresa_id !== auth()->user()->empresa_id) {
            abort(403);
        }

        $configuracion = ConfiguracionRecibo::getConfiguracionParaEmpresa($factura->empresa_id);

        // Otras facturas pendientes/vencidas del mismo cliente (deuda acumulada)
        $otrasFacturas = Factura::where('empresa_id', $factura->empresa_id)
            ->where('cliente_id', $factura->cliente_id)
            ->whereIn('estado', ['pendiente', 'vencido', 'parcial'])
            ->where('id', '!=', $factura->id)
            ->orderBy('fecha_vencimiento')
            ->get(['id', 'numero_factura', 'total', 'fecha_vencimiento', 'estado', 'periodo_id']);

        // Deuda total = otras pendientes + saldo pendiente de esta (0 si ya está pagada)
        $deudaTotal = $otrasFacturas->sum('total') + ($factura->saldo_pendiente ?? 0);

        return compact('factura', 'configuracion', 'otrasFacturas', 'deudaTotal');
    }

    public function ver($facturaId)
    {
        $datos = $this->cargarDatos($facturaId);
        return view('facturas.ver', $datos);
    }

    public function generarPdf($facturaId)
    {
        $datos = $this->cargarDatos($facturaId);
        $html  = view('facturas.pdf', $datos)->render();

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'inline; filename="factura-' . $datos['factura']->numero_factura . '.html"');
    }

    public function imprimir($facturaId)
    {
        $datos = $this->cargarDatos($facturaId);
        return view('facturas.imprimir', $datos);
    }

    public function boletaImprimir($facturaId)
    {
        return $this->generarBoleta($facturaId, true);
    }

    public function boletaPdf($facturaId)
    {
        return $this->generarBoleta($facturaId, false);
    }

    public function boletasMasivas()
    {
        $empresaId = auth()->user()->empresa_id;
        $empresa   = auth()->user()->empresa;

        // Todos los clientes con facturas pendientes
        $clienteIds = Factura::where('empresa_id', $empresaId)
            ->whereIn('estado', ['pendiente', 'vencido', 'parcial'])
            ->distinct()
            ->pluck('cliente_id');

        if ($clienteIds->isEmpty()) {
            return back()->with('error', 'No hay clientes con facturas pendientes.');
        }

        // Para cada cliente, cargar sus facturas pendientes
        $clientes = \App\Models\Cliente::with(['barrio'])
            ->whereIn('id', $clienteIds)
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();

        $datos = $clientes->map(function ($cliente) use ($empresaId) {
            $facturas = Factura::with('periodo')
                ->where('empresa_id', $empresaId)
                ->where('cliente_id', $cliente->id)
                ->whereIn('estado', ['pendiente', 'vencido', 'parcial'])
                ->orderBy('fecha_vencimiento')
                ->get(['id', 'numero_factura', 'total', 'saldo_pendiente', 'fecha_vencimiento', 'estado', 'periodo_id']);

            return [
                'cliente'         => $cliente,
                'facturasCliente' => $facturas,
                'totalDeuda'      => $facturas->sum('saldo_pendiente'),
            ];
        });

        return view('facturas.boletas-masivas', compact('empresa', 'datos'));
    }

    private function generarBoleta($facturaId, bool $esImpresion)
    {
        $factura = Factura::with(['cliente', 'cliente.barrio', 'periodo', 'empresa'])
            ->findOrFail($facturaId);

        if ($factura->empresa_id !== auth()->user()->empresa_id) {
            abort(403);
        }

        $empresa  = $factura->empresa;
        $cliente  = $factura->cliente;

        // Todas las facturas pendientes del cliente (incluye la actual)
        $facturasCliente = Factura::with('periodo')
            ->where('empresa_id', $factura->empresa_id)
            ->where('cliente_id', $factura->cliente_id)
            ->whereIn('estado', ['pendiente', 'vencido', 'parcial'])
            ->orderBy('fecha_vencimiento')
            ->get(['id', 'numero_factura', 'total', 'saldo_pendiente', 'fecha_vencimiento', 'estado', 'periodo_id']);

        $totalDeuda = $facturasCliente->sum('saldo_pendiente');

        $autoImprimir = $esImpresion;

        $html = view('facturas.boleta-cobro', compact(
            'empresa', 'cliente', 'facturasCliente', 'totalDeuda', 'autoImprimir'
        ))->render();

        if ($esImpresion) {
            return response($html)->header('Content-Type', 'text/html');
        }

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'inline; filename="boleta-' . $cliente->id . '.html"');
    }
}
