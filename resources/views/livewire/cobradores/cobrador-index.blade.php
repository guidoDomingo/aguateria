<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Cobradores</h1>
                <p class="text-gray-600">Gestiona el personal de cobranza</p>
            </div>
            <a href="{{ route('cobradores.crear') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <i class="fas fa-plus"></i>
                Nuevo Cobrador
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Búsqueda -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <input type="text" 
                       wire:model.live="search" 
                       placeholder="Nombre, apellido, cédula, código o teléfono..."
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Zona -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Zona</label>
                <select wire:model.live="zona_filtro" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas las zonas</option>
                    @foreach($zonas as $zona)
                        <option value="{{ $zona->id }}">{{ $zona->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Estado -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select wire:model.live="estado_filtro" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos los estados</option>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                    <option value="suspendido">Suspendido</option>
                </select>
            </div>
        </div>

        @if($search || $zona_filtro || $estado_filtro)
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
                            Cobrador
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Contacto
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Zona
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Comisión
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
                    @forelse($cobradores as $cobrador)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $cobrador->nombre }} {{ $cobrador->apellido }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $cobrador->codigo }} • C.I: {{ $cobrador->cedula }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($cobrador->telefono)
                                        <div><i class="fas fa-phone text-gray-400 mr-1"></i>{{ $cobrador->telefono }}</div>
                                    @endif
                                    @if($cobrador->email)
                                        <div><i class="fas fa-envelope text-gray-400 mr-1"></i>{{ $cobrador->email }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">
                                    {{ $cobrador->zona->nombre ?? 'Sin zona' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($cobrador->comision_porcentaje > 0)
                                        <div>{{ number_format($cobrador->comision_porcentaje, 2) }}%</div>
                                    @endif
                                    @if($cobrador->comision_fija > 0)
                                        <div>Gs. {{ number_format($cobrador->comision_fija) }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $estadoClases = [
                                        'activo' => 'bg-green-100 text-green-800',
                                        'inactivo' => 'bg-gray-100 text-gray-800',
                                        'suspendido' => 'bg-red-100 text-red-800'
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $estadoClases[$cobrador->estado] }}">
                                    {{ ucfirst($cobrador->estado) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('cobradores.editar', $cobrador->id) }}" 
                                       class="text-blue-600 hover:text-blue-900" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    @if($cobrador->estado === 'activo')
                                        <button wire:click="eliminar({{ $cobrador->id }})"
                                                onclick="confirm('¿Desactivar este cobrador?') || event.stopImmediatePropagation()"
                                                class="text-red-600 hover:text-red-900" title="Desactivar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @else
                                        <button wire:click="activar({{ $cobrador->id }})"
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
                                    <i class="fas fa-user-tie text-4xl mb-3 text-gray-300"></i>
                                    <p class="font-medium">No hay cobradores registrados</p>
                                    <p class="text-sm">Comienza creando el primer cobrador</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($cobradores->hasPages())
            <div class="border-t border-gray-200 px-6 py-3">
                {{ $cobradores->links() }}
            </div>
        @endif
    </div>

    <!-- Información adicional -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-500"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Información sobre cobradores</h3>
                <div class="mt-1 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Los cobradores pueden tener asignados clientes para la gestión de pagos</li>
                        <li>Se pueden configurar comisiones por porcentaje o monto fijo</li>
                        <li>Un cobrador puede estar vinculado a un usuario del sistema</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>