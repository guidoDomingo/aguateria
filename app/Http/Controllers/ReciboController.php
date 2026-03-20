<?php

namespace App\Http\Controllers;

use App\Models\Recibo;
use App\Models\ConfiguracionRecibo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReciboController extends Controller
{
    /**
     * Generar PDF del recibo
     */
    public function generarPdf($reciboId)
    {
        $recibo = Recibo::with(['pago', 'pago.cliente', 'pago.metodoPago', 'pago.cobrador'])->findOrFail($reciboId);
        
        // Verificar que el usuario tenga acceso al recibo
        if ($recibo->pago->empresa_id !== auth()->user()->empresa_id) {
            abort(403, 'No tiene permisos para acceder a este recibo');
        }

        // Obtener configuración personalizada
        $configuracion = ConfiguracionRecibo::getConfiguracionParaEmpresa(auth()->user()->empresa_id);
        
        // Seleccionar la plantilla según configuración
        $plantilla = $this->getPlantillaRecibo($configuracion->plantilla);
        
        $html = view($plantilla, compact('recibo', 'configuracion'))->render();
        
        // Generar respuesta según configuración de tamaño
        return $this->generarRespuestaPdf($html, $recibo, $configuracion);
    }
    
    /**
     * Imprimir recibo (versión para impresión)
     */
    public function imprimir($reciboId)
    {
        $recibo = Recibo::with(['pago', 'pago.cliente', 'pago.metodoPago', 'pago.cobrador'])->findOrFail($reciboId);
        
        // Verificar que el usuario tenga acceso al recibo
        if ($recibo->pago->empresa_id !== auth()->user()->empresa_id) {
            abort(403, 'No tiene permisos para acceder a este recibo');
        }

        // Obtener configuración personalizada
        $configuracion = ConfiguracionRecibo::getConfiguracionParaEmpresa(auth()->user()->empresa_id);
        
        // Seleccionar la plantilla según configuración
        $plantilla = $this->getPlantillaRecibo($configuracion->plantilla, true);
        
        return view($plantilla, compact('recibo', 'configuracion'));
    }

    /**
     * Obtener el nombre de la plantilla según configuración
     */
    private function getPlantillaRecibo($tipoPlantilla, $esImpresion = false)
    {
        $sufijo = $esImpresion ? '-imprimir' : '-pdf';
        
        switch ($tipoPlantilla) {
            case 'modern':
                return "recibos.modern{$sufijo}";
            case 'classic':
                return "recibos.classic{$sufijo}";
            case 'minimal':
                return "recibos.minimal{$sufijo}";
            case 'recibo_dinero':
                return "recibos.recibo-dinero{$sufijo}";
            default:
                return "recibos.standard{$sufijo}";
        }
    }

    /**
     * Generar respuesta PDF según configuración
     */
    private function generarRespuestaPdf($html, $recibo, $configuracion)
    {
        // CSS específico según tamaño de papel
        $css = $this->generarCssPersonalizado($configuracion);
        
        // Inyectar CSS personalizado en el HTML
        $html = str_replace('</head>', "<style>{$css}</style></head>", $html);
        
        $filename = 'recibo-' . $recibo->numero_recibo . '.html';
        
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    /**
     * Generar CSS personalizado según configuración
     */
    private function generarCssPersonalizado($configuracion)
    {
        $css = [];
        
        // Configurar tamaño de página
        if ($configuracion->tamaño_papel === '80mm') {
            $css[] = '@page { size: 80mm auto; margin: 0; }';
            $css[] = 'body { width: 80mm; font-size: 12px; }';
        } elseif ($configuracion->tamaño_papel === '58mm') {
            $css[] = '@page { size: 58mm auto; margin: 0; }';
            $css[] = 'body { width: 58mm; font-size: 10px; }';
        } elseif ($configuracion->tamaño_papel === 'A4') {
            $css[] = '@page { size: A4; margin: ' . $configuracion->margenes_superior . 'mm ' . $configuracion->margenes_derecho . 'mm ' . $configuracion->margenes_inferior . 'mm ' . $configuracion->margenes_izquierdo . 'mm; }';
        } elseif ($configuracion->tamaño_papel === 'personalizado') {
            $css[] = '@page { size: ' . $configuracion->ancho_personalizado . 'mm ' . $configuracion->alto_personalizado . 'mm; margin: 0; }';
        }
        
        // Configurar fuente y colores
        $colores = $configuracion->colores;
        $css[] = 'body { font-family: ' . $configuracion->fuente . ', sans-serif; font-size: ' . $configuracion->tamaño_fuente . 'px; color: ' . $colores['text'] . '; background-color: ' . $colores['background'] . '; }';
        $css[] = '.header { background-color: ' . $colores['header'] . '; color: white; }';
        $css[] = '.logo { height: ' . $configuracion->tamaño_logo . 'px; }';
        
        // Configurar alineación del logo
        $alineacion = $configuracion->posicion_logo === 'left' ? 'left' : ($configuracion->posicion_logo === 'right' ? 'right' : 'center');
        $css[] = '.logo-container { text-align: ' . $alineacion . '; }';
        
        // Impresión automática
        if ($configuracion->impresion_automatica) {
            $css[] = '@media print { body { -webkit-print-color-adjust: exact; } }';
            $css[] = 'window.onload = function() { window.print(); };';
        }
        
        return implode("\n", $css);
    }
}