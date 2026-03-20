@php
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

$logoPath = $empresa->logo ?? null;
$logoUrl  = $logoPath ? Storage::url($logoPath) : null;

$nombreEmpresa = strtoupper($empresa->nombre ?? '');
$ciudadEmpresa = strtoupper(($empresa->ciudad ?? '') . (!empty($empresa->pais) ? ' - ' . $empresa->pais : ' - PARAGUAY'));

$hoy = Carbon::now();
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Aviso de Cobro — {{ $cliente->nombre_completo }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
@page { size: A4 portrait; margin: 0; }

body {
    font-family: Arial, sans-serif;
    font-size: 11px;
    background: #fff;
    color: #000;
}

.pagina {
    width: 210mm;
    height: 297mm;
    margin: 0 auto;
    padding: 6mm 8mm;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}

/* Cada copia ocupa la mitad */
.boleta {
    width: 100%;
    height: 136mm;
    border: 1.5px solid #000;
    padding: 4mm 5mm;
    display: flex;
    flex-direction: column;
    gap: 2mm;
    overflow: hidden;
}

/* HEADER */
.header {
    display: flex;
    justify-content: space-between;
    align-items: stretch;
    gap: 3mm;
    border-bottom: 1.5px solid #000;
    padding-bottom: 3mm;
    margin-bottom: 1mm;
}

.header-left {
    border: 1.5px solid #000;
    display: flex;
    align-items: center;
    gap: 3mm;
    padding: 2mm 3mm;
    flex: 1;
}

.header-logo {
    width: 20mm;
    height: 16mm;
    object-fit: contain;
    flex-shrink: 0;
}

.header-logo-placeholder {
    width: 20mm;
    height: 16mm;
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

.header-right {
    text-align: right;
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-width: 55mm;
}

.titulo-boleta {
    font-weight: bold;
    font-size: 13px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #1a56db;
}

.subtitulo-boleta {
    font-size: 10px;
    color: #555;
    margin-top: 1mm;
}

.numero-boleta {
    font-weight: bold;
    font-size: 14px;
    margin-top: 1.5mm;
    font-family: 'Courier New', monospace;
    color: #1a56db;
}

/* CLIENTE */
.cliente-box {
    border: 1px solid #ccc;
    padding: 2mm 3mm;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1mm 4mm;
    font-size: 10.5px;
}

.cliente-box .label {
    color: #666;
    font-size: 9px;
    text-transform: uppercase;
}

.cliente-box .valor {
    font-weight: bold;
}

/* TABLA */
.tabla-titulo {
    font-weight: bold;
    font-size: 10px;
    text-transform: uppercase;
    color: #1a56db;
    letter-spacing: 0.3px;
    margin-top: 1mm;
}

table.facturas {
    width: 100%;
    border-collapse: collapse;
    font-size: 10px;
}

table.facturas th {
    background: #1a56db;
    color: #fff;
    padding: 2mm 2mm;
    text-align: left;
    font-size: 9px;
    text-transform: uppercase;
}

table.facturas th.r,
table.facturas td.r { text-align: right; }

table.facturas td {
    padding: 1.5mm 2mm;
    border-bottom: 1px solid #eee;
}

table.facturas tr.vencida td {
    color: #c00;
}

table.facturas tr.total-row td {
    font-weight: bold;
    background: #f0f4ff;
    border-top: 1.5px solid #1a56db;
    color: #1a56db;
}

/* FOOTER */
.footer-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-top: auto;
    padding-top: 1mm;
    gap: 3mm;
}

.cobro-boxes {
    display: flex;
    flex-direction: column;
    gap: 2mm;
}

.caja-cobro {
    display: flex;
    align-items: center;
    gap: 2mm;
    font-size: 10.5px;
}

.caja-cobro-box {
    border: 1px solid #000;
    width: 38mm;
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
    font-size: 10px;
    margin-top: 1mm;
}

.copias-info {
    font-size: 9px;
    text-align: right;
    margin-top: 1mm;
}

/* CORTE */
.corte {
    height: 5mm;
    display: flex;
    align-items: center;
    position: relative;
    margin: 0;
}

.corte::before {
    content: '';
    position: absolute;
    left: 0; right: 0; top: 50%;
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

<div class="boleta">

    {{-- HEADER --}}
    <div class="header">
        <div class="header-left">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo" class="header-logo">
            @else
                <div class="header-logo-placeholder">LOGO</div>
            @endif
            <div class="header-empresa-info">
                <div class="empresa-nombre">{{ $nombreEmpresa }}</div>
                <div class="empresa-sub">{{ $ciudadEmpresa }}</div>
                @if($empresa->telefono)
                <div class="empresa-sub">Tel: {{ $empresa->telefono }}</div>
                @endif
            </div>
        </div>

        <div class="header-right">
            <div class="titulo-boleta">Aviso de Cobro</div>
            <div class="subtitulo-boleta">Fecha: {{ $hoy->format('d/m/Y') }}</div>
            <div class="numero-boleta">Cliente N° {{ str_pad($cliente->id, 6, '0', STR_PAD_LEFT) }}</div>
        </div>
    </div>

    {{-- DATOS DEL CLIENTE --}}
    <div class="cliente-box">
        <div>
            <div class="label">Cliente</div>
            <div class="valor">{{ $cliente->nombre_completo }}</div>
        </div>
        <div>
            <div class="label">C.I. / R.U.C.</div>
            <div class="valor">{{ $cliente->cedula ?? '-' }}</div>
        </div>
        @if($cliente->direccion)
        <div>
            <div class="label">Dirección</div>
            <div>{{ $cliente->direccion }}</div>
        </div>
        @endif
        @if($cliente->barrio)
        <div>
            <div class="label">Barrio</div>
            <div>{{ $cliente->barrio->nombre }}</div>
        </div>
        @endif
    </div>

    {{-- TABLA DE FACTURAS --}}
    <div class="tabla-titulo">Detalle de facturas pendientes</div>
    <table class="facturas">
        <thead>
            <tr>
                <th>N° Factura</th>
                <th>Período</th>
                <th>Vencimiento</th>
                <th>Estado</th>
                <th class="r">Monto (Gs.)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($facturasCliente as $f)
            @php
                $esVencida = $f->fecha_vencimiento->isPast() && $f->estado !== 'pagado';
                $periodo = $f->periodo
                    ? Carbon::createFromDate($f->periodo->año, $f->periodo->mes, 1)->locale('es')->isoFormat('MMM YYYY')
                    : '-';
                $estadoLabel = match($f->estado) {
                    'vencido'   => 'Vencida',
                    'pendiente' => 'Pendiente',
                    'parcial'   => 'Parcial',
                    default     => ucfirst($f->estado),
                };
            @endphp
            <tr class="{{ $esVencida ? 'vencida' : '' }}">
                <td>#{{ $f->numero_factura }}</td>
                <td>{{ $periodo }}</td>
                <td>{{ $f->fecha_vencimiento->format('d/m/Y') }}{{ $esVencida ? ' ⚠' : '' }}</td>
                <td>{{ $estadoLabel }}</td>
                <td class="r">{{ number_format($f->saldo_pendiente, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" style="text-align:right;">TOTAL A COBRAR:</td>
                <td class="r">{{ number_format($totalDeuda, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- FOOTER --}}
    <div class="footer-row">
        <div class="cobro-boxes">
            <div class="caja-cobro">
                <span>Monto cobrado Gs.</span>
                <div class="caja-cobro-box"></div>
            </div>
            <div class="caja-cobro">
                <span>Saldo&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Gs.</span>
                <div class="caja-cobro-box"></div>
            </div>
        </div>
        <div class="firma-area">
            <div class="firma-linea"></div>
            <div class="firma-label">FIRMA COBRADOR</div>
            <div class="copias-info">
                Original: Cliente&nbsp;&nbsp;&nbsp;Duplicado: Empresa
            </div>
        </div>
    </div>

</div>

@if($copia === 1)
<div class="corte">
    <span class="corte-texto">✂ &nbsp; Cortar aquí</span>
</div>
@endif

@endfor

</div>

@if($autoImprimir ?? false)
<script>window.onload = function(){ setTimeout(function(){ window.print(); }, 300); };</script>
@endif

</body>
</html>
