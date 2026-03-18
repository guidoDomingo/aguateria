<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Recibo de Pago #{{ $recibo->numero_recibo }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: {{ $configuracion->fuente }}, serif;
            font-size: {{ $configuracion->tamaño_fuente }}px;
            line-height: 1.5;
            color: {{ $configuracion->colores['text'] ?? '#2c3e50' }};
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
            border: 3px solid {{ $configuracion->colores['header'] ?? '#34495e' }};
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
        
        .header {
            background: {{ $configuracion->colores['header'] ?? '#34495e' }};
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
            border-bottom: 5px solid {{ $configuracion->colores['accent'] ?? '#e67e22' }};
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: repeating-linear-gradient(
                90deg,
                {{ $configuracion->colores['accent'] ?? '#e67e22' }},
                {{ $configuracion->colores['accent'] ?? '#e67e22' }} 10px,
                transparent 10px,
                transparent 20px
            );
        }
        
        .ornament-top, .ornament-bottom {
            text-align: center;
            font-size: {{ $configuracion->tamaño_fuente + 6 }}px;
            color: {{ $configuracion->colores['accent'] ?? '#e67e22' }};
            margin: 5px 0;
        }
        
        .logo-container {
            text-align: {{ $configuracion->posicion_logo === 'left' ? 'left' : ($configuracion->posicion_logo === 'right' ? 'right' : 'center') }};
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }
        
        .logo {
            @if($configuracion->mostrar_logo)
            height: {{ $configuracion->tamaño_logo }}px;
            border: 3px solid white;
            border-radius: 50%;
            @else
            display: none;
            @endif
        }
        
        .recibo-title {
            font-size: {{ $configuracion->tamaño_fuente + 10 }}px;
            font-weight: 700;
            letter-spacing: 3px;
            margin: 10px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
        }
        
        .recibo-number {
            font-size: {{ $configuracion->tamaño_fuente + 6 }}px;
            font-weight: 300;
            letter-spacing: 1px;
            position: relative;
            z-index: 1;
        }
        
        .content {
            padding: 30px;
        }
        
        .empresa-section {
            text-align: center;
            margin-bottom: 25px;
            padding: 20px;
            border: 2px dashed {{ $configuracion->colores['header'] ?? '#34495e' }};
            background: linear-gradient(45deg, #ecf0f1, #bdc3c7);
            border-radius: 10px;
            position: relative;
        }
        
        .empresa-section::before,
        .empresa-section::after {
            content: '❦';
            position: absolute;
            top: -10px;
            font-size: 20px;
            background: white;
            padding: 0 5px;
            color: {{ $configuracion->colores['accent'] ?? '#e67e22' }};
        }
        
        .empresa-section::before {
            left: 20px;
        }
        
        .empresa-section::after {
            right: 20px;
        }
        
        .empresa-nombre {
            font-size: {{ $configuracion->tamaño_fuente + 8 }}px;
            font-weight: 800;
            margin-bottom: 10px;
            color: {{ $configuracion->colores['header'] ?? '#34495e' }};
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        
        .info-line {
            font-size: {{ $configuracion->tamaño_fuente + 1 }}px;
            margin: 4px 0;
            color: #2c3e50;
            font-style: italic;
        }
        
        .section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid {{ $configuracion->colores['header'] ?? '#34495e' }};
            border-radius: 5px;
            background: #fafafa;
        }
        
        .section-title {
            font-weight: 700;
            margin-bottom: 15px;
            color: {{ $configuracion->colores['header'] ?? '#34495e' }};
            font-size: {{ $configuracion->tamaño_fuente + 3 }}px;
            text-align: center;
            border-bottom: 2px solid {{ $configuracion->colores['accent'] ?? '#e67e22' }};
            padding-bottom: 8px;
            position: relative;
        }
        
        .section-title::before,
        .section-title::after {
            content: '◆';
            position: absolute;
            bottom: -8px;
            font-size: 12px;
            color: {{ $configuracion->colores['accent'] ?? '#e67e22' }};
        }
        
        .section-title::before {
            left: 0;
        }
        
        .section-title::after {
            right: 0;
        }
        
        .info-grid {
            display: grid;
            gap: 8px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dotted #95a5a6;
        }
        
        .info-label {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .info-value {
            font-weight: 500;
            color: #34495e;
        }
        
        .cliente-section {
            background: linear-gradient(45deg, #e8f5e8, #d1f2d1);
            border: 2px solid #27ae60;
        }
        
        .pago-section {
            background: linear-gradient(45deg, #e8f0ff, #d1e7ff);
            border: 2px solid #3498db;
        }
        
        .facturas-section {
            background: linear-gradient(45deg, #fff0e8, #ffe7d1);
            border: 2px solid #e67e22;
            margin: 25px 0;
            padding: 20px;
        }
        
        .factura-lista {
            margin-top: 15px;
        }
        
        .factura-item {
            background: white;
            border: 1px solid #d35400;
            border-radius: 5px;
            padding: 12px 15px;
            margin: 8px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .factura-numero {
            font-weight: 700;
            color: #2c3e50;
            font-family: monospace;
            font-size: {{ $configuracion->tamaño_fuente + 1 }}px;
        }
        
        .factura-monto {
            font-weight: 800;
            color: #d35400;
            font-size: {{ $configuracion->tamaño_fuente + 2 }}px;
        }
        
        .total-section {
            background: linear-gradient(135deg, {{ $configuracion->colores['header'] ?? '#34495e' }}, #2c3e50);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin: 30px 0;
            text-align: center;
            position: relative;
            border: 3px solid {{ $configuracion->colores['accent'] ?? '#e67e22' }};
        }
        
        .total-section::before {
            content: '';
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 30px;
            background: {{ $configuracion->colores['accent'] ?? '#e67e22' }};
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .total-section::after {
            content: '₲';
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 18px;
            font-weight: bold;
            color: white;
            line-height: 30px;
        }
        
        .total-label {
            font-size: {{ $configuracion->tamaño_fuente + 4 }}px;
            font-weight: 400;
            letter-spacing: 2px;
            margin-bottom: 8px;
            opacity: 0.9;
        }
        
        .total-amount {
            font-size: {{ $configuracion->tamaño_fuente + 12 }}px;
            font-weight: 900;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            letter-spacing: 1px;
        }
        
        .mensaje-decorativo {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            border: 3px double {{ $configuracion->colores['header'] ?? '#34495e' }};
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
            font-style: italic;
            color: #495057;
            position: relative;
        }
        
        .mensaje-decorativo::before,
        .mensaje-decorativo::after {
            content: '"';
            font-size: 40px;
            color: {{ $configuracion->colores['accent'] ?? '#e67e22' }};
            position: absolute;
            top: -5px;
        }
        
        .mensaje-decorativo::before {
            left: 10px;
        }
        
        .mensaje-decorativo::after {
            right: 10px;
            transform: rotate(180deg);
        }
        
        .footer {
            background: {{ $configuracion->colores['header'] ?? '#34495e' }};
            color: white;
            padding: 20px;
            text-align: center;
            border-top: 5px solid {{ $configuracion->colores['accent'] ?? '#e67e22' }};
            position: relative;
        }
        
        .footer::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: repeating-linear-gradient(
                90deg,
                {{ $configuracion->colores['accent'] ?? '#e67e22' }},
                {{ $configuracion->colores['accent'] ?? '#e67e22' }} 10px,
                transparent 10px,
                transparent 20px
            );
        }
        
        .footer-line {
            margin: 3px 0;
            font-size: {{ $configuracion->tamaño_fuente - 1 }}px;
        }

        /* Print styles */
        @media print {
            body { -webkit-print-color-adjust: exact; }
            .container { 
                box-shadow: none; 
            }
        }

        @if($configuracion->tamaño_papel === '80mm')
        @page { size: 80mm auto; margin: 0; }
        .container { border: 1px solid {{ $configuracion->colores['header'] ?? '#34495e' }}; }
        .section { padding: 8px; margin: 10px 0; }
        .content { padding: 15px; }
        @elseif($configuracion->tamaño_papel === '58mm')
        @page { size: 58mm auto; margin: 0; }
        .container { border: 1px solid {{ $configuracion->colores['header'] ?? '#34495e' }}; }
        .section { padding: 6px; margin: 8px 0; }
        .content { padding: 10px; }
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
    <div class="container">
        <div class="header">
            <div class="ornament-top">❖ ❖ ❖</div>
            
            @if($configuracion->mostrar_logo && auth()->user()->empresa->logo)
            <div class="logo-container">
                <img src="{{ Storage::url('public/logos/' . auth()->user()->empresa->logo) }}" 
                     alt="Logo" class="logo">
            </div>
            @endif
            
            <div class="recibo-title">RECIBO DE PAGO</div>
            <div class="recibo-number">N° {{ $recibo->numero_recibo }}</div>
            
            <div class="ornament-bottom">❖ ❖ ❖</div>
        </div>

        <div class="content">
            @if($configuracion->mensaje_superior)
            <div class="mensaje-decorativo">
                {{ $configuracion->mensaje_superior }}
            </div>
            @endif

            <div class="empresa-section">
                <div class="empresa-nombre">{{ auth()->user()->empresa->nombre }}</div>
                
                @if($configuracion->mostrar_direccion_empresa && auth()->user()->empresa->direccion)
                <div class="info-line">{{ auth()->user()->empresa->direccion }}</div>
                @endif
                
                @if($configuracion->mostrar_telefono_empresa && auth()->user()->empresa->telefono)
                <div class="info-line">Teléfono: {{ auth()->user()->empresa->telefono }}</div>
                @endif
                
                @if($configuracion->mostrar_email_empresa && auth()->user()->empresa->email)
                <div class="info-line">Email: {{ auth()->user()->empresa->email }}</div>
                @endif
            </div>

            <div class="section pago-section">
                <div class="section-title">Información del Pago</div>
                <div class="info-grid">
                    @if($configuracion->mostrar_fecha)
                    <div class="info-row">
                        <span class="info-label">Fecha de Pago:</span>
                        <span class="info-value">{{ $recibo->pago->fecha_pago->format('d/m/Y') }}</span>
                    </div>
                    @endif
                    @if($configuracion->mostrar_hora)
                    <div class="info-row">
                        <span class="info-label">Hora:</span>
                        <span class="info-value">{{ $recibo->created_at->format('H:i') }}</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">Método de Pago:</span>
                        <span class="info-value">{{ $recibo->pago->metodoPago->nombre ?? 'Efectivo' }}</span>
                    </div>
                    @if($recibo->pago->cobrador)
                    <div class="info-row">
                        <span class="info-label">Cobrador:</span>
                        <span class="info-value">{{ $recibo->pago->cobrador->nombre }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="section cliente-section">
                <div class="section-title">Datos del Cliente</div>
                <div class="info-grid">
                    <div class="info-row">
                        <span class="info-label">Nombre Completo:</span>
                        <span class="info-value">{{ $recibo->pago->cliente->nombre_completo }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Cédula:</span>
                        <span class="info-value">{{ $recibo->pago->cliente->cedula }}</span>
                    </div>
                    @if($recibo->pago->cliente->direccion)
                    <div class="info-row">
                        <span class="info-label">Dirección:</span>
                        <span class="info-value">{{ $recibo->pago->cliente->direccion }}</span>
                    </div>
                    @endif
                </div>
            </div>

            @if($recibo->facturas_pagadas && count($recibo->facturas_pagadas) > 0)
            <div class="facturas-section">
                <div class="section-title">Detalle de Facturas Canceladas</div>
                <div class="factura-lista">
                    @foreach($recibo->facturas_pagadas as $facturaPagada)
                    <div class="factura-item">
                        <span class="factura-numero">Factura {{ $facturaPagada['numero'] }}</span>
                        <span class="factura-monto">Gs. {{ number_format($facturaPagada['monto'], 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="total-section">
                <div class="total-label">TOTAL PAGADO</div>
                <div class="total-amount">Gs. {{ number_format($recibo->pago->monto_pagado, 0, ',', '.') }}</div>
            </div>

            @if($configuracion->mensaje_inferior)
            <div class="mensaje-decorativo">
                {{ $configuracion->mensaje_inferior }}
            </div>
            @endif

            @if($configuracion->terminos_condiciones)
            <div class="mensaje-decorativo">
                <small>{{ $configuracion->terminos_condiciones }}</small>
            </div>
            @endif
        </div>

        <div class="footer">
            <div class="footer-line">Documento generado electrónicamente</div>
            @if($configuracion->mostrar_fecha && $configuracion->mostrar_hora)
            <div class="footer-line">Generado el {{ now()->format('d/m/Y \a \l\a\s H:i:s') }}</div>
            @endif
        </div>
    </div>
</body>
</html>