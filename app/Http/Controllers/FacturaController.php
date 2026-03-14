<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FacturaController extends Controller
{
    /**
     * Ver detalle de factura
     */
    public function ver($facturaId)
    {
        $factura = Factura::with(['cliente', 'periodo', 'detalles'])->findOrFail($facturaId);
        
        // Verificar que el usuario tenga acceso a la factura
        if ($factura->empresa_id !== auth()->user()->empresa_id) {
            abort(403, 'No tiene permisos para acceder a esta factura');
        }
        
        return view('facturas.ver', compact('factura'));
    }
    
    /**
     * Generar PDF de la factura
     */
    public function generarPdf($facturaId)
    {
        $factura = Factura::with(['cliente', 'periodo', 'detalles', 'empresa'])->findOrFail($facturaId);
        
        // Verificar que el usuario tenga acceso a la factura
        if ($factura->empresa_id !== auth()->user()->empresa_id) {
            abort(403, 'No tiene permisos para acceder a esta factura');
        }
        
        $html = view('facturas.pdf', compact('factura'))->render();
        
        // Por ahora devolver HTML como respuesta para testing
        // Luego se puede convertir a PDF real con DomPDF
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'inline; filename="factura-' . $factura->numero_factura . '.html"');
    }
    
    /**
     * Imprimir factura (versión para impresión)
     */
    public function imprimir($facturaId)
    {
        $factura = Factura::with(['cliente', 'periodo', 'detalles', 'empresa'])->findOrFail($facturaId);
        
        // Verificar que el usuario tenga acceso a la factura
        if ($factura->empresa_id !== auth()->user()->empresa_id) {
            abort(403, 'No tiene permisos para acceder a esta factura');
        }
        
        return view('facturas.imprimir', compact('factura'));
    }
}