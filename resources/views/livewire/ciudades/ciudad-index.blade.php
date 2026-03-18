<div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div></div>
        <a href="{{ route('ciudades.crear') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-plus"></i>
            Nueva Ciudad
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <input type="text"
                       wire:model.live="search"
                       placeholder="Nombre o departamento..."
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
                <select wire:model.live="departamento_filtro"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos los departamentos</option>
                    @foreach($departamentos as $dep)
                        <option value="{{ $dep }}">{{ $dep }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select wire:model.live="estado_filtro"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
        </div>

        @if($search || $departamento_filtro || $estado_filtro !== '')
            <div class="mt-3 pt-3 border-t">
                <button wire:click="limpiarFiltros"
                        class="text-red-600 hover:text-red-800 text-sm flex items-center gap-1">
                    <i class="fas fa-times"></i> Limpiar filtros
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ciudad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barrios</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($ciudades as $ciudad)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $ciudad->nombre }}</div>
                                @if($ciudad->codigo_postal)
                                    <div class="text-xs text-gray-500">CP: {{ $ciudad->codigo_postal }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $ciudad->departamento }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $ciudad->barrios_count }} barrios
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($ciudad->activo)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('ciudades.editar', $ciudad->id) }}"
                                       class="text-blue-600 hover:text-blue-900" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($ciudad->activo)
                                        <button wire:click="desactivar({{ $ciudad->id }})"
                                                onclick="confirm('¿Desactivar esta ciudad?') || event.stopImmediatePropagation()"
                                                class="text-red-600 hover:text-red-900" title="Desactivar">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @else
                                        <button wire:click="activar({{ $ciudad->id }})"
                                                class="text-green-600 hover:text-green-900" title="Activar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-city text-4xl mb-3 text-gray-300"></i>
                                    <p class="font-medium">No hay ciudades registradas</p>
                                    <p class="text-sm">Comienza creando la primera ciudad</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ciudades->hasPages())
            <div class="border-t border-gray-200 px-6 py-3">
                {{ $ciudades->links() }}
            </div>
        @endif
    </div>
</div>
