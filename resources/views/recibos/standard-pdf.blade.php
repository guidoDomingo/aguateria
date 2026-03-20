<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Recibo #{{ $recibo->numero_recibo }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: {{ $configuracion->fuente }}, sans-serif;
            font-size: {{ $configuracion->tamaño_fuente }}px;
            line-height: 1.4;
            color: {{ $configuracion->colores['text'] ?? '#1f2937' }};
            background: {{ $configuracion->colores['background'] ?? '#ffffff' }};
        }
        
        .recibo-container {
            width: 100%;
            @if($configuracion->tamaño_papel === '80mm')
                max-width: 80mm;
            @elseif($configuracion->tamaño_papel === '58mm')
                max-width: 58mm;
            @else
                max-width: 100%;
            @endif
        }
        
        .header {
            background: {{ $configuracion->colores['header'] ?? '#2563eb' }};
            color: white;
            padding: 10px;
            text-align: center;
            margin-bottom: 10px;
        }
        
        .logo-container {
            text-align: {{ $configuracion->posicion_logo === 'left' ? 'left' : ($configuracion->posicion_logo === 'right' ? 'right' : 'center') }};
            margin-bottom: 10px;
        }
        
        .logo {
            @if($configuracion->mostrar_logo)
            height: {{ $configuracion->tamaño_logo }}px;
            @else
            display: none;
            @endif
        }
        
        .empresa-info {
            text-align: center;
            margin-bottom: 15px;
            line-height: 1.3;
        }
        
        .empresa-nombre {
            font-size: {{ $configuracion->tamaño_fuente + 4 }}px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .info-line {
            font-size: {{ $configuracion->tamaño_fuente - 1 }}px;
            margin: 2px 0;
        }
        
        .recibo-info {
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
            margin: 10px 0;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }
        
        .info-label {
            font-weight: bold;
        }
        
        .cliente-info {
            margin: 15px 0;
            padding: 8px;
            background: #f9fafb;
            border-left: 3px solid {{ $configuracion->colores['header'] ?? '#2563eb' }};
        }
        
        .facturas-pagadas {
            margin: 15px 0;
        }
        
        .facturas-title {
            font-weight: bold;
            margin-bottom: 8px;
            color: {{ $configuracion->colores['header'] ?? '#2563eb' }};
        }
        
        .factura-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }
        
        .total-section {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px solid {{ $configuracion->colores['header'] ?? '#2563eb' }};
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: {{ $configuracion->tamaño_fuente + 2 }}px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .mensaje-section {
            margin-top: 15px;
            padding: 8px;
            text-align: center;
            border: 1px dashed #ccc;
            background: #f0f9ff;
        }
        
        .terminos {
            margin-top: 10px;
            font-size: {{ $configuracion->tamaño_fuente - 2 }}px;
            text-align: center;
            color: #6b7280;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: {{ $configuracion->tamaño_fuente - 2 }}px;
            color: #6b7280;
        }

        @if($configuracion->tamaño_papel === '80mm')
        @page { size: 80mm auto; margin: 0; }
        @elseif($configuracion->tamaño_papel === '58mm')
        @page { size: 58mm auto; margin: 0; }
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
        
        @media print {
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
    @if($configuracion->impresion_automatica)
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
    @endif
</head>
<body>
    <div class="recibo-container">
        @if($configuracion->mensaje_superior)
        <div class="mensaje-section">
            <div>{{ $configuracion->mensaje_superior }}</div>
        </div>
        @endif

        @if($configuracion->mostrar_logo && auth()->user()->empresa->logo)
        <div class="logo-container">
            <img src="data:image/png;base64,{{ base64_encode(Storage::get('public/logos/' . auth()->user()->empresa->logo)) }}" 
                 alt="Logo" class="logo">
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

        <div class="header">
            <h2>RECIBO DE PAGO</h2>
            <div>N° {{ $recibo->numero_recibo }}</div>
        </div>

        <div class="recibo-info">
            @if($configuracion->mostrar_fecha)
            <div class="info-row">
                <span class="info-label">Fecha:</span>
                <span>{{ $recibo->pago->fecha_pago->format('d/m/Y') }}</span>
            </div>
            @endif
            
            @if($configuracion->mostrar_hora)
            <div class="info-row">
                <span class="info-label">Hora:</span>
                <span>{{ $recibo->created_at->format('H:i') }}</span>
            </div>
            @endif
            
            <div class="info-row">
                <span class="info-label">Método:</span>
                <span>{{ $recibo->pago->metodoPago->nombre ?? 'Efectivo' }}</span>
            </div>
            
            @if($recibo->pago->cobrador)
            <div class="info-row">
                <span class="info-label">Cobrador:</span>
                <span>{{ $recibo->pago->cobrador->nombre }}</span>
            </div>
            @endif
        </div>

        <div class="cliente-info">
            <div class="info-row">
                <span class="info-label">Cliente:</span>
                <span>{{ $recibo->pago->cliente->nombre_completo }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Cédula:</span>
                <span>{{ $recibo->pago->cliente->cedula }}</span>
            </div>
            @if($recibo->pago->cliente->direccion)
            <div class="info-row">
                <span class="info-label">Dirección:</span>
                <span>{{ $recibo->pago->cliente->direccion }}</span>
            </div>
            @endif
        </div>

        @if($recibo->facturas_pagadas && count($recibo->facturas_pagadas) > 0)
        <div class="facturas-pagadas">
            <div class="facturas-title">Facturas Canceladas:</div>
            @foreach($recibo->facturas_pagadas as $facturaPagada)
            <div class="factura-item">
                <span>{{ $facturaPagada['numero'] }}</span>
                <span>Gs. {{ number_format($facturaPagada['monto'], 0, ',', '.') }}</span>
            </div>
            @endforeach
        </div>
        @endif

        @php
            $dd = $recibo->datos_descuento ?? [];
            $moraExon  = floatval($dd['mora_exonerada'] ?? 0);
            $descuento = floatval($dd['descuento'] ?? 0);
            $porcDesc  = floatval($dd['porcentaje_descuento'] ?? 0);
            $subtotalOrig = floatval($dd['subtotal_original'] ?? $recibo->pago->monto_pagado);
        @endphp
        <div class="total-section">
            @if($moraExon > 0 || $descuento > 0)
            <div class="total-row" style="font-size:10px;color:#666;">
                <span>Subtotal:</span>
                <span>Gs. {{ number_format($subtotalOrig, 0, ',', '.') }}</span>
            </div>
            @if($moraExon > 0)
            <div class="total-row" style="font-size:10px;color:#d97706;">
                <span>Mora exonerada:</span>
                <span>- Gs. {{ number_format($moraExon, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($descuento > 0)
            <div class="total-row" style="font-size:10px;color:#7c3aed;">
                <span>Descuento {{ $porcDesc > 0 ? $porcDesc.'%' : '' }}:</span>
                <span>- Gs. {{ number_format($descuento, 0, ',', '.') }}</span>
            </div>
            @endif
            @endif
            <div class="total-row">
                <span>TOTAL PAGADO:</span>
                <span>Gs. {{ number_format($recibo->pago->monto_pagado, 0, ',', '.') }}</span>
            </div>
        </div>

        @if($configuracion->mensaje_inferior)
        <div class="mensaje-section">
            <div>{{ $configuracion->mensaje_inferior }}</div>
        </div>
        @endif

        @if($configuracion->terminos_condiciones)
        <div class="terminos">
            {{ $configuracion->terminos_condiciones }}
        </div>
        @endif

        <div class="footer">
            <div>Documento generado electrónicamente</div>
            @if($configuracion->mostrar_fecha && $configuracion->mostrar_hora)
            <div>{{ now()->format('d/m/Y H:i:s') }}</div>
            @endif
        </div>
    </div>
</body>
</html>