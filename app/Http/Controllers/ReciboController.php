<?php

namespace App\Http\Controllers;

use App\Models\Recibo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReciboController extends Controller
{
    /**
     * Generar PDF del recibo
     */
    public function generarPdf($reciboId)
    {
        $recibo = Recibo::with(['pago'])->findOrFail($reciboId);
        
        // Verificar que el usuario tenga acceso al recibo
        if ($recibo->pago->empresa_id !== auth()->user()->empresa_id) {
            abort(403, 'No tiene permisos para acceder a este recibo');
        }
        
        // Para simplicidad, vamos a generar un PDF básico con HTML
        // En un futuro se puede implementar con DomPDF o similar
        
        $html = view('recibos.pdf', compact('recibo'))->render();
        
        // Por ahora devolver HTML como respuesta para testing
        // Luego se puede convertir a PDF real
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'inline; filename="recibo-' . $recibo->numero_recibo . '.html"');
    }
    
    /**
     * Imprimir recibo (versión para impresión)
     */
    public function imprimir($reciboId)
    {
        $recibo = Recibo::with(['pago'])->findOrFail($reciboId);
        
        // Verificar que el usuario tenga acceso al recibo
        if ($recibo->pago->empresa_id !== auth()->user()->empresa_id) {
            abort(403, 'No tiene permisos para acceder a este recibo');
        }
        
        return view('recibos.imprimir', compact('recibo'));
    }
}