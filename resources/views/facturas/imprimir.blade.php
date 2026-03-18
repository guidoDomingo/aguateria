<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura {{ $factura->numero_factura }}</title>
    <style>
        @php $color = $configuracion->colores['header'] ?? '#2563eb'; @endphp
        body { font-family: {{ $configuracion->fuente ?? 'Arial' }}, sans-serif; margin:0; padding:12px; background:white; font-size:12px; line-height:1.3; color:#222; }
        .header { text-align:center; border-bottom:2px solid {{ $color }}; padding-bottom:12px; margin-bottom:12px; }
        .empresa-nombre { font-size:16px; font-weight:bold; color:{{ $color }}; }
        .empresa-info { font-size:10px; color:#666; margin-top:3px; }
        .factura-num { font-size:14px; font-weight:bold; margin:8px 0; border:2px solid {{ $color }}; display:inline-block; padding:4px 12px; color:{{ $color }}; }
        .aviso { display:inline-block; padding:2px 8px; border-radius:3px; font-size:10px; font-weight:bold; margin-top:4px; }
        .aviso-desc { background:#fee2e2; color:#991b1b; }
        .aviso-ult { background:#fef9c3; color:#92400e; }
        .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:15px; margin:12px 0; font-size:11px; }
        .info-seccion h4 { font-size:10px; font-weight:bold; color:{{ $color }}; text-transform:uppercase; border-bottom:1px solid #eee; padding-bottom:2px; margin:0 0 6px; }
        .info-seccion p { margin:2px 0; }
        .label { color:#888; }
        .total-box { border:2px solid {{ $color }}; padding:8px 12px; margin:10px 0; text-align:center; }
        .total-box .monto { font-size:18px; font-weight:bold; color:{{ $color }}; }
        .total-box .label-total { font-size:10px; color:#666; }
        .tabla { width:100%; border-collapse:collapse; font-size:10px; margin:8px 0; }
        .tabla th { background:#f3f4f6; padding:4px 6px; text-align:left; border-bottom:2px solid #ddd; }
        .tabla th.r, .tabla td.r { text-align:right; }
        .tabla td { padding:4px 6px; border-bottom:1px solid #f0f0f0; }
        .otras-box { background:#fff7f7; border:1px solid #fca5a5; padding:8px 10px; margin:10px 0; border-radius:3px; }
        .otras-box h4 { font-size:10px; font-weight:bold; color:#dc2626; text-transform:uppercase; margin:0 0 5px; }
        .deuda-total { display:flex; justify-content:space-between; font-weight:bold; font-size:12px; color:#dc2626; border-top:1px solid #fca5a5; padding-top:5px; margin-top:4px; }
        .pagos-box { background:#f0fdf4; border:1px solid #bbf7d0; padding:8px 10px; margin:10px 0; border-radius:3px; }
        .pagos-box h4 { font-size:10px; font-weight:bold; color:#166534; text-transform:uppercase; margin:0 0 5px; }
        .footer { text-align:center; font-size:9px; color:#999; margin-top:15px; padding-top:10px; border-top:1px solid #eee; }
        @media print { body{margin:0;padding:8px;} @page{margin:.5cm;} }
    </style>
    <script>
        window.onload = function() { setTimeout(function(){ window.print(); }, 400); }
    </script>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="empresa-nombre">{{ $factura->empresa->nombre }}</div>
        <div class="empresa-info">
            @if($configuracion->mostrar_direccion_empresa && $factura->empresa->direccion)
                {{ $factura->empresa->direccion }}
            @endif
            @if($configuracion->mostrar_telefono_empresa && $factura->empresa->telefono)
                &nbsp;·&nbsp; Tel: {{ $factura->empresa->telefono }}
            @endif
            @if($configuracion->mostrar_email_empresa && $factura->empresa->email)
                &nbsp;·&nbsp; {{ $factura->empresa->email }}
            @endif
        </div>
        <div class="factura-num">FACTURA N° {{ $factura->numero_factura }}</div>
        @if($factura->aviso === 'desconexion')
            <div><span class="aviso aviso-desc">⚠ AVISO DE DESCONEXIÓN</span></div>
        @elseif($factura->aviso === 'ultimo_aviso')
            <div><span class="aviso aviso-ult">🔔 ÚLTIMO AVISO</span></div>
        @endif
    </div>

    <!-- Info cliente y factura -->
    <div class="info-grid">
        <div class="info-seccion">
            <h4>Datos</h4>
            <p><span class="label">Fecha:</span> {{ $factura->fecha_emision->format('d/m/Y') }}</p>
            <p><span class="label">Vencimiento:</span>
                <strong style="{{ $factura->fecha_vencimiento->isPast() && $factura->estado !== 'pagado' ? 'color:#dc2626' : '' }}">
                    {{ $factura->fecha_vencimiento->format('d/m/Y') }}
                </strong>
            </p>
            <p><span class="label">Período:</span>
                @if($factura->periodo)
                    {{ \Carbon\Carbon::createFromDate($factura->periodo->año, $factura->periodo->mes, 1)->locale('es')->isoFormat('MMM YYYY') }}
                @else Sin período @endif
            </p>
            <p><span class="label">Estado:</span> {{ ucfirst($factura->estado) }}</p>
        </div>
        <div class="info-seccion">
            <h4>Cliente</h4>
            <p><strong>{{ $factura->cliente->nombre }} {{ $factura->cliente->apellido }}</strong></p>
            <p><span class="label">CI:</span> {{ $factura->cliente->cedula }}</p>
            @if($factura->cliente->direccion)
            <p>{{ \Str::limit($factura->cliente->direccion, 40) }}</p>
            @endif
            @if($factura->cliente->telefono)
            <p>Tel: {{ $factura->cliente->telefono }}</p>
            @endif
            @if($factura->cliente->barrio)
            <p><span class="label">Barrio:</span> {{ $factura->cliente->barrio->nombre }}</p>
            @endif
        </div>
    </div>

    <!-- Total esta factura -->
    <div class="total-box">
        <div class="label-total">TOTAL ESTA FACTURA</div>
        <div class="monto">{{ number_format($factura->total, 0, ',', '.') }} Gs.</div>
        @if($factura->mora > 0)
            <div style="font-size:10px;color:#d97706">Incluye mora: {{ number_format($factura->mora, 0, ',', '.') }} Gs.</div>
        @endif
    </div>

    <!-- Otras facturas pendientes -->
    @if($otrasFacturas->count() > 0)
    <div class="otras-box">
        <h4>⚠ Otras facturas pendientes del cliente</h4>
        <table class="tabla">
            @foreach($otrasFacturas as $otra)
            <tr>
                <td>#{{ $otra->numero_factura }}</td>
                <td style="{{ $otra->fecha_vencimiento->isPast() ? 'color:#dc2626' : '' }}">
                    Vence: {{ $otra->fecha_vencimiento->format('d/m/Y') }}
                </td>
                <td class="r">{{ number_format($otra->total, 0, ',', '.') }} Gs.</td>
            </tr>
            @endforeach
        </table>
        <div class="deuda-total">
            <span>DEUDA TOTAL DEL CLIENTE:</span>
            <span>{{ number_format($deudaTotal, 0, ',', '.') }} Gs.</span>
        </div>
    </div>
    @endif

    <!-- Historial de pagos -->
    @if($factura->pagos->count() > 0)
    <div class="pagos-box">
        <h4>Pagos Registrados</h4>
        <table class="tabla" style="font-size:10px">
            <thead>
                <tr>
                    <th>Fecha</th><th>Método</th><th class="r">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($factura->pagos as $pago)
                <tr>
                    <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                    <td>{{ $pago->metodoPago->nombre ?? $pago->metodo_pago ?? '-' }}</td>
                    <td class="r" style="color:#16a34a;font-weight:bold">{{ number_format($pago->monto_pagado, 0, ',', '.') }} Gs.</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="display:flex;justify-content:space-between;font-weight:bold;font-size:11px;padding-top:4px;border-top:1px solid #bbf7d0">
            <span>Total pagado:</span>
            <span style="color:#16a34a">{{ number_format($factura->pagos->sum('monto_pagado'), 0, ',', '.') }} Gs.</span>
        </div>
        @if($factura->saldo_pendiente > 0)
        <div style="display:flex;justify-content:space-between;font-weight:bold;font-size:11px;color:#dc2626">
            <span>Saldo pendiente:</span>
            <span>{{ number_format($factura->saldo_pendiente, 0, ',', '.') }} Gs.</span>
        </div>
        @endif
    </div>
    @endif

    <!-- Detalles de servicios -->
    @if($factura->detalles && $factura->detalles->count() > 0)
    <table class="tabla">
        <thead>
            <tr>
                <th>Concepto</th><th>Descripción</th><th class="r">Cant.</th><th class="r">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factura->detalles as $detalle)
            <tr>
                <td>{{ $detalle->concepto }}</td>
                <td style="color:#666">{{ $detalle->descripcion }}</td>
                <td class="r">{{ $detalle->cantidad }}</td>
                <td class="r">{{ number_format($detalle->subtotal, 0, ',', '.') }} Gs.</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($factura->observaciones)
    <div style="margin:10px 0;padding:6px;background:#f9f9f9;border:1px solid #eee;font-size:10px;">
        <strong>Obs:</strong> {{ $factura->observaciones }}
    </div>
    @endif

    <div class="footer">
        {{ $configuracion->mensaje_inferior ?? 'Gracias por su preferencia' }}<br>
        {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
