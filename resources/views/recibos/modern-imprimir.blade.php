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
            font-family: {{ $configuracion->fuente }}, sans-serif;
            font-size: {{ $configuracion->tamaño_fuente }}px;
            line-height: 1.6;
            color: {{ $configuracion->colores['text'] ?? '#2d3748' }};
            background: {{ $configuracion->colores['background'] ?? '#f7fafc' }};
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
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-radius: 12px;
            overflow: hidden;
        }
        
        .header-gradient {
            background: linear-gradient(135deg, {{ $configuracion->colores['header'] ?? '#667eea' }}, {{ $configuracion->colores['accent'] ?? '#764ba2' }});
            padding: 30px 25px;
            text-align: center;
            position: relative;
            color: white;
        }
        
        .header-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="20" cy="80" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .logo-container {
            position: relative;
            z-index: 1;
            text-align: {{ $configuracion->posicion_logo === 'left' ? 'left' : ($configuracion->posicion_logo === 'right' ? 'right' : 'center') }};
            margin-bottom: 15px;
        }
        
        .logo {
            @if($configuracion->mostrar_logo)
            height: {{ $configuracion->tamaño_logo }}px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            @else
            display: none;
            @endif
        }
        
        .recibo-title {
            font-size: {{ $configuracion->tamaño_fuente + 8 }}px;
            font-weight: 700;
            margin: 10px 0;
            position: relative;
            z-index: 1;
        }
        
        .recibo-number {
            font-size: {{ $configuracion->tamaño_fuente + 4 }}px;
            font-weight: 300;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .content {
            padding: 25px;
        }
        
        .empresa-info {
            text-align: center;
            margin-bottom: 25px;
            padding: 20px;
            background: linear-gradient(45deg, #f8fafc, #e2e8f0);
            border-radius: 8px;
            border-left: 4px solid {{ $configuracion->colores['accent'] ?? '#764ba2' }};
        }
        
        .empresa-nombre {
            font-size: {{ $configuracion->tamaño_fuente + 6 }}px;
            font-weight: 800;
            margin-bottom: 8px;
            color: {{ $configuracion->colores['header'] ?? '#667eea' }};
        }
        
        .info-line {
            font-size: {{ $configuracion->tamaño_fuente }}px;
            margin: 3px 0;
            color: #4a5568;
        }
        
        .info-cards {
            display: block;
            margin-bottom: 25px;
        }
        
        .info-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 15px;
        }
        
        .card-title {
            font-weight: 600;
            color: {{ $configuracion->colores['header'] ?? '#667eea' }};
            margin-bottom: 10px;
            font-size: {{ $configuracion->tamaño_fuente + 1 }}px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .card-title::before {
            content: '';
            width: 4px;
            height: 4px;
            background: {{ $configuracion->colores['accent'] ?? '#764ba2' }};
            border-radius: 50%;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            padding: 3px 0;
        }
        
        .info-label {
            font-weight: 500;
            color: #4a5568;
        }
        
        .info-value {
            font-weight: 600;
            color: #2d3748;
        }
        
        .facturas-section {
            background: linear-gradient(135deg, #fff5f5, #fed7d7);
            border: 1px solid #feb2b2;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .facturas-title {
            font-weight: 700;
            margin-bottom: 15px;
            color: #c53030;
            font-size: {{ $configuracion->tamaño_fuente + 2 }}px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .facturas-title::before {
            content: '📄';
            font-size: {{ $configuracion->tamaño_fuente + 4 }}px;
        }
        
        .factura-item {
            background: white;
            border: 1px solid #fbb6ce;
            border-radius: 6px;
            padding: 10px 15px;
            margin: 8px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .factura-numero {
            font-weight: 600;
            color: #2d3748;
        }
        
        .factura-monto {
            font-weight: 700;
            color: #c53030;
            font-size: {{ $configuracion->tamaño_fuente + 1 }}px;
        }
        
        .total-section {
            background: linear-gradient(135deg, {{ $configuracion->colores['header'] ?? '#667eea' }}, {{ $configuracion->colores['accent'] ?? '#764ba2' }});
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .total-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            animation: float 20s linear infinite;
        }
        
        @keyframes float {
            0% { transform: translateX(-50%) translateY(-50%) rotate(0deg); }
            100% { transform: translateX(-50%) translateY(-50%) rotate(360deg); }
        }
        
        .total-label {
            font-size: {{ $configuracion->tamaño_fuente + 2 }}px;
            font-weight: 300;
            opacity: 0.9;
            margin-bottom: 5px;
            position: relative;
            z-index: 1;
        }
        
        .total-amount {
            font-size: {{ $configuracion->tamaño_fuente + 8 }}px;
            font-weight: 800;
            position: relative;
            z-index: 1;
        }
        
        .mensaje-card {
            background: linear-gradient(45deg, #edf2f7, #e2e8f0);
            border: 2px dashed {{ $configuracion->colores['accent'] ?? '#764ba2' }};
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
            font-style: italic;
            color: #4a5568;
        }
        
        .footer {
            background: #f7fafc;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            color: #718096;
            font-size: {{ $configuracion->tamaño_fuente - 2 }}px;
        }
        
        .footer-line {
            margin: 3px 0;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, {{ $configuracion->colores['header'] ?? '#667eea' }}, {{ $configuracion->colores['accent'] ?? '#764ba2' }});
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .print-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.3);
        }

        /* Print styles */
        @media print {
            body { 
                -webkit-print-color-adjust: exact; 
            }
            .container { 
                box-shadow: none; 
                border-radius: 0; 
            }
            .print-button {
                display: none;
            }
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
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">
        <i class="fas fa-print"></i> Imprimir Recibo
    </button>

    <div class="container">
        <div class="header-gradient">
            @if($configuracion->mostrar_logo && auth()->user()->empresa->logo)
            <div class="logo-container">
                <img src="{{ Storage::url('public/logos/' . auth()->user()->empresa->logo) }}" 
                     alt="Logo" class="logo">
            </div>
            @endif
            
            <div class="recibo-title">RECIBO DE PAGO</div>
            <div class="recibo-number">N° {{ $recibo->numero_recibo }}</div>
        </div>

        <div class="content">
            @if($configuracion->mensaje_superior)
            <div class="mensaje-card">
                {{ $configuracion->mensaje_superior }}
            </div>
            @endif

            <div class="empresa-info">
                <div class="empresa-nombre">{{ auth()->user()->empresa->nombre }}</div>
                
                @if($configuracion->mostrar_direccion_empresa && auth()->user()->empresa->direccion)
                <div class="info-line">📍 {{ auth()->user()->empresa->direccion }}</div>
                @endif
                
                @if($configuracion->mostrar_telefono_empresa && auth()->user()->empresa->telefono)
                <div class="info-line">📞 {{ auth()->user()->empresa->telefono }}</div>
                @endif
                
                @if($configuracion->mostrar_email_empresa && auth()->user()->empresa->email)
                <div class="info-line">✉️ {{ auth()->user()->empresa->email }}</div>
                @endif
            </div>

            <div class="info-cards">
                <div class="info-card">
                    <div class="card-title">Información del Pago</div>
                    @if($configuracion->mostrar_fecha)
                    <div class="info-row">
                        <span class="info-label">Fecha:</span>
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
                        <span class="info-label">Método:</span>
                        <span class="info-value">{{ $recibo->pago->metodoPago->nombre ?? 'Efectivo' }}</span>
                    </div>
                </div>

                <div class="info-card">
                    <div class="card-title">Datos del Cliente</div>
                    <div class="info-row">
                        <span class="info-label">Nombre:</span>
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

                @if($recibo->pago->cobrador)
                <div class="info-card">
                    <div class="card-title">Información Adicional</div>
                    <div class="info-row">
                        <span class="info-label">Cobrador:</span>
                        <span class="info-value">{{ $recibo->pago->cobrador->nombre }}</span>
                    </div>
                </div>
                @endif
            </div>

            @if($recibo->facturas_pagadas && count($recibo->facturas_pagadas) > 0)
            <div class="facturas-section">
                <div class="facturas-title">Facturas Canceladas</div>
                @foreach($recibo->facturas_pagadas as $facturaPagada)
                <div class="factura-item">
                    <span class="factura-numero">{{ $facturaPagada['numero'] }}</span>
                    <span class="factura-monto">Gs. {{ number_format($facturaPagada['monto'], 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
            @endif

            <div class="total-section">
                <div class="total-label">TOTAL PAGADO</div>
                <div class="total-amount">Gs. {{ number_format($recibo->pago->monto_pagado, 0, ',', '.') }}</div>
            </div>

            @if($configuracion->mensaje_inferior)
            <div class="mensaje-card">
                {{ $configuracion->mensaje_inferior }}
            </div>
            @endif

            @if($configuracion->terminos_condiciones)
            <div class="mensaje-card">
                <small>{{ $configuracion->terminos_condiciones }}</small>
            </div>
            @endif
        </div>

        <div class="footer">
            <div class="footer-line">Documento generado electrónicamente</div>
            @if($configuracion->mostrar_fecha && $configuracion->mostrar_hora)
            <div class="footer-line">{{ now()->format('d/m/Y H:i:s') }}</div>
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