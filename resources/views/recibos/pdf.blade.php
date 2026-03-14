<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo {{ $recibo->numero_recibo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
        }
        .recibo {
            max-width: 400px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
            background: white;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .empresa {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .direccion {
            font-size: 12px;
            color: #666;
        }
        .titulo {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            color: #333;
        }
        .datos {
            margin: 15px 0;
        }
        .fila {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            padding: 2px 0;
        }
        .label {
            font-weight: bold;
            width: 40%;
        }
        .valor {
            width: 60%;
            text-align: left;
        }
        .monto {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            background: #f5f5f5;
            padding: 10px;
            margin: 20px 0;
            border: 2px solid #333;
        }
        .footer {
            text-align: center;
            font-size: 11px;
            color: #666;
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .firma {
            text-align: center;
            margin-top: 40px;
        }
        .linea-firma {
            border-top: 1px solid #333;
            width: 200px;
            margin: 0 auto 5px auto;
        }
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="recibo">
        <!-- Header de la empresa -->
        <div class="header">
            <div class="empresa">{{ $recibo->empresa_nombre }}</div>
            <div class="direccion">{{ $recibo->empresa_direccion }}</div>
            @if($recibo->empresa_telefono && $recibo->empresa_telefono !== 'N/A')
            <div class="direccion">Tel: {{ $recibo->empresa_telefono }}</div>
            @endif
        </div>

        <!-- Título -->
        <div class="titulo">RECIBO DE PAGO</div>

        <!-- Datos del recibo -->
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
                <span class="valor">{{ $recibo->cliente_direccion }}</span>
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

        <!-- Monto pagado -->
        <div class="monto">
            {{ $recibo->monto_formateado }}
        </div>

        @if($recibo->observaciones)
        <div class="datos">
            <div class="fila">
                <span class="label">Observaciones:</span>
            </div>
            <div style="margin-top: 5px; padding: 5px; background: #f9f9f9; font-size: 12px;">
                {{ $recibo->observaciones }}
            </div>
        </div>
        @endif

        <!-- Firma -->
        <div class="firma">
            <div class="linea-firma"></div>
            <div style="font-size: 12px;">Firma y Sello</div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>Gracias por su pago puntual</div>
            <div>Sistema de Gestión - {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>
</body>
</html>