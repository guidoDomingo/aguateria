<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Tarifas</h1>
                <p class="text-gray-600">Gestiona los planes de precios del servicio</p>
            </div>
            <a href="{{ route('tarifas.crear') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <i class="fas fa-plus"></i>
                Nueva Tarifa
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Búsqueda -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <input type="text" 
                       wire:model.live="search" 
                       placeholder="Código, nombre o descripción..."
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Estado -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select wire:model.live="estado_filtro" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos los estados</option>
                    <option value="activa">Activa</option>
                    <option value="inactiva">Inactiva</option>
                </select>
            </div>
        </div>

        @if($search || $estado_filtro)
            <div class="mt-3 pt-3 border-t">
                <button wire:click="limpiarFiltros" 
                        class="text-red-600 hover:text-red-800 text-sm flex items-center gap-1">
                    <i class="fas fa-times"></i>
                    Limpiar filtros
                </button>
            </div>
        @endif
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tarifa
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Monto Mensual
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Mora
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Vencimiento
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($tarifas as $tarifa)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $tarifa->nombre }}</div>
                                    <div class="text-sm text-gray-500">{{ $tarifa->codigo }}</div>
                                    @if($tarifa->descripcion)
                                        <div class="text-xs text-gray-400">{{ Str::limit($tarifa->descripcion, 40) }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">
                                    Gs. {{ number_format($tarifa->monto_mensual, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($tarifa->genera_mora)
                                    <div class="text-sm text-gray-900">
                                        @if($tarifa->tipo_mora === 'porcentaje')
                                            {{ $tarifa->monto_mora }}%
                                        @else
                                            Gs. {{ number_format($tarifa->monto_mora, 0, ',', '.') }}
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $tarifa->dias_gracia }} días de gracia
                                    </div>
                                @else
                                    <span class="text-sm text-gray-500">Sin mora</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $tarifa->dias_vencimiento }} días</div>
                                @if($tarifa->corte_automatico)
                                    <div class="text-xs text-red-500">
                                        Corte a los {{ $tarifa->dias_corte }} días
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($tarifa->estado === 'activa')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Activa
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Inactiva
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('tarifas.editar', $tarifa->id) }}" 
                                       class="text-blue-600 hover:text-blue-900" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    @if($tarifa->estado === 'activa')
                                        <button wire:click="desactivar({{ $tarifa->id }})"
                                                onclick="confirm('¿Desactivar esta tarifa?') || event.stopImmediatePropagation()"
                                                class="text-red-600 hover:text-red-900" title="Desactivar">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @else
                                        <button wire:click="activar({{ $tarifa->id }})"
                                                class="text-green-600 hover:text-green-900" title="Activar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-dollar-sign text-4xl mb-3 text-gray-300"></i>
                                    <p class="font-medium">No hay tarifas registradas</p>
                                    <p class="text-sm">Comienza creando la primera tarifa</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($tarifas->hasPages())
            <div class="border-t border-gray-200 px-6 py-3">
                {{ $tarifas->links() }}
            </div>
        @endif
    </div>
</div>