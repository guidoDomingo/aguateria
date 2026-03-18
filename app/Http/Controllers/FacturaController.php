<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\ConfiguracionRecibo;

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
}
