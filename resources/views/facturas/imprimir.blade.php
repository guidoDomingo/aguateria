<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Factura {{ $factura->numero_factura }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 15px;
            background: white;
            font-size: 12px;
            line-height: 1.3;
        }
        .factura {
            max-width: 600px;
            margin: 0 auto;
            background: white;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .empresa-nombre {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .empresa-info {
            font-size: 10px;
            color: #666;
            margin-bottom: 10px;
        }
        .factura-numero {
            font-size: 16px;
            font-weight: bold;
            background: #f5f5f5;
            padding: 8px;
            border: 2px solid #333;
            margin-top: 10px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 15px 0;
            font-size: 11px;
        }
        .info-seccion h4 {
            margin: 0 0 8px 0;
            font-size: 12px;
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 2px;
        }
        .info-seccion p {
            margin: 2px 0;
            line-height: 1.2;
        }
        .tabla-detalles {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10px;
        }
        .tabla-detalles th,
        .tabla-detalles td {
            border: 1px solid #ccc;
            padding: 4px;
        }
        .tabla-detalles th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .tabla-detalles .numero {
            text-align: right;
        }
        .tabla-detalles .total-row {
            background: #f0f0f0;
            font-weight: bold;
        }
        .total-destacado {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            background: #f5f5f5;
            padding: 10px;
            margin: 15px 0;
            border: 2px solid #333;
        }
        .estado {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .estado.pendiente { background: #fff3cd; color: #856404; }
        .estado.pagado { background: #d4edda; color: #155724; }
        .estado.vencido { background: #f8d7da; color: #721c24; }
        .estado.parcial { background: #d1ecf1; color: #0c5460; }
        .estado.anulado { background: #e2e3e5; color: #383d41; }
        .pagos-info {
            background: #f9f9f9;
            padding: 10px;
            margin: 15px 0;
            border: 1px solid #ddd;
            font-size: 11px;
        }
        .footer {
            text-align: center;
            font-size: 9px;
            color: #666;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
        }
        @media print {
            body {
                margin: 0;
                padding: 10px;
                font-size: 11px;
            }
            .factura {
                max-width: 100%;
            }
        }
        @page {
            margin: 1cm;
        }
    </style>
    <script>
        window.onload = function() {
            // Auto-imprimir cuando la página carga
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</head>
<body>
    <div class="factura">
        <!-- Header -->
        <div class="header">
            <div class="empresa-nombre">{{ $factura->empresa->nombre }}</div>
            <div class="empresa-info">
                {{ $factura->empresa->direccion }}
                @if($factura->empresa->telefono) • Tel: {{ $factura->empresa->telefono }}@endif
                @if($factura->empresa->email) • {{ $factura->empresa->email }}@endif
            </div>
            <div class="factura-numero">
                FACTURA N° {{ str_pad($factura->numero_factura, 6, '0', STR_PAD_LEFT) }}
            </div>
        </div>

        <div class="info-grid">
            <!-- Datos de la Factura -->
            <div class="info-seccion">
                <h4>Datos de la Factura</h4>
                <p><strong>Fecha:</strong> {{ $factura->fecha_emision->format('d/m/Y') }}</p>
                <p><strong>Vencimiento:</strong> {{ $factura->fecha_vencimiento->format('d/m/Y') }}</p>
                <p><strong>Período:</strong> 
                    @if($factura->periodo)
                        {{ \Carbon\Carbon::createFromDate($factura->periodo->año, $factura->periodo->mes, 1)->locale('es')->isoFormat('MMM YYYY') }}
                    @else
                        Sin período
                    @endif
                </p>
                <p><strong>Estado:</strong> <span class="estado {{ $factura->estado }}">{{ ucfirst($factura->estado) }}</span></p>
            </div>

            <!-- Datos del Cliente -->
            <div class="info-seccion">
                <h4>Cliente</h4>
                <p><strong>{{ $factura->cliente->nombre }} {{ $factura->cliente->apellido }}</strong></p>
                <p>{{ $factura->cliente->cedula }}</p>
                <p>{{ Str::limit($factura->cliente->direccion, 40) }}</p>
                @if($factura->cliente->telefono)
                <p>Tel: {{ $factura->cliente->telefono }}</p>
                @endif
            </div>
        </div>

        <!-- Total Destacado -->
        <div class="total-destacado">
            TOTAL: {{ number_format($factura->total, 0, ',', '.') }} Gs.
        </div>

        <!-- Detalles de la Factura -->
        @if($factura->detalles && $factura->detalles->count() > 0)
        <table class="tabla-detalles">
            <thead>
                <tr>
                    <th width="30%">Concepto</th>
                    <th width="35%">Descripción</th>
                    <th width="10%">Cant.</th>
                    <th width="25%">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($factura->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->concepto }}</td>
                    <td>{{ $detalle->descripcion }}</td>
                    <td class="numero">{{ $detalle->cantidad }}</td>
                    <td class="numero">{{ number_format($detalle->subtotal, 0, ',', '.') }} Gs.</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3"><strong>TOTAL:</strong></td>
                    <td class="numero"><strong>{{ number_format($factura->total, 0, ',', '.') }} Gs.</strong></td>
                </tr>
            </tbody>
        </table>
        @endif

        <!-- Información de Pagos -->
        @if($factura->monto_pagado > 0)
        <div class="pagos-info">
            <h4>Estado de Pagos</h4>
            <p><strong>Monto Pagado:</strong> {{ number_format($factura->monto_pagado, 0, ',', '.') }} Gs.</p>
            <p><strong>Saldo Pendiente:</strong> {{ number_format($factura->saldo_pendiente, 0, ',', '.') }} Gs.</p>
            @if($factura->fecha_pago)
            <p><strong>Fecha de Pago:</strong> {{ $factura->fecha_pago->format('d/m/Y') }}</p>
            @endif
        </div>
        @endif

        <!-- Observaciones -->
        @if($factura->observaciones)
        <div style="margin-top: 15px; padding: 8px; background: #f9f9f9; border: 1px solid #ddd; font-size: 10px;">
            <strong>Observaciones:</strong><br>
            {{ $factura->observaciones }}
        </div>
        @endif

        <div class="footer">
            <p>Gracias por su preferencia</p>
            <p>{{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</body>
</html>