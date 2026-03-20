@php
use App\Helpers\NumeroEnLetras;
use Illuminate\Support\Facades\Storage;

$empresa     = $recibo->datos_empresa ?? [];
$monto       = floatval($recibo->pago->monto_pagado ?? $recibo->monto_pagado);
$montoLetras = NumeroEnLetras::convertir((int) $monto);
$fecha       = $recibo->fecha_pago instanceof \Carbon\Carbon
               ? $recibo->fecha_pago
               : \Carbon\Carbon::parse($recibo->fecha_pago);
$dia         = $fecha->day;
$mes         = ucfirst($fecha->locale('es')->monthName);
$anio        = $fecha->year;
$ciudad      = $empresa['ciudad'] ?? '';
$logoPath    = $empresa['logo'] ?? null;
if (!$logoPath && $recibo->pago && $recibo->pago->empresa_id) {
    $emp = \App\Models\Empresa::find($recibo->pago->empresa_id);
    $logoPath = $emp->logo ?? null;
} elseif (!$logoPath) {
    $logoPath = \App\Models\Empresa::first()->logo ?? null;
}
$logoUrl = $logoPath ? Storage::url($logoPath) : null;
$concepto    = $recibo->periodo_pagado ?? 'Servicio de agua';
$nombreEmpresa = strtoupper($empresa['nombre'] ?? '');
$ciudadEmpresa = strtoupper($ciudad . ((!empty($empresa['pais']) ? ' - ' . $empresa['pais'] : ' - PARAGUAY')));
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Recibo Nº {{ $recibo->numero_recibo }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
@page { size: A4 portrait; margin: 0; }

body {
    font-family: Arial, sans-serif;
    font-size: 11px;
    background: #fff;
    color: #000;
}

/* === PÁGINA: 210mm x 297mm === */
.pagina {
    width: 210mm;
    height: 297mm;
    margin: 0 auto;
    padding: 6mm 8mm;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}

/* === CADA COPIA: exactamente la mitad === */
.recibo {
    width: 100%;
    height: 136mm;           /* (297mm - 12mm padding - 5mm corte) / 2 */
    border: 1.5px solid #000;
    padding: 4mm 5mm;
    display: flex;
    flex-direction: column;
    gap: 2mm;
    overflow: hidden;
}

/* === HEADER === */
.header {
    display: flex;
    justify-content: space-between;
    align-items: stretch;
    gap: 3mm;
    border-bottom: 1.5px solid #000;
    padding-bottom: 3mm;
    margin-bottom: 2mm;
}

/* Caja izquierda con logo + empresa (tiene borde) */
.header-left {
    border: 1.5px solid #000;
    display: flex;
    align-items: center;
    gap: 3mm;
    padding: 2mm 3mm;
    flex: 1;
}

.header-logo {
    width: 22mm;
    height: 18mm;
    object-fit: contain;
    flex-shrink: 0;
}

.header-logo-placeholder {
    width: 22mm;
    height: 18mm;
    border: 1px solid #ccc;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 8px;
    color: #999;
    flex-shrink: 0;
}

.header-empresa-info {
    text-align: center;
    flex: 1;
}

.empresa-nombre {
    font-weight: bold;
    font-size: 12px;
    line-height: 1.3;
}

.empresa-sub {
    font-size: 10px;
    margin-top: 1mm;
}

/* Caja derecha: RECIBO DE DINERO + monto + número */
.header-right {
    text-align: right;
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-width: 55mm;
}

.titulo-recibo {
    font-weight: bold;
    font-size: 14px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.monto-box {
    border: 2px solid #000;
    padding: 1mm 3mm;
    font-weight: bold;
    font-size: 14px;
    display: inline-block;
    min-width: 50mm;
    text-align: center;
    margin-top: 2mm;
}

.numero-recibo {
    color: red;
    font-weight: bold;
    font-size: 16px;
    margin-top: 1.5mm;
    font-family: 'Courier New', monospace;
}

/* === CAMPOS === */
.campo {
    display: flex;
    align-items: baseline;
    gap: 2mm;
    line-height: 1.8;
}

.campo-label {
    white-space: nowrap;
    flex-shrink: 0;
}

.campo-linea {
    flex: 1;
    border-bottom: 1px dotted #000;
    min-width: 15mm;
    padding-bottom: 1px;
}

.campo-linea-valor {
    font-style: italic;
}

/* Caja para cantidad en letras */
.cantidad-box {
    flex: 1;
    border: 1px solid #000;
    padding: 1mm 2mm;
    min-height: 6mm;
    font-weight: bold;
    text-transform: uppercase;
}

.cantidad-box-vacia {
    display: block;
    width: 100%;
    border: 1px solid #000;
    min-height: 6mm;
    margin-top: 1mm;
}

/* Línea punteada de concepto adicional */
.linea-dotted {
    border-bottom: 1px dotted #000;
    min-height: 5mm;
    margin-top: 1mm;
}

/* === FOOTER === */
.footer-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-top: auto;
    padding-top: 1mm;
}

.entrega-saldo {
    display: flex;
    flex-direction: column;
    gap: 2mm;
}

.caja-gs {
    display: flex;
    align-items: center;
    gap: 2mm;
}

.caja-gs-box {
    border: 1px solid #000;
    width: 35mm;
    height: 6.5mm;
}

.firma-area {
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.firma-linea {
    border-bottom: 1px dotted #000;
    width: 55mm;
    height: 10mm;
}

.firma-label {
    font-weight: bold;
    font-size: 11px;
    margin-top: 1mm;
}

.copias-info {
    font-size: 9px;
    text-align: right;
    margin-top: 1.5mm;
    line-height: 1.6;
    align-self: flex-end;
}

/* === CORTE === */
.corte {
    height: 5mm;
    display: flex;
    align-items: center;
    gap: 0;
    position: relative;
    margin: 0;
}

.corte::before {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    top: 50%;
    border-top: 1px dashed #888;
}

.corte-texto {
    background: #fff;
    padding: 0 3mm;
    font-size: 8px;
    color: #888;
    position: relative;
    z-index: 1;
    margin: 0 auto;
}

@media print {
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .no-print { display: none !important; }
}
</style>
</head>
<body>

<div class="pagina">

@for($copia = 1; $copia <= 2; $copia++)

    <div class="recibo">
        {{-- HEADER --}}
        <div class="header">
            {{-- Izquierda: Logo + Empresa (con borde) --}}
            <div class="header-left">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo" class="header-logo">
                @else
                    <div class="header-logo-placeholder">LOGO</div>
                @endif
                <div class="header-empresa-info">
                    <div class="empresa-nombre">{{ $nombreEmpresa }}</div>
                    <div class="empresa-sub">{{ $ciudadEmpresa }}</div>
                    @if(!empty($empresa['telefono']))
                    <div class="empresa-sub">Tel: {{ $empresa['telefono'] }}</div>
                    @endif
                </div>
            </div>

            {{-- Derecha: Título + Monto + Número --}}
            <div class="header-right">
                <div class="titulo-recibo">RECIBO DE DINERO</div>
                <div><span class="monto-box">Gs. {{ number_format($monto, 0, ',', '.') }}</span></div>
                <div class="numero-recibo">{{ $recibo->numero_recibo }}</div>
            </div>
        </div>

        {{-- FECHA --}}
        <div class="campo" style="justify-content:flex-end;">
            <span>{{ $ciudad }},&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;de</span>
            <span class="campo-linea campo-linea-valor" style="max-width:35mm;text-align:center;">{{ $mes }}</span>
            <span>de 20</span>
            <span class="campo-linea campo-linea-valor" style="max-width:10mm;text-align:center;">{{ substr($anio,2) }}</span>
        </div>

        {{-- CLIENTE --}}
        <div class="campo">
            <span class="campo-label">Recibi(mos) de</span>
            <span class="campo-linea campo-linea-valor">{{ $recibo->cliente_nombre }}</span>
        </div>

        {{-- RUC --}}
        <div class="campo">
            <span class="campo-label">R.U.C.:</span>
            <span class="campo-linea campo-linea-valor" style="max-width:70mm;">{{ $recibo->cliente_cedula }}</span>
        </div>

        {{-- CANTIDAD EN LETRAS --}}
        <div class="campo">
            <span class="campo-label">La cantidad de Guaraníes</span>
            <span class="cantidad-box">{{ $montoLetras }}</span>
        </div>
        <div class="cantidad-box-vacia">&nbsp;</div>

        {{-- CONCEPTO --}}
        <div class="campo">
            <span class="campo-label">en concepto de</span>
            <span class="campo-linea campo-linea-valor">{{ $concepto }}</span>
        </div>
        <div class="linea-dotted"></div>

        {{-- DEUDAS PENDIENTES --}}
        @if(isset($otrasFacturas) && $otrasFacturas->count() > 0)
        <div style="border:1px solid #c00;padding:2mm 3mm;margin-top:2mm;font-size:10px;">
            <div style="font-weight:bold;color:#c00;margin-bottom:1mm;">⚠ Facturas pendientes:</div>
            @foreach($otrasFacturas as $otra)
            <div style="display:flex;justify-content:space-between;">
                <span>#{{ $otra->numero_factura }} — Vence: {{ \Carbon\Carbon::parse($otra->fecha_vencimiento)->format('d/m/Y') }}</span>
                <span style="font-weight:bold;">Gs. {{ number_format($otra->saldo_pendiente, 0, ',', '.') }}</span>
            </div>
            @endforeach
            <div style="display:flex;justify-content:space-between;font-weight:bold;border-top:1px solid #c00;margin-top:1mm;padding-top:1mm;color:#c00;">
                <span>DEUDA TOTAL:</span>
                <span>Gs. {{ number_format($deudaTotal, 0, ',', '.') }}</span>
            </div>
        </div>
        @endif

        {{-- FOOTER --}}
        <div class="footer-row">
            <div class="entrega-saldo">
                <div class="caja-gs">
                    <span>Entrega Gs.</span>
                    <div class="caja-gs-box"></div>
                </div>
                <div class="caja-gs">
                    <span>Saldo &nbsp;&nbsp;&nbsp;Gs.</span>
                    <div class="caja-gs-box"></div>
                </div>
            </div>
            <div class="firma-area">
                <div class="firma-linea"></div>
                <div class="firma-label">FIRMA</div>
                <div class="copias-info">
                    Original: Cliente<br>
                    Duplicado: Arch. Tributario
                </div>
            </div>
        </div>
    </div>

    @if($copia === 1)
    {{-- LÍNEA DE CORTE --}}
    <div class="corte">
        <span class="corte-texto">✂ &nbsp; Cortar aquí</span>
    </div>
    @endif

@endfor

</div>


</body>
</html>
