<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura {{ $factura->numero_factura }}</title>
    <style>
        @php $color = $configuracion->colores['header'] ?? '#2563eb'; @endphp
        body { font-family: {{ $configuracion->fuente ?? 'Arial' }}, sans-serif; margin:0; padding:20px; background:white; font-size:{{ $configuracion->tamaño_fuente ?? 13 }}px; line-height:1.4; color:#222; }
        .factura { max-width:800px; margin:0 auto; border:1px solid #ddd; background:white; }
        .header { padding:20px; border-bottom:3px solid {{ $color }}; background:#f8f9fa; }
        .header-top { display:flex; justify-content:space-between; align-items:flex-start; }
        .empresa-nombre { font-size:20px; font-weight:bold; color:{{ $color }}; margin-bottom:4px; }
        .empresa-info { font-size:11px; color:#666; }
        .factura-num { text-align:right; }
        .factura-num .num { font-size:22px; font-weight:bold; color:{{ $color }}; }
        .factura-num .fecha { font-size:11px; color:#666; }
        .aviso-badge { display:inline-block; padding:3px 8px; border-radius:3px; font-size:10px; font-weight:bold; margin-top:4px; }
        .aviso-desconexion { background:#fee2e2; color:#991b1b; }
        .aviso-ultimo { background:#fef9c3; color:#92400e; }
        .contenido { padding:20px; }
        .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:30px; margin-bottom:25px; }
        .info-seccion h3 { font-size:10px; font-weight:bold; color:{{ $color }}; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #eee; padding-bottom:4px; margin:0 0 8px 0; }
        .info-seccion p { margin:3px 0; font-size:12px; color:#444; }
        .label { color:#888; margin-right:4px; }
        .seccion-titulo { font-size:10px; font-weight:bold; color:{{ $color }}; text-transform:uppercase; letter-spacing:.5px; margin:20px 0 8px; }
        .tabla { width:100%; border-collapse:collapse; font-size:12px; }
        .tabla th { background:#f3f4f6; padding:6px 8px; text-align:left; font-size:10px; text-transform:uppercase; color:#555; border-bottom:2px solid #ddd; }
        .tabla th.r, .tabla td.r { text-align:right; }
        .tabla td { padding:6px 8px; border-bottom:1px solid #f0f0f0; }
        .tabla .total-row { font-weight:bold; background:#f8f9fa; }
        .monto-box { background:#f8f9fa; border:2px solid {{ $color }}; padding:12px 20px; margin:20px 0; }
        .monto-row { display:flex; justify-content:space-between; padding:3px 0; font-size:12px; }
        .monto-row.total { font-size:16px; font-weight:bold; border-top:2px solid {{ $color }}; padding-top:8px; margin-top:6px; color:{{ $color }}; }
        .monto-row.deuda { font-size:14px; font-weight:bold; color:#dc2626; border-top:1px solid #fca5a5; padding-top:8px; margin-top:6px; }
        .pagos-tabla { width:100%; border-collapse:collapse; font-size:11px; margin-top:6px; }
        .pagos-tabla th { background:#dcfce7; padding:5px 8px; text-align:left; font-size:10px; color:#166534; }
        .pagos-tabla th.r, .pagos-tabla td.r { text-align:right; }
        .pagos-tabla td { padding:5px 8px; border-bottom:1px solid #f0fdf4; }
        .otras-facturas { background:#fff7f7; border:1px solid #fca5a5; padding:10px 15px; margin-top:12px; border-radius:4px; }
        .otras-facturas h4 { font-size:10px; font-weight:bold; color:#dc2626; text-transform:uppercase; margin:0 0 6px 0; }
        .otras-tabla { width:100%; border-collapse:collapse; font-size:11px; }
        .otras-tabla td { padding:3px 6px; border-bottom:1px solid #fee2e2; }
        .otras-tabla td.r { text-align:right; }
        .footer { text-align:center; font-size:10px; color:#999; margin-top:30px; padding-top:15px; border-top:1px solid #eee; }
        @media print { body{margin:0;padding:8px;} .factura{border:none;} }
    </style>
</head>
<body>
<div class="factura">
    <!-- Header -->
    <div class="header">
        <div class="header-top">
            <div>
                <div class="empresa-nombre">{{ $factura->empresa->nombre }}</div>
                <div class="empresa-info">
                    @if($configuracion->mostrar_direccion_empresa && $factura->empresa->direccion)
                        {{ $factura->empresa->direccion }}<br>
                    @endif
                    @if($configuracion->mostrar_telefono_empresa && $factura->empresa->telefono)
                        Tel: {{ $factura->empresa->telefono }}
                        @if($configuracion->mostrar_email_empresa && $factura->empresa->email) &nbsp;|&nbsp; @endif
                    @endif
                    @if($configuracion->mostrar_email_empresa && $factura->empresa->email)
                        {{ $factura->empresa->email }}
                    @endif
                </div>
            </div>
            <div class="factura-num">
                <div class="num">FACTURA N° {{ $factura->numero_factura }}</div>
                <div class="fecha">Emitida: {{ $factura->fecha_emision->format('d/m/Y') }}</div>
                @if($factura->aviso === 'desconexion')
                    <div class="aviso-badge aviso-desconexion">⚠ AVISO DE DESCONEXIÓN</div>
                @elseif($factura->aviso === 'ultimo_aviso')
                    <div class="aviso-badge aviso-ultimo">🔔 ÚLTIMO AVISO</div>
                @endif
            </div>
        </div>
    </div>

    <div class="contenido">
        <!-- Info grid -->
        <div class="info-grid">
            <div class="info-seccion">
                <h3>Datos de la Factura</h3>
                <p><span class="label">Período:</span>
                    @if($factura->periodo)
                        {{ \Carbon\Carbon::createFromDate($factura->periodo->año, $factura->periodo->mes, 1)->locale('es')->isoFormat('MMMM YYYY') }}
                    @else Sin período @endif
                </p>
                <p><span class="label">Vencimiento:</span>
                    <strong style="{{ $factura->fecha_vencimiento->isPast() && $factura->estado !== 'pagado' ? 'color:#dc2626' : '' }}">
                        {{ $factura->fecha_vencimiento->format('d/m/Y') }}
                    </strong>
                </p>
                <p><span class="label">Estado:</span> {{ ucfirst($factura->estado) }}</p>
            </div>
            <div class="info-seccion">
                <h3>Cliente</h3>
                <p><strong>{{ $factura->cliente->nombre }} {{ $factura->cliente->apellido }}</strong></p>
                <p><span class="label">CI:</span> {{ $factura->cliente->cedula }}</p>
                @if($factura->cliente->direccion)
                <p>{{ $factura->cliente->direccion }}</p>
                @endif
                @if($factura->cliente->telefono)
                <p><span class="label">Tel:</span> {{ $factura->cliente->telefono }}</p>
                @endif
                @if($factura->cliente->barrio)
                <p><span class="label">Barrio:</span> {{ $factura->cliente->barrio->nombre }}</p>
                @endif
            </div>
        </div>

        <!-- Montos -->
        <div class="monto-box">
            <div class="monto-row"><span>Subtotal:</span><span>{{ number_format($factura->subtotal, 0, ',', '.') }} Gs.</span></div>
            @if($factura->descuento > 0)
            <div class="monto-row" style="color:#16a34a"><span>Descuento:</span><span>- {{ number_format($factura->descuento, 0, ',', '.') }} Gs.</span></div>
            @endif
            @if($factura->mora > 0)
            <div class="monto-row" style="color:#d97706"><span>Mora:</span><span>+ {{ number_format($factura->mora, 0, ',', '.') }} Gs.</span></div>
            @endif
            <div class="monto-row total"><span>TOTAL ESTA FACTURA:</span><span>{{ number_format($factura->total, 0, ',', '.') }} Gs.</span></div>

            @if($otrasFacturas->count() > 0)
            <div class="otras-facturas">
                <h4>⚠ Otras facturas pendientes del cliente</h4>
                <table class="otras-tabla">
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
                <div class="monto-row deuda" style="display:flex;justify-content:space-between;margin-top:8px;padding-top:6px;border-top:1px solid #fca5a5;font-weight:bold;color:#dc2626">
                    <span>DEUDA TOTAL DEL CLIENTE:</span>
                    <span>{{ number_format($deudaTotal, 0, ',', '.') }} Gs.</span>
                </div>
            </div>
            @endif
        </div>

        <!-- Historial de pagos -->
        @if($factura->pagos->count() > 0)
        <div class="seccion-titulo">Historial de Pagos</div>
        <table class="pagos-tabla">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Método</th>
                    <th>Referencia</th>
                    <th class="r">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($factura->pagos as $pago)
                <tr>
                    <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                    <td>{{ $pago->metodoPago->nombre ?? $pago->metodo_pago ?? '-' }}</td>
                    <td style="color:#666">{{ $pago->referencia ?? '-' }}</td>
                    <td class="r" style="color:#16a34a;font-weight:bold">{{ number_format($pago->monto_pagado, 0, ',', '.') }} Gs.</td>
                </tr>
                @endforeach
                <tr style="font-weight:bold;background:#f0fdf4">
                    <td colspan="3" style="text-align:right">Total pagado:</td>
                    <td class="r" style="color:#16a34a">{{ number_format($factura->pagos->sum('monto_pagado'), 0, ',', '.') }} Gs.</td>
                </tr>
                @if($factura->saldo_pendiente > 0)
                <tr style="font-weight:bold;background:#fff7f7">
                    <td colspan="3" style="text-align:right">Saldo pendiente:</td>
                    <td class="r" style="color:#dc2626">{{ number_format($factura->saldo_pendiente, 0, ',', '.') }} Gs.</td>
                </tr>
                @endif
            </tbody>
        </table>
        @endif

        <!-- Detalle de servicios -->
        @if($factura->detalles && $factura->detalles->count() > 0)
        <div class="seccion-titulo">Detalle de Servicios</div>
        <table class="tabla">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th>Descripción</th>
                    <th class="r">Cant.</th>
                    <th class="r">P. Unit.</th>
                    <th class="r">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($factura->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->concepto }}</td>
                    <td style="color:#666">{{ $detalle->descripcion }}</td>
                    <td class="r">{{ $detalle->cantidad }}</td>
                    <td class="r">{{ number_format($detalle->precio_unitario, 0, ',', '.') }}</td>
                    <td class="r">{{ number_format($detalle->subtotal, 0, ',', '.') }} Gs.</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($factura->observaciones)
        <div style="margin-top:20px;padding:10px;background:#f9f9f9;border:1px solid #eee;font-size:11px;">
            <strong>Observaciones:</strong> {{ $factura->observaciones }}
        </div>
        @endif

        <div class="footer">
            {{ $configuracion->mensaje_inferior ?? 'Gracias por su preferencia' }}<br>
            Generado el {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</div>
</body>
</html>
