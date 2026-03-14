<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $factura->numero_factura }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
            font-size: 14px;
            line-height: 1.4;
        }
        .factura {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            background: white;
        }
        .header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 2px solid #333;
        }
        .empresa {
            text-align: center;
            margin-bottom: 15px;
        }
        .empresa-nombre {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .empresa-info {
            font-size: 12px;
            color: #666;
        }
        .factura-info {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-top: 15px;
        }
        .contenido {
            padding: 20px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .info-seccion h3 {
            font-size: 16px;
            margin: 0 0 10px 0;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .info-seccion p {
            margin: 3px 0;
            font-size: 13px;
            color: #555;
        }
        .info-seccion .label {
            font-weight: bold;
            color: #333;
        }
        .detalles-factura {
            margin: 30px 0;
        }
        .tabla-detalles {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }
        .tabla-detalles th,
        .tabla-detalles td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .tabla-detalles th {
            background: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        .tabla-detalles .numero {
            text-align: right;
        }
        .tabla-detalles .total-row {
            background: #f8f9fa;
            font-weight: bold;
        }
        .resumen {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 30px;
        }
        .resumen-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }
        .resumen-item.total {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        .estado {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .estado.pendiente { background: #fff3cd; color: #856404; }
        .estado.pagado { background: #d4edda; color: #155724; }
        .estado.vencido { background: #f8d7da; color: #721c24; }
        .estado.parcial { background: #d1ecf1; color: #0c5460; }
        .estado.anulado { background: #e2e3e5; color: #383d41; }
        .vencimiento {
            color: #721c24;
            font-weight: bold;
        }
        .observaciones {
            margin-top: 30px;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        .observaciones h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        @media print {
            body { margin: 0; padding: 10px; }
            .factura { border: none; }
        }
    </style>
</head>
<body>
    <div class="factura">
        <!-- Header -->
        <div class="header">
            <div class="empresa">
                <div class="empresa-nombre">{{ $factura->empresa->nombre }}</div>
                <div class="empresa-info">
                    {{ $factura->empresa->direccion }}
                    @if($factura->empresa->telefono)
                    <br>Tel: {{ $factura->empresa->telefono }}
                    @endif
                    @if($factura->empresa->email)
                    <br>Email: {{ $factura->empresa->email }}
                    @endif
                </div>
            </div>
            <div class="factura-info">
                FACTURA N° {{ str_pad($factura->numero_factura, 6, '0', STR_PAD_LEFT) }}
            </div>
        </div>

        <!-- Contenido -->
        <div class="contenido">
            <div class="info-grid">
                <!-- Datos de la Factura -->
                <div class="info-seccion">
                    <h3>Datos de la Factura</h3>
                    <p><span class="label">Fecha de Emisión:</span> {{ $factura->fecha_emision->format('d/m/Y') }}</p>
                    <p><span class="label">Fecha de Vencimiento:</span> 
                        <span class="{{ $factura->fecha_vencimiento < now() ? 'vencimiento' : '' }}">
                            {{ $factura->fecha_vencimiento->format('d/m/Y') }}
                        </span>
                    </p>
                    <p><span class="label">Período:</span> 
                        @if($factura->periodo)
                            {{ \Carbon\Carbon::createFromDate($factura->periodo->año, $factura->periodo->mes, 1)->locale('es')->isoFormat('MMMM YYYY') }}
                        @else
                            Sin período
                        @endif
                    </p>
                    <p><span class="label">Estado:</span> 
                        <span class="estado {{ $factura->estado }}">{{ ucfirst($factura->estado) }}</span>
                    </p>
                </div>

                <!-- Datos del Cliente -->
                <div class="info-seccion">
                    <h3>Datos del Cliente</h3>
                    <p><span class="label">Nombre:</span> {{ $factura->cliente->nombre }} {{ $factura->cliente->apellido }}</p>
                    <p><span class="label">Cédula:</span> {{ $factura->cliente->cedula }}</p>
                    <p><span class="label">Dirección:</span> {{ $factura->cliente->direccion }}</p>
                    @if($factura->cliente->telefono)
                    <p><span class="label">Teléfono:</span> {{ $factura->cliente->telefono }}</p>
                    @endif
                    @if($factura->cliente->barrio)
                    <p><span class="label">Barrio:</span> {{ $factura->cliente->barrio->nombre }}</p>
                    @endif
                </div>
            </div>

            <!-- Detalles de la Factura -->
            @if($factura->detalles && $factura->detalles->count() > 0)
            <div class="detalles-factura">
                <h3>Detalle de Servicios</h3>
                <table class="tabla-detalles">
                    <thead>
                        <tr>
                            <th width="25%">Concepto</th>
                            <th width="35%">Descripción</th>
                            <th width="10%">Cant.</th>
                            <th width="15%">Precio Unit.</th>
                            <th width="15%">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($factura->detalles as $detalle)
                        <tr>
                            <td>{{ $detalle->concepto }}</td>
                            <td>{{ $detalle->descripcion }}</td>
                            <td class="numero">{{ $detalle->cantidad }}</td>
                            <td class="numero">{{ number_format($detalle->precio_unitario, 0, ',', '.') }} Gs.</td>
                            <td class="numero">{{ number_format($detalle->subtotal, 0, ',', '.') }} Gs.</td>
                        </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="4" style="text-align: right;"><strong>TOTAL:</strong></td>
                            <td class="numero" style="font-size: 16px;"><strong>{{ number_format($factura->total, 0, ',', '.') }} Gs.</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Resumen de Pagos -->
            @if($factura->monto_pagado > 0)
            <div class="resumen">
                <div>
                    <h3>Resumen de Pagos</h3>
                    <div class="resumen-item">
                        <span>Total de la Factura:</span>
                        <span>{{ number_format($factura->total, 0, ',', '.') }} Gs.</span>
                    </div>
                    <div class="resumen-item">
                        <span>Monto Pagado:</span>
                        <span>{{ number_format($factura->monto_pagado, 0, ',', '.') }} Gs.</span>
                    </div>
                    <div class="resumen-item total">
                        <span>Saldo Pendiente:</span>
                        <span>{{ number_format($factura->saldo_pendiente, 0, ',', '.') }} Gs.</span>
                    </div>
                    @if($factura->fecha_pago)
                    <div class="resumen-item">
                        <span>Fecha de Pago:</span>
                        <span>{{ $factura->fecha_pago->format('d/m/Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Observaciones -->
            @if($factura->observaciones)
            <div class="observaciones">
                <h4>Observaciones</h4>
                <p>{{ $factura->observaciones }}</p>
            </div>
            @endif

            <!-- Footer -->
            <div class="footer">
                <p>Gracias por su preferencia</p>
                <p>Documento generado el {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>
</body>
</html>