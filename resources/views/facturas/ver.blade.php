<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $factura->numero_factura }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
<div class="max-w-4xl mx-auto py-8 px-4">

    <!-- Acciones -->
    <div class="flex justify-between items-center mb-4">
        <a href="{{ route('facturas.index') }}" class="text-gray-600 hover:text-gray-900 flex items-center gap-1 text-sm">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
        <div class="flex gap-2">
            <a href="{{ route('facturas.pdf', $factura->id) }}" target="_blank"
               class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                <i class="fas fa-file-pdf mr-1"></i> PDF
            </a>
            <a href="{{ route('facturas.imprimir', $factura->id) }}" target="_blank"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                <i class="fas fa-print mr-1"></i> Imprimir
            </a>
        </div>
    </div>

    <!-- Cabecera factura -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden mb-6">
        <div class="px-6 py-4 flex justify-between items-start" style="background-color: {{ $configuracion->colores['header'] ?? '#2563eb' }}">
            <div class="text-white">
                <p class="text-sm opacity-80">{{ $factura->empresa->nombre }}</p>
                @if($configuracion->mostrar_direccion_empresa && $factura->empresa->direccion)
                    <p class="text-xs opacity-70">{{ $factura->empresa->direccion }}</p>
                @endif
                @if($configuracion->mostrar_telefono_empresa && $factura->empresa->telefono)
                    <p class="text-xs opacity-70">Tel: {{ $factura->empresa->telefono }}</p>
                @endif
                @if($configuracion->mostrar_email_empresa && $factura->empresa->email)
                    <p class="text-xs opacity-70">{{ $factura->empresa->email }}</p>
                @endif
            </div>
            <div class="text-right text-white">
                <p class="text-2xl font-bold">#{{ $factura->numero_factura }}</p>
                <p class="text-sm opacity-80">{{ $factura->fecha_emision->format('d/m/Y') }}</p>
                @if($factura->aviso === 'desconexion')
                    <span class="inline-block mt-1 px-2 py-0.5 bg-red-200 text-red-900 text-xs rounded font-semibold">⚠ DESCONEXIÓN</span>
                @elseif($factura->aviso === 'ultimo_aviso')
                    <span class="inline-block mt-1 px-2 py-0.5 bg-yellow-200 text-yellow-900 text-xs rounded font-semibold">🔔 ÚLTIMO AVISO</span>
                @endif
            </div>
        </div>

        <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Datos de la factura -->
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">Datos de la Factura</h3>
                <div class="space-y-1 text-sm text-gray-700">
                    <div class="flex gap-2">
                        <span class="text-gray-500 w-28">Período:</span>
                        <span>
                            @if($factura->periodo)
                                {{ \Carbon\Carbon::createFromDate($factura->periodo->año, $factura->periodo->mes, 1)->locale('es')->isoFormat('MMMM YYYY') }}
                            @else Sin período @endif
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-gray-500 w-28">Vencimiento:</span>
                        <span class="{{ $factura->fecha_vencimiento->isPast() && $factura->estado !== 'pagado' ? 'text-red-600 font-semibold' : '' }}">
                            {{ $factura->fecha_vencimiento->format('d/m/Y') }}
                            @if($factura->fecha_vencimiento->isPast() && $factura->estado !== 'pagado')
                                ({{ $factura->fecha_vencimiento->diffInDays(now()) }} días vencida)
                            @endif
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-gray-500 w-28">Estado:</span>
                        @switch($factura->estado)
                            @case('pendiente')
                                <span class="px-2 py-0.5 rounded-full text-xs bg-yellow-100 text-yellow-800 font-medium">Pendiente</span>@break
                            @case('vencido')
                                <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-800 font-medium">Vencida</span>@break
                            @case('pagado')
                                <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-800 font-medium">Pagada</span>@break
                            @case('parcial')
                                <span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-800 font-medium">Pago parcial</span>@break
                            @case('anulado')
                                <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-800 font-medium">Anulada</span>@break
                        @endswitch
                    </div>
                </div>
            </div>

            <!-- Datos del cliente -->
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">Cliente</h3>
                <div class="text-sm text-gray-700 space-y-1">
                    <p class="font-semibold">{{ $factura->cliente->nombre }} {{ $factura->cliente->apellido }}</p>
                    <p class="text-gray-500">CI: {{ $factura->cliente->cedula }}</p>
                    @if($factura->cliente->direccion)
                        <p>{{ $factura->cliente->direccion }}</p>
                    @endif
                    @if($factura->cliente->telefono)
                        <p>Tel: {{ $factura->cliente->telefono }}</p>
                    @endif
                    @if($factura->cliente->barrio)
                        <p>Barrio: {{ $factura->cliente->barrio->nombre }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Montos -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Resumen de la Factura</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">Subtotal</span>
                <span>{{ number_format($factura->subtotal, 0, ',', '.') }} Gs.</span>
            </div>
            @if($factura->descuento > 0)
            <div class="flex justify-between text-green-700">
                <span>Descuento</span>
                <span>- {{ number_format($factura->descuento, 0, ',', '.') }} Gs.</span>
            </div>
            @endif
            @if($factura->mora > 0)
            <div class="flex justify-between text-orange-700">
                <span>Mora</span>
                <span>+ {{ number_format($factura->mora, 0, ',', '.') }} Gs.</span>
            </div>
            @endif
            <div class="flex justify-between font-bold text-base border-t pt-2 mt-2">
                <span>Total esta factura</span>
                <span>{{ number_format($factura->total, 0, ',', '.') }} Gs.</span>
            </div>

            @php
                // "Ya pagado" = lo que realmente se descontó del total (no la suma cruda de pagos)
                $totalPagado = $factura->total - $factura->saldo_pendiente;
            @endphp
            @if($totalPagado > 0)
            <div class="flex justify-between text-green-700 font-medium">
                <span>Ya pagado</span>
                <span>- {{ number_format($totalPagado, 0, ',', '.') }} Gs.</span>
            </div>
            @endif
            <div class="flex justify-between font-bold text-base border-t pt-2 mt-1 {{ $factura->saldo_pendiente > 0 ? 'text-red-700' : 'text-green-700' }}">
                <span>{{ $factura->saldo_pendiente > 0 ? 'Saldo pendiente' : '✓ Factura cancelada' }}</span>
                <span>{{ number_format($factura->saldo_pendiente, 0, ',', '.') }} Gs.</span>
            </div>
        </div>

        @if($otrasFacturas->count() > 0)
        <div class="mt-4 pt-4 border-t border-red-100 bg-red-50 -mx-6 -mb-6 px-6 pb-6 rounded-b-lg">
            <h4 class="text-xs font-semibold text-red-700 uppercase mb-2 flex items-center gap-1">
                <i class="fas fa-exclamation-triangle"></i>
                {{ $otrasFacturas->count() }} {{ $otrasFacturas->count() === 1 ? 'factura pendiente adicional' : 'facturas pendientes adicionales' }} del cliente
            </h4>
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-red-600 border-b border-red-200">
                        <th class="text-left py-1">Factura</th>
                        <th class="text-left py-1">Vencimiento</th>
                        <th class="text-right py-1">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($otrasFacturas as $otra)
                    <tr class="border-b border-red-100">
                        <td class="py-1 font-medium">#{{ $otra->numero_factura }}</td>
                        <td class="py-1 text-red-600">
                            {{ $otra->fecha_vencimiento->format('d/m/Y') }}
                            @if($otra->fecha_vencimiento->isPast())
                                ({{ $otra->fecha_vencimiento->diffInDays(now()) }}d vencida)
                            @endif
                        </td>
                        <td class="py-1 text-right font-semibold">{{ number_format($otra->total, 0, ',', '.') }} Gs.</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($deudaTotal > 0)
            <div class="flex justify-between font-bold text-sm mt-3 pt-2 border-t border-red-300 text-red-800">
                <span>DEUDA TOTAL DEL CLIENTE</span>
                <span>{{ number_format($deudaTotal, 0, ',', '.') }} Gs.</span>
            </div>
            @endif
        </div>
        @endif
    </div>

    <!-- Historial de pagos -->
    @if($factura->pagos->count() > 0)
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Historial de Pagos</h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-gray-500 uppercase border-b">
                    <th class="text-left py-2">Fecha</th>
                    <th class="text-left py-2">Método</th>
                    <th class="text-left py-2">Ajustes</th>
                    <th class="text-right py-2">Cobrado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($factura->pagos as $pago)
                <tr class="border-b border-gray-100">
                    <td class="py-2">{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                    <td class="py-2">{{ $pago->metodoPago->nombre ?? '-' }}</td>
                    <td class="py-2 text-xs text-gray-500">
                        @if(($pago->mora_exonerada ?? 0) > 0)
                            <span class="text-orange-600">Mora exon.: {{ number_format($pago->mora_exonerada, 0, ',', '.') }} Gs.</span>
                        @endif
                        @if(($pago->descuento ?? 0) > 0)
                            <span class="text-purple-600 ml-1">Desc. {{ $pago->porcentaje_descuento ?? 0 }}%: {{ number_format($pago->descuento, 0, ',', '.') }} Gs.</span>
                        @endif
                        @if(($pago->mora_exonerada ?? 0) == 0 && ($pago->descuento ?? 0) == 0)
                            <span>-</span>
                        @endif
                    </td>
                    <td class="py-2 text-right font-medium text-green-700">{{ number_format($pago->monto_pagado, 0, ',', '.') }} Gs.</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="flex justify-between text-sm font-semibold mt-2 pt-2 border-t">
            <span>Total cobrado</span>
            <span class="text-green-700">{{ number_format($factura->total - $factura->saldo_pendiente, 0, ',', '.') }} Gs.</span>
        </div>
        @if($factura->saldo_pendiente > 0)
        <div class="flex justify-between text-sm font-semibold mt-1">
            <span>Saldo pendiente</span>
            <span class="text-red-700">{{ number_format($factura->saldo_pendiente, 0, ',', '.') }} Gs.</span>
        </div>
        @endif
    </div>
    @endif

    <!-- Detalles de servicio -->
    @if($factura->detalles && $factura->detalles->count() > 0)
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Detalle de Servicios</h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-gray-500 uppercase border-b">
                    <th class="text-left py-2">Concepto</th>
                    <th class="text-left py-2">Descripción</th>
                    <th class="text-right py-2">Cant.</th>
                    <th class="text-right py-2">Precio Unit.</th>
                    <th class="text-right py-2">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($factura->detalles as $detalle)
                <tr class="border-b border-gray-100">
                    <td class="py-2">{{ $detalle->concepto }}</td>
                    <td class="py-2 text-gray-500">{{ $detalle->descripcion }}</td>
                    <td class="py-2 text-right">{{ $detalle->cantidad }}</td>
                    <td class="py-2 text-right">{{ number_format($detalle->precio_unitario, 0, ',', '.') }} Gs.</td>
                    <td class="py-2 text-right font-medium">{{ number_format($detalle->subtotal, 0, ',', '.') }} Gs.</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($factura->observaciones)
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6 text-sm text-gray-600">
        <strong class="text-gray-700">Observaciones:</strong> {{ $factura->observaciones }}
    </div>
    @endif

    @if($configuracion->mensaje_inferior)
    <p class="text-center text-sm text-gray-500">{{ $configuracion->mensaje_inferior }}</p>
    @endif
</div>
</body>
</html>
