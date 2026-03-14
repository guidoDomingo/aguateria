<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Recibo {{ $recibo->numero_recibo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 15px;
            background: white;
            font-size: 12px;
        }
        .recibo {
            max-width: 350px;
            margin: 0 auto;
            padding: 15px;
            background: white;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #333;
            padding-bottom: 8px;
        }
        .empresa {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .direccion {
            font-size: 10px;
            color: #666;
        }
        .titulo {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 15px 0;
        }
        .datos {
            margin: 10px 0;
        }
        .fila {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .label {
            font-weight: bold;
            width: 35%;
            font-size: 11px;
        }
        .valor {
            width: 65%;
            font-size: 11px;
        }
        .monto {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            background: #f5f5f5;
            padding: 8px;
            margin: 15px 0;
            border: 2px solid #333;
        }
        .footer {
            text-align: center;
            font-size: 9px;
            color: #666;
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        .firma {
            text-align: center;
            margin-top: 25px;
        }
        .linea-firma {
            border-top: 1px solid #333;
            width: 150px;
            margin: 0 auto 3px auto;
        }
        @media print {
            body {
                margin: 0;
                padding: 5px;
                font-size: 11px;
            }
            .recibo {
                padding: 10px;
            }
        }
        @page {
            margin: 0.5cm;
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
    <div class="recibo">
        <div class="header">
            <div class="empresa">{{ $recibo->empresa_nombre }}</div>
            <div class="direccion">{{ $recibo->empresa_direccion }}</div>
            @if($recibo->empresa_telefono && $recibo->empresa_telefono !== 'N/A')
            <div class="direccion">Tel: {{ $recibo->empresa_telefono }}</div>
            @endif
        </div>

        <div class="titulo">RECIBO DE PAGO</div>

        <div class="datos">
            <div class="fila">
                <span class="label">Recibo N°:</span>
                <span class="valor">{{ $recibo->numero_recibo }}</span>
            </div>
            <div class="fila">
                <span class="label">Fecha:</span>
                <span class="valor">{{ $recibo->fecha_pago->format('d/m/Y') }}</span>
            </div>
            <div class="fila">
                <span class="label">Cliente:</span>
                <span class="valor">{{ $recibo->cliente_nombre }}</span>
            </div>
            @if($recibo->cliente_cedula)
            <div class="fila">
                <span class="label">Cédula:</span>
                <span class="valor">{{ $recibo->cliente_cedula }}</span>
            </div>
            @endif
            <div class="fila">
                <span class="label">Dirección:</span>
                <span class="valor">{{ Str::limit($recibo->cliente_direccion, 30) }}</span>
            </div>
            <div class="fila">
                <span class="label">Período:</span>
                <span class="valor">{{ $recibo->periodo_pagado }}</span>
            </div>
            <div class="fila">
                <span class="label">Método:</span>
                <span class="valor">{{ $recibo->metodo_pago }}</span>
            </div>
            @if($recibo->referencia)
            <div class="fila">
                <span class="label">Referencia:</span>
                <span class="valor">{{ $recibo->referencia }}</span>
            </div>
            @endif
        </div>

        <div class="monto">
            {{ $recibo->monto_formateado }}
        </div>

        <div class="firma">
            <div class="linea-firma"></div>
            <div style="font-size: 10px;">Firma y Sello</div>
        </div>

        <div class="footer">
            <div>Gracias por su pago</div>
            <div>{{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>
</body>
</html>