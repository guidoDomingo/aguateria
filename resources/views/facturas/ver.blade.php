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
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border mb-6 p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Factura #{{ str_pad($factura->numero_factura, 6, '0', STR_PAD_LEFT) }}</h1>
                    <p class="text-gray-600">{{ $factura->fecha_emision->format('d/m/Y') }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('facturas.pdf', $factura->id) }}" 
                       target="_blank"
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-file-pdf mr-2"></i>
                        Descargar PDF
                    </a>
                    <a href="{{ route('facturas.imprimir', $factura->id) }}" 
                       target="_blank"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-print mr-2"></i>
                        Imprimir
                    </a>
                    <a href="{{ route('facturas.index') }}" 
                       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver
                    </a>
                </div>
            </div>

            <!-- Info de la empresa -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Empresa</h3>
                    <div class="text-sm text-gray-600">
                        <p class="font-medium">{{ $factura->empresa->nombre }}</p>
                        <p>{{ $factura->empresa->direccion }}</p>
                        @if($factura->empresa->telefono)
                        <p>Tel: {{ $factura->empresa->telefono }}</p>
                        @endif
                        @if($factura->empresa->email)
                        <p>Email: {{ $factura->empresa->email }}</p>
                        @endif
                    </div>
                </div>
                
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Cliente</h3>
                    <div class="text-sm text-gray-600">
                        <p class="font-medium">{{ $factura->cliente->nombre }} {{ $factura->cliente->apellido }}</p>
                        <p>{{ $factura->cliente->cedula }}</p>
                        <p>{{ $factura->cliente->direccion }}</p>
                        @if($factura->cliente->telefono)
                        <p>Tel: {{ $factura->cliente->telefono }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Detalles de la factura -->
            <div class="border-t pt-6">
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <span class="text-sm font-medium text-gray-600">Período:</span>
                        <span class="ml-2">
                            @if($factura->periodo)
                                {{ \Carbon\Carbon::createFromDate($factura->periodo->año, $factura->periodo->mes, 1)->locale('es')->isoFormat('MMMM YYYY') }}
                            @else
                                Sin período
                            @endif
                        </span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Vencimiento:</span>
                        <span class="ml-2 {{ $factura->fecha_vencimiento < now() ? 'text-red-600 font-semibold' : '' }}">
                            {{ $factura->fecha_vencimiento->format('d/m/Y') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Estado:</span>
                        <span class="ml-2">
                            @switch($factura->estado)
                                @case('pendiente')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Pendiente
                                    </span>
                                    @break
                                @case('pagado')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Pagada
                                    </span>
                                    @break
                                @case('vencido')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Vencida
                                    </span>
                                    @break
                                @case('parcial')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Pago Parcial
                                    </span>
                                    @break
                                @case('anulado')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Anulada
                                    </span>
                                    @break
                            @endswitch
                        </span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Total:</span>
                        <span class="ml-2 text-lg font-bold text-gray-900">
                            {{ number_format($factura->total, 0, ',', '.') }} Gs.
                        </span>
                    </div>
                </div>

                <!-- Detalles/Conceptos -->
                @if($factura->detalles && $factura->detalles->count() > 0)
                <div class="mt-6">
                    <h4 class="font-semibold text-gray-900 mb-3">Detalles de la Factura</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Concepto</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Precio Unit.</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($factura->detalles as $detalle)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $detalle->concepto }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $detalle->descripcion }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ $detalle->cantidad }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($detalle->precio_unitario, 0, ',', '.') }} Gs.</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ number_format($detalle->subtotal, 0, ',', '.') }} Gs.</td>
                                </tr>
                                @endforeach
                                <tr class="bg-gray-50">
                                    <td colspan="4" class="px-4 py-3 text-right font-semibold text-gray-900">Total:</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 text-lg">
                                        {{ number_format($factura->total, 0, ',', '.') }} Gs.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Información de pago -->
                @if($factura->monto_pagado > 0)
                <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <h4 class="font-semibold text-green-900 mb-2">Información de Pago</h4>
                    <div class="text-sm text-green-800">
                        <p><strong>Monto Pagado:</strong> {{ number_format($factura->monto_pagado, 0, ',', '.') }} Gs.</p>
                        <p><strong>Saldo Pendiente:</strong> {{ number_format($factura->saldo_pendiente, 0, ',', '.') }} Gs.</p>
                        @if($factura->fecha_pago)
                        <p><strong>Fecha de Pago:</strong> {{ $factura->fecha_pago->format('d/m/Y') }}</p>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Observaciones -->
                @if($factura->observaciones)
                <div class="mt-6">
                    <h4 class="font-semibold text-gray-900 mb-2">Observaciones</h4>
                    <div class="p-3 bg-gray-50 border rounded-lg text-sm text-gray-600">
                        {{ $factura->observaciones }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>