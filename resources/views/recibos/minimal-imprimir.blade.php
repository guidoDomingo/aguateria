<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Imprimir Recibo #{{ $recibo->numero_recibo }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: {{ $configuracion->fuente }}, monospace;
            font-size: {{ $configuracion->tamaño_fuente }}px;
            line-height: 1.4;
            color: {{ $configuracion->colores['text'] ?? '#000000' }};
            background: {{ $configuracion->colores['background'] ?? '#ffffff' }};
        }
        
        .container {
            @if($configuracion->tamaño_papel === '80mm')
                max-width: 80mm;
            @elseif($configuracion->tamaño_papel === '58mm')
                max-width: 58mm;
            @elseif($configuracion->tamaño_papel === 'A4')
                max-width: 210mm;
            @else
                max-width: 100%;
            @endif
            margin: 0 auto;
            background: white;
            border: 1px solid {{ $configuracion->colores['header'] ?? '#000000' }};
            font-family: monospace;
        }
        
        .header {
            background: {{ $configuracion->colores['header'] ?? '#000000' }};
            color: white;
            padding: 15px;
            text-align: center;
            border-bottom: 3px solid {{ $configuracion->colores['accent'] ?? '#666666' }};
        }
        
        .logo-container {
            text-align: {{ $configuracion->posicion_logo === 'left' ? 'left' : ($configuracion->posicion_logo === 'right' ? 'right' : 'center') }};
            margin-bottom: 10px;
        }
        
        .logo {
            @if($configuracion->mostrar_logo)
            height: {{ $configuracion->tamaño_logo }}px;
            border: 2px solid white;
            @else
            display: none;
            @endif
        }
        
        .recibo-title {
            font-size: {{ $configuracion->tamaño_fuente + 6 }}px;
            font-weight: bold;
            margin: 8px 0;
            letter-spacing: 2px;
        }
        
        .recibo-number {
            font-size: {{ $configuracion->tamaño_fuente + 2 }}px;
            font-weight: normal;
        }
        
        .content {
            padding: 20px;
        }
        
        .empresa-info {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px 0;
            border-bottom: 1px solid {{ $configuracion->colores['accent'] ?? '#666666' }};
        }
        
        .empresa-nombre {
            font-size: {{ $configuracion->tamaño_fuente + 4 }}px;
            font-weight: bold;
            margin-bottom: 8px;
            color: {{ $configuracion->colores['header'] ?? '#000000' }};
        }
        
        .info-line {
            font-size: {{ $configuracion->tamaño_fuente }}px;
            margin: 2px 0;
            color: #333333;
        }
        
        .section {
            margin: 15px 0;
            border: 1px solid #cccccc;
            padding: 10px;
            background: #fafafa;
        }
        
        .section-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: {{ $configuracion->colores['header'] ?? '#000000' }};
            font-size: {{ $configuracion->tamaño_fuente + 1 }}px;
            text-decoration: underline;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-table td {
            padding: 4px 8px;
            border-bottom: 1px dotted #cccccc;
            vertical-align: top;
        }
        
        .info-label {
            font-weight: bold;
            width: 40%;
        }
        
        .info-value {
            width: 60%;
        }
        
        .facturas-list {
            margin-top: 10px;
        }
        
        .factura-item {
            padding: 8px;
            margin: 4px 0;
            border: 1px solid #dddddd;
            background: white;
            display: flex;
            justify-content: space-between;
            font-family: monospace;
        }
        
        .factura-numero {
            font-weight: bold;
        }
        
        .factura-monto {
            font-weight: bold;
            color: {{ $configuracion->colores['header'] ?? '#000000' }};
        }
        
        .total-section {
            background: {{ $configuracion->colores['header'] ?? '#000000' }};
            color: white;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
            border: 2px solid {{ $configuracion->colores['accent'] ?? '#666666' }};
        }
        
        .total-label {
            font-size: {{ $configuracion->tamaño_fuente + 2 }}px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .total-amount {
            font-size: {{ $configuracion->tamaño_fuente + 6 }}px;
            font-weight: bold;
            font-family: monospace;
        }
        
        .mensaje-box {
            border: 2px dashed {{ $configuracion->colores['accent'] ?? '#666666' }};
            padding: 10px;
            text-align: center;
            margin: 15px 0;
            background: #f9f9f9;
            font-style: italic;
        }
        
        .footer {
            background: #f5f5f5;
            padding: 10px;
            text-align: center;
            border-top: 1px solid {{ $configuracion->colores['accent'] ?? '#666666' }};
            font-size: {{ $configuracion->tamaño_fuente - 2 }}px;
            color: #666666;
        }
        
        .separator {
            text-align: center;
            margin: 10px 0;
            font-size: {{ $configuracion->tamaño_fuente + 2 }}px;
            color: {{ $configuracion->colores['accent'] ?? '#666666' }};
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: {{ $configuracion->colores['header'] ?? '#000000' }};
            color: white;
            border: 2px solid {{ $configuracion->colores['accent'] ?? '#666666' }};
            padding: 10px 20px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            font-family: monospace;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
        }

        .print-button:hover {
            background: {{ $configuracion->colores['accent'] ?? '#666666' }};
            border-color: {{ $configuracion->colores['header'] ?? '#000000' }};
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.4);
        }

        /* Print styles */
        @media print {
            body { 
                -webkit-print-color-adjust: exact; 
            }
            .container { 
                border: 1px solid #000; 
            }
            .print-button {
                display: none;
            }
        }

        @if($configuracion->tamaño_papel === '80mm')
        @page { size: 80mm auto; margin: 0; }
        .content { padding: 10px; }
        .section { padding: 6px; margin: 8px 0; }
        @elseif($configuracion->tamaño_papel === '58mm')
        @page { size: 58mm auto; margin: 0; }
        .content { padding: 8px; }
        .section { padding: 4px; margin: 6px 0; }
        @elseif($configuracion->tamaño_papel === 'A4')
        @page { 
            size: A4 {{ $configuracion->orientacion === 'landscape' ? 'landscape' : 'portrait' }};
            margin: {{ $configuracion->margenes_superior }}mm {{ $configuracion->margenes_derecho }}mm {{ $configuracion->margenes_inferior }}mm {{ $configuracion->margenes_izquierdo }}mm;
        }
        @elseif($configuracion->tamaño_papel === 'personalizado' && $configuracion->ancho_personalizado && $configuracion->alto_personalizado)
        @page { 
            size: {{ $configuracion->ancho_personalizado }}mm {{ $configuracion->alto_personalizado }}mm;
            margin: {{ $configuracion->margenes_superior }}mm {{ $configuracion->margenes_derecho }}mm {{ $configuracion->margenes_inferior }}mm {{ $configuracion->margenes_izquierdo }}mm;
        }
        @endif
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">
        IMPRIMIR
    </button>

    <div class="container">
        <div class="header">
            @if($configuracion->mostrar_logo && auth()->user()->empresa->logo)
            <div class="logo-container">
                <img src="{{ Storage::url('public/logos/' . auth()->user()->empresa->logo) }}" 
                     alt="Logo" class="logo">
            </div>
            @endif
            
            <div class="recibo-title">RECIBO DE PAGO</div>
            <div class="recibo-number">No. {{ $recibo->numero_recibo }}</div>
        </div>

        <div class="content">
            @if($configuracion->mensaje_superior)
            <div class="mensaje-box">
                {{ $configuracion->mensaje_superior }}
            </div>
            @endif

            <div class="empresa-info">
                <div class="empresa-nombre">{{ auth()->user()->empresa->nombre }}</div>
                
                @if($configuracion->mostrar_direccion_empresa && auth()->user()->empresa->direccion)
                <div class="info-line">{{ auth()->user()->empresa->direccion }}</div>
                @endif
                
                @if($configuracion->mostrar_telefono_empresa && auth()->user()->empresa->telefono)
                <div class="info-line">Tel: {{ auth()->user()->empresa->telefono }}</div>
                @endif
                
                @if($configuracion->mostrar_email_empresa && auth()->user()->empresa->email)
                <div class="info-line">{{ auth()->user()->empresa->email }}</div>
                @endif
            </div>

            <div class="separator">- - - - - - - - - - - - - - -</div>

            <div class="section">
                <div class="section-title">DATOS DEL PAGO</div>
                <table class="info-table">
                    @if($configuracion->mostrar_fecha)
                    <tr>
                        <td class="info-label">Fecha:</td>
                        <td class="info-value">{{ $recibo->pago->fecha_pago->format('d/m/Y') }}</td>
                    </tr>
                    @endif
                    @if($configuracion->mostrar_hora)
                    <tr>
                        <td class="info-label">Hora:</td>
                        <td class="info-value">{{ $recibo->created_at->format('H:i') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="info-label">Método:</td>
                        <td class="info-value">{{ $recibo->pago->metodoPago->nombre ?? 'Efectivo' }}</td>
                    </tr>
                    @if($recibo->pago->cobrador)
                    <tr>
                        <td class="info-label">Cobrador:</td>
                        <td class="info-value">{{ $recibo->pago->cobrador->nombre }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <div class="section">
                <div class="section-title">DATOS DEL CLIENTE</div>
                <table class="info-table">
                    <tr>
                        <td class="info-label">Nombre:</td>
                        <td class="info-value">{{ $recibo->pago->cliente->nombre_completo }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Cedula:</td>
                        <td class="info-value">{{ $recibo->pago->cliente->cedula }}</td>
                    </tr>
                    @if($recibo->pago->cliente->direccion)
                    <tr>
                        <td class="info-label">Direccion:</td>
                        <td class="info-value">{{ $recibo->pago->cliente->direccion }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            @if($recibo->facturas_pagadas && count($recibo->facturas_pagadas) > 0)
            <div class="section">
                <div class="section-title">FACTURAS CANCELADAS</div>
                <div class="facturas-list">
                    @foreach($recibo->facturas_pagadas as $facturaPagada)
                    <div class="factura-item">
                        <span class="factura-numero">{{ $facturaPagada['numero'] }}</span>
                        <span class="factura-monto">Gs. {{ number_format($facturaPagada['monto'], 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="separator">= = = = = = = = = = = = = = =</div>

                    @php
            $dd = $recibo->datos_descuento ?? [];
            $moraExon  = floatval($dd["mora_exonerada"] ?? 0);
            $descuento = floatval($dd["descuento"] ?? 0);
            $porcDesc  = floatval($dd["porcentaje_descuento"] ?? 0);
            $subtotalOrig = floatval($dd["subtotal_original"] ?? $recibo->pago->monto_pagado);
        @endphp
        <div class="total-section">
            @if($moraExon > 0 || $descuento > 0)
            <div style="font-size:10px;color:#666;display:flex;justify-content:space-between;">
                <span>Subtotal:</span><span>Gs. {{ number_format($subtotalOrig, 0, ",", ".") }}</span>
            </div>
            @if($moraExon > 0)
            <div style="font-size:10px;color:#d97706;display:flex;justify-content:space-between;">
                <span>Mora exonerada:</span><span>- Gs. {{ number_format($moraExon, 0, ",", ".") }}</span>
            </div>
            @endif
            @if($descuento > 0)
            <div style="font-size:10px;color:#7c3aed;display:flex;justify-content:space-between;">
                <span>Descuento {{ $porcDesc > 0 ? $porcDesc."%%" : "" }}:</span><span>- Gs. {{ number_format($descuento, 0, ",", ".") }}</span>
            </div>
            @endif
            @endif
            
                <div class="total-label">TOTAL PAGADO</div>
                <div class="total-amount">Gs. {{ number_format($recibo->pago->monto_pagado, 0, ',', '.') }}</div>
            </div>

            @if($configuracion->mensaje_inferior)
            <div class="mensaje-box">
                {{ $configuracion->mensaje_inferior }}
            </div>
            @endif

            @if($configuracion->terminos_condiciones)
            <div class="mensaje-box">
                <small>{{ $configuracion->terminos_condiciones }}</small>
            </div>
            @endif

            <div class="separator">- - - - - - - - - - - - - - -</div>
        </div>

        <div class="footer">
            <div>Documento generado electronicamente</div>
            @if($configuracion->mostrar_fecha && $configuracion->mostrar_hora)
            <div>{{ now()->format('d/m/Y H:i:s') }}</div>
            @endif
        </div>
    </div>

    @if($configuracion->impresion_automatica)
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
    @endif
</body>
</html>