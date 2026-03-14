<div>
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestión de Pagos</h1>
            <p class="text-gray-600">Registra y administra los pagos de clientes</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('pagos.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                <i class="fas fa-plus mr-2"></i>
                Registrar Pago
            </a>
        </div>
    </div>

    <!-- Estadísticas -->
    @if(!empty($estadisticas))
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-receipt text-lg"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-600">Total Pagos</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $estadisticas['cantidad_pagos'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-dollar-sign text-lg"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-600">Monto Total</p>
                    <p class="text-xl font-semibold text-gray-900">{{ number_format($estadisticas['monto_total'] ?? 0, 0, ',', '.') }} Gs.</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-credit-card text-lg"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-600">Promedio</p>
                    <p class="text-xl font-semibold text-gray-900">{{ number_format($estadisticas['promedio_pago'] ?? 0, 0, ',', '.') }} Gs.</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-orange-100 text-orange-600">
                    <i class="fas fa-file-invoice text-lg"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-600">Facturas Pagadas</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $estadisticas['facturas_pagadas'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filtros y Búsqueda -->
    <div class="bg-white p-6 rounded-lg shadow-sm border mb-6">
        <!-- Filtros de fecha predefinidos -->
        <div class="flex flex-wrap gap-2 mb-4">
            <button wire:click="establecerHoy" 
                    class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200 transition-colors">
                Hoy
            </button>
            <button wire:click="establecerSemana" 
                    class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200 transition-colors">
                Esta Semana
            </button>
            <button wire:click="establecerMes" 
                    class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200 transition-colors">
                Este Mes
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Búsqueda -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Buscar
                </label>
                <input wire:model.live.debounce.300ms="buscar" 
                       type="text" 
                       placeholder="Cliente, recibo..."
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Fecha Desde -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Desde
                </label>
                <input wire:model.live="fechaDesde" 
                       type="date" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Fecha Hasta -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Hasta
                </label>
                <input wire:model.live="fechaHasta" 
                       type="date" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Método de Pago -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Método de Pago
                </label>
                <select wire:model.live="filtroMetodo" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos</option>
                    @foreach($metodosPago as $metodo)
                        <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Cobrador -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Cobrador
                </label>
                <select wire:model.live="filtroCobrador" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos</option>
                    @foreach($cobradores as $cobrador)
                        <option value="{{ $cobrador->id }}">{{ $cobrador->nombre }}</option>
                    @endforeach
                </select>
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

    <!-- Tabla de Pagos -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            N° Recibo
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cliente
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Fecha
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Monto
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Método
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cobrador
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
                    @forelse($pagos as $pago)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    #{{ str_pad($pago->numero_recibo, 6, '0', STR_PAD_LEFT) }}
                                </div>
                                @if($pago->recibo)
                                    <div class="text-xs text-blue-600">
                                        <i class="fas fa-file-alt"></i> Con recibo
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $pago->cliente->nombre }} {{ $pago->cliente->apellido }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $pago->cliente->cedula }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ number_format($pago->monto_pagado, 0, ',', '.') }} Gs.
                                </div>
                                @if($pago->factura)
                                    <div class="text-xs text-gray-500">
                                        Factura {{ str_pad($pago->factura->numero_factura, 6, '0', STR_PAD_LEFT) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $pago->metodoPago->nombre ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $pago->cobrador->nombre ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($pago->estado === 'confirmado')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Confirmado
                                    </span>
                                @elseif($pago->estado === 'pendiente')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i>
                                        Pendiente
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Anulado
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <!-- Imprimir Recibo -->
                                    <button wire:click="imprimirRecibo({{ $pago->id }})" 
                                            title="Imprimir recibo" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    
                                    <!-- PDF del recibo -->
                                    <button wire:click="descargarPdf({{ $pago->id }})" 
                                            title="Descargar recibo" class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                    
                                    @if($pago->estado === 'confirmado')
                                        <!-- Anular -->
                                        <button wire:click="confirmarAnulacion({{ $pago->id }})" 
                                                title="Anular pago" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-receipt text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-lg font-medium">No hay pagos registrados</p>
                                    <p class="text-sm">No se encontraron pagos con los filtros aplicados</p>
                                    <div class="mt-4 flex gap-2">
                                        <a href="{{ route('pagos.create') }}" 
                                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                            <i class="fas fa-plus mr-2"></i>
                                            Registrar Pago
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
        @if($pagos->hasPages())
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                {{ $pagos->links() }}
            </div>
        @endif
    </div>

    <!-- Modal de Confirmación para Anular -->
    @if($mostrarModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-full pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cerrarModal"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Confirmar Anulación
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        ¿Estás seguro de que deseas anular este pago? Esta acción revertirá el pago de las facturas asociadas y no se puede deshacer.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="anularPago" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            <i class="fas fa-ban mr-2"></i>
                            Anular Pago
                        </button>
                        <button wire:click="cerrarModal" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Mensajes Flash -->
    @if (session()->has('pago_exitoso'))
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" 
             x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 8000)">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md mx-4 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-check text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">¡Pago Registrado Exitosamente!</h3>
                @php $pagoData = session('pago_exitoso'); @endphp
                <p class="text-gray-600 mb-4">{{ $pagoData['mensaje'] }}</p>
                
                @if(!empty($pagoData['vuelto']))
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                        <div class="flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-yellow-600 mr-2"></i>
                            <span class="font-semibold text-yellow-800">Vuelto: {{ $pagoData['vuelto'] }} Gs.</span>
                        </div>
                    </div>
                @endif
                
                <button @click="show = false" 
                        class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-check mr-2"></i>
                    Entendido
                </button>
            </div>
        </div>
    @endif

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