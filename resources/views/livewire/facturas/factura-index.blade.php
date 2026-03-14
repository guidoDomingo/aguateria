<div>
    <!-- Header -->
    <div class="bg-white p-6 rounded-lg shadow-sm border mb-6">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestión de Facturas</h1>
                <p class="text-gray-600">Administra la facturación de tu empresa</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <!-- Facturación Automática -->
                <div class="relative">
                    <button id="dropdown-button" 
                            type="button"
                            style="background-color: #16a34a; color: white;"
                            class="px-4 py-2 rounded-lg font-medium flex items-center hover:opacity-90 transition-opacity">
                        ⚡ Facturación Automática ▼
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div id="dropdown-menu" 
                         style="display: none; position: absolute; top: 100%; left: 0; margin-top: 8px; width: 280px; background: white; border: 1px solid #d1d5db; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); z-index: 50;">
                        
                        <!-- Período Actual -->
                        <div style="padding: 16px; border-bottom: 1px solid #f3f4f6; background-color: #f9fafb;">
                            <div style="font-size: 12px; color: #6b7280; font-weight: 600; margin-bottom: 4px;">PERÍODO ACTUAL</div>
                            <div style="font-size: 14px; font-weight: bold; color: #111827;">
                                {{ $fechaActual->locale('es')->isoFormat('MMMM YYYY') }}
                            </div>
                            @if($infoPeriodoActual['listo'])
                                <div style="font-size: 12px; color: #16a34a; margin-top: 8px;">
                                    ✓ {{ $infoPeriodoActual['mensaje'] }}
                                </div>
                            @else
                                <div style="font-size: 12px; color: #dc2626; margin-top: 8px;">
                                    ⚠ {{ $infoPeriodoActual['mensaje'] }}
                                </div>
                            @endif
                        </div>
                        
                        <!-- Opciones -->
                        <div style="padding: 8px;">
                            <button wire:click="procesarFacturacionAutomatica" 
                                    {{ !$infoPeriodoActual['listo'] || $procesandoFacturacion ? 'disabled' : '' }}
                                    style="width: 100%; text-align: left; padding: 12px; border-radius: 6px; display: flex; align-items: center; border: none; background: transparent; cursor: pointer;"
                                    onmouseover="this.style.backgroundColor='#f0f9f0'"
                                    onmouseout="this.style.backgroundColor='transparent'">
                                <div style="width: 32px; height: 32px; background-color: #dcfce7; border-radius: 6px; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                    📅
                                </div>
                                <div>
                                    <div style="font-weight: 500; color: #111827;">Mes Actual</div>
                                    <div style="font-size: 12px; color: #6b7280;">{{ $fechaActual->locale('es')->isoFormat('MMMM YYYY') }}</div>
                                </div>
                            </button>
                            
                            <button wire:click="procesarFacturacionMesSiguiente" 
                                    {{ $procesandoFacturacion ? 'disabled' : '' }}
                                    style="width: 100%; text-align: left; padding: 12px; border-radius: 6px; display: flex; align-items: center; border: none; background: transparent; cursor: pointer;"
                                    onmouseover="this.style.backgroundColor='#f0f4ff'"
                                    onmouseout="this.style.backgroundColor='transparent'">
                                <div style="width: 32px; height: 32px; background-color: #dbeafe; border-radius: 6px; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                    ➕
                                </div>
                                <div>
                                    <div style="font-weight: 500; color: #111827;">Próximo Mes</div>
                                    <div style="font-size: 12px; color: #6b7280;">{{ $fechaActual->copy()->addMonth()->locale('es')->isoFormat('MMMM YYYY') }}</div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
                
                <button wire:click="abrirModalFacturacion" 
                        type="button"
                        style="background-color: #4f46e5; color: white;"
                        class="px-4 py-2 rounded-lg font-medium hover:opacity-90 transition-opacity">
                    📋 Facturación Manual
                </button>
                
                <a href="{{ route('facturas.create') }}" 
                   style="background-color: #2563eb; color: white;"
                   class="px-4 py-2 rounded-lg font-medium hover:opacity-90 transition-opacity text-decoration-none">
                    ➕ Nueva Factura
                </a>
                
                <button wire:click="aplicarMoras" 
                        type="button"
                        style="background-color: #ea580c; color: white;"
                        class="px-4 py-2 rounded-lg font-medium hover:opacity-90 transition-opacity">
                    ⏰ Aplicar Moras
                </button>
            </div>
        </div>
    </div>

    <!-- Estadísticas del Período -->
    @if(!empty($estadisticas))
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-file-invoice text-lg"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-600">Total Facturas</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $estadisticas['total_facturas'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-dollar-sign text-lg"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-600">Monto Facturado</p>
                    <p class="text-xl font-semibold text-gray-900">{{ number_format($estadisticas['monto_total'] ?? 0, 0, ',', '.') }} Gs.</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check-circle text-lg"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-600">Pagadas</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $estadisticas['facturas_pagadas'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-exclamation-triangle text-lg"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-600">Pendientes</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $estadisticas['facturas_pendientes'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filtros y Búsqueda -->
    <div class="bg-white p-6 rounded-lg shadow-sm border mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Búsqueda -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Buscar
                </label>
                <input wire:model.live.debounce.300ms="buscar" 
                       type="text" 
                       placeholder="Cliente, número..."
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Filtro por Estado -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Estado
                </label>
                <select wire:model.live="filtroEstado" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="todas">Todas</option>
                    <option value="pendiente">Pendientes</option>
                    <option value="pagado">Pagadas</option>
                    <option value="vencido">Vencidas</option>
                    <option value="parcial">Parciales</option>
                    <option value="consolidado">Consolidadas</option>
                    <option value="anulado">Anuladas</option>
                </select>
            </div>

            <!-- Filtro por Período -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Período
                </label>
                <select wire:model.live="filtroPeriodo" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos los períodos</option>
                    @foreach($periodos as $periodo)
                        <option value="{{ $periodo->id }}">
                            {{ \Carbon\Carbon::createFromDate($periodo->año, $periodo->mes, 1)->locale('es')->isoFormat('MMMM YYYY') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Opciones adicionales -->
            <div class="flex flex-col justify-end">
                <label class="flex items-center">
                    <input wire:model.live="mostrarSoloVencidas" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:border-blue-500 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-600">Solo vencidas</span>
                </label>
            </div>
        </div>

        <!-- Botón limpiar filtros -->
        <div class="flex justify-end mt-4">
            <button wire:click="limpiarFiltros" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                <i class="fas fa-broom mr-1"></i>
                Limpiar filtros
            </button>
        </div>
    </div>

    <!-- Tabla de Facturas -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            N° Factura
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cliente
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Período
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Monto
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Vencimiento
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($facturas as $factura)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    #{{ str_pad($factura->numero_factura, 6, '0', STR_PAD_LEFT) }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $factura->fecha_emision->format('d/m/Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $factura->cliente->nombre }} {{ $factura->cliente->apellido }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $factura->cliente->cedula }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($factura->periodo)
                                    {{ \Carbon\Carbon::createFromDate($factura->periodo->año, $factura->periodo->mes, 1)->locale('es')->isoFormat('MMM YYYY') }}
                                @else
                                    <span class="text-gray-400">Sin período</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ number_format($factura->total, 0, ',', '.') }} Gs.
                                </div>
                                @if($factura->monto_pagado > 0)
                                    <div class="text-xs text-green-600">
                                        Pagado: {{ number_format($factura->monto_pagado, 0, ',', '.') }} Gs.
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ $factura->fecha_vencimiento->format('d/m/Y') }}
                                </div>
                                @if($factura->fecha_vencimiento->isPast() && $factura->estado !== 'pagado')
                                    <div class="text-xs text-red-600">
                                        Vencida ({{ $factura->fecha_vencimiento->diffInDays(now()) }} días)
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @switch($factura->estado)
                                    @case('pendiente')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            Pendiente
                                        </span>
                                        @break
                                    @case('pagado')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Pagada
                                        </span>
                                        @break
                                    @case('vencido')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Vencida
                                        </span>
                                        @break
                                    @case('parcial')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-adjust mr-1"></i>
                                            Parcial
                                        </span>
                                        @break
                                    @case('anulado')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Anulada
                                        </span>
                                        @break
                                    @case('consolidado')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <i class="fas fa-layer-group mr-1"></i>
                                            Consolidada
                                        </span>
                                        @break
                                @endswitch
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <!-- Ver/Imprimir -->
                                    <button wire:click="verFactura({{ $factura->id }})" 
                                            title="Ver factura" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <!-- PDF -->
                                    <button wire:click="descargarPdf({{ $factura->id }})" 
                                            title="Descargar PDF" class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                    
                                    <!-- Imprimir -->
                                    <button wire:click="imprimirFactura({{ $factura->id }})" 
                                            title="Imprimir factura" class="text-purple-600 hover:text-purple-900">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    
                                    @if($factura->estado !== 'pagado' && $factura->estado !== 'anulado')
                                        <!-- Editar -->
                                        <a href="{{ route('facturas.edit', $factura->id) }}" 
                                           title="Editar" class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <!-- Anular -->
                                        <button wire:click="anularFactura({{ $factura->id }})" 
                                                title="Anular" class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('¿Está seguro de anular esta factura?')">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-file-invoice text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-lg font-medium">No hay facturas</p>
                                    <p class="text-sm">No se encontraron facturas con los filtros aplicados</p>
                                    <div class="mt-4 flex gap-2">
                                        <a href="{{ route('facturas.create') }}" 
                                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                            <i class="fas fa-plus mr-2"></i>
                                            Nueva Factura
                                        </a>
                                        <button wire:click="limpiarFiltros" 
                                                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                                            <i class="fas fa-broom mr-2"></i>
                                            Limpiar Filtros
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($facturas->hasPages())
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                {{ $facturas->links() }}
            </div>
        @endif
    </div>

    <!-- Modal de Facturación Masiva -->
    @if($mostrarModalFacturacion)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-full pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cerrarModalFacturacion"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-file-invoice text-green-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                    Facturación Manual por Período
                                </h3>
                                
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Seleccionar Período Específico
                                    </label>
                                    <select wire:model="periodoSeleccionado" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Seleccione un período...</option>
                                        @foreach($periodos as $periodo)
                                            <option value="{{ $periodo->id }}">
                                                {{ \Carbon\Carbon::createFromDate($periodo->año, $periodo->mes, 1)->locale('es')->isoFormat('MMMM YYYY') }}
                                                ({{ ucfirst($periodo->estado) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="text-sm text-gray-500 bg-blue-50 p-3 rounded-lg">
                                    <p class="mb-2"><strong><i class="fas fa-info-circle mr-1"></i> Facturación Manual:</strong></p>
                                    <p>• Permite seleccionar cualquier período específico</p>
                                    <p>• Útil para facturación de períodos anteriores o futuros</p>
                                    <p>• Se generarán facturas para todos los clientes activos del período seleccionado</p>
                                </div>
                                
                                <div class="text-sm text-green-600 bg-green-50 p-3 rounded-lg mt-3">
                                    <p><strong><i class="fas fa-lightbulb mr-1"></i> Sugerencia:</strong></p>
                                    <p>Para facturar el mes actual automáticamente, use el botón "Facturación Automática" en la parte superior.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="procesarFacturacionMasiva" 
                                {{ !$periodoSeleccionado || $procesandoFacturacion ? 'disabled' : '' }}
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            @if($procesandoFacturacion)
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                Procesando...
                            @else
                                <i class="fas fa-play mr-2"></i>
                                Generar Facturas
                            @endif
                        </button>
                        <button wire:click="cerrarModalFacturacion" 
                                {{ $procesandoFacturacion ? 'disabled' : '' }}
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Mensajes Flash -->
    @if (session()->has('message'))
        <div class="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" 
             x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 5000)">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm">{{ session('message') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed top-4 right-4 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" 
             x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 5000)">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- JavaScript para el dropdown -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdownButton = document.getElementById('dropdown-button');
    const dropdownMenu = document.getElementById('dropdown-menu');
    
    if (dropdownButton && dropdownMenu) {
        dropdownButton.addEventListener('click', function(e) {
            e.stopPropagation();
            if (dropdownMenu.style.display === 'none' || dropdownMenu.style.display === '') {
                dropdownMenu.style.display = 'block';
            } else {
                dropdownMenu.style.display = 'none';
            }
        });

        // Cerrar al hacer click fuera
        document.addEventListener('click', function(event) {
            if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.style.display = 'none';
            }
        });

        // Cerrar dropdown cuando se ejecuta una acción de Livewire
        document.addEventListener('livewire:request', function() {
            dropdownMenu.style.display = 'none';
        });
    }
});
</script>