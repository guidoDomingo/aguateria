<div>
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestión de Clientes</h1>
            <p class="text-gray-600">Administra los clientes de tu empresa</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('clientes.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                <i class="fas fa-plus mr-2"></i>
                Nuevo Cliente
            </a>
        </div>
    </div>

    <!-- Estadísticas Rápidas -->
    @if(isset($estadisticas))
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-users text-lg"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-600">Total</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $estadisticas['total'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle text-lg"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-600">Activos</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $estadisticas['activos'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-pause-circle text-lg"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-600">Suspendidos</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $estadisticas['suspendidos'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-cut text-lg"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-600">Cortados</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $estadisticas['cortados'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filtros y Búsqueda -->
    <div class="bg-white p-6 rounded-lg shadow-sm border mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Búsqueda -->
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Buscar cliente
                </label>
                <input wire:model.live.debounce.300ms="buscar" 
                       type="text" 
                       placeholder="Nombre, cédula o dirección..."
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Filtro por Estado -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Estado
                </label>
                <select wire:model.live="filtroEstado" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="todos">Todos</option>
                    <option value="activo">Activos</option>
                    <option value="suspendido">Suspendidos</option>
                    <option value="cortado">Cortados</option>
                    <option value="retirado">Retirados</option>
                </select>
            </div>

            <!-- Filtro por Barrio -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Barrio
                </label>
                <select wire:model.live="filtroBarrio" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos los barrios</option>
                    @foreach($barrios as $barrio)
                        <option value="{{ $barrio->id }}">{{ $barrio->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro por Cobrador -->
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

        <!-- Botones adicionales -->
        <div class="flex justify-between items-center mt-4">
            <div class="flex items-center space-x-4">
                <label class="flex items-center">
                    <input wire:model.live="mostrarInactivos" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:border-blue-500 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-600">Incluir inactivos</span>
                </label>
            </div>
            <button wire:click="limpiarFiltros" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                <i class="fas fa-broom mr-1"></i>
                Limpiar filtros
            </button>
        </div>
    </div>

    <!-- Tabla de Clientes -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cliente
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cédula
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Contacto
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ubicación
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cobrador
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($clientes as $cliente)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $cliente->nombre }} {{ $cliente->apellido }}
                                    </div>
                                    @if($cliente->descuento_especial > 0)
                                        <div class="text-xs text-green-600">
                                            <i class="fas fa-percentage"></i> {{ $cliente->descuento_especial }}% descuento
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $cliente->cedula }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($cliente->telefono)
                                        <div><i class="fas fa-phone text-xs text-gray-400 mr-1"></i> {{ $cliente->telefono }}</div>
                                    @endif
                                    @if($cliente->email)
                                        <div><i class="fas fa-envelope text-xs text-gray-400 mr-1"></i> {{ $cliente->email }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $cliente->direccion }}</div>
                                <div class="text-xs text-gray-500">{{ $cliente->barrio->nombre ?? 'Sin barrio' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @switch($cliente->estado)
                                    @case('activo')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Activo
                                        </span>
                                        @break
                                    @case('suspendido')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-pause-circle mr-1"></i>
                                            Suspendido
                                        </span>
                                        @break
                                    @case('cortado')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-cut mr-1"></i>
                                            Cortado
                                        </span>
                                        @break
                                    @case('retirado')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Retirado
                                        </span>
                                        @break
                                @endswitch
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $cliente->cobrador->nombre ?? 'Sin asignar' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('clientes.edit', $cliente->id) }}" 
                                       class="text-blue-600 hover:text-blue-900" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <!-- Menú desplegable para cambiar estado -->
                                    <div class="relative inline-block text-left">
                                        <button type="button" class="text-gray-600 hover:text-gray-900" 
                                                onclick="toggleDropdown('dropdown-{{ $cliente->id }}')" title="Cambiar estado">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                        <div id="dropdown-{{ $cliente->id }}" 
                                             class="hidden absolute right-0 z-10 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 divide-y divide-gray-100">
                                            <div class="py-1">
                                                @if($cliente->estado !== 'activo')
                                                    <button wire:click="cambiarEstado({{ $cliente->id }}, 'activo')" 
                                                            class="text-green-700 group flex items-center px-4 py-2 text-sm hover:bg-gray-100 w-full text-left">
                                                        <i class="fas fa-check-circle mr-3"></i>
                                                        Activar
                                                    </button>
                                                @endif
                                                @if($cliente->estado !== 'suspendido')
                                                    <button wire:click="cambiarEstado({{ $cliente->id }}, 'suspendido')" 
                                                            class="text-yellow-700 group flex items-center px-4 py-2 text-sm hover:bg-gray-100 w-full text-left">
                                                        <i class="fas fa-pause-circle mr-3"></i>
                                                        Suspender
                                                    </button>
                                                @endif
                                                @if($cliente->estado !== 'cortado')
                                                    <button wire:click="cambiarEstado({{ $cliente->id }}, 'cortado')" 
                                                            class="text-red-700 group flex items-center px-4 py-2 text-sm hover:bg-gray-100 w-full text-left">
                                                        <i class="fas fa-cut mr-3"></i>
                                                        Cortar
                                                    </button>
                                                @endif
                                                @if($cliente->estado !== 'retirado')
                                                    <button wire:click="cambiarEstado({{ $cliente->id }}, 'retirado')" 
                                                            class="text-gray-700 group flex items-center px-4 py-2 text-sm hover:bg-gray-100 w-full text-left">
                                                        <i class="fas fa-times-circle mr-3"></i>
                                                        Retirar
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <button wire:click="confirmarEliminacion({{ $cliente->id }})" 
                                            class="text-red-600 hover:text-red-900" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-lg font-medium">No hay clientes</p>
                                    <p class="text-sm">Comienza agregando tu primer cliente</p>
                                    <a href="{{ route('clientes.create') }}" 
                                       class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-plus mr-2"></i>
                                        Agregar Cliente
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($clientes->hasPages())
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                {{ $clientes->links() }}
            </div>
        @endif
    </div>

    <!-- Modal de Confirmación para Eliminar -->
    @if($mostrarModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" 
             x-data="{ open: true }" 
             x-show="open" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
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
                                    Confirmar Eliminación
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        ¿Estás seguro de que deseas eliminar este cliente? Esta acción no se puede deshacer y se eliminará toda la información asociada.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="eliminar" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            <i class="fas fa-trash mr-2"></i>
                            Eliminar
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

<script>
    function toggleDropdown(id) {
        const dropdown = document.getElementById(id);
        if (dropdown.classList.contains('hidden')) {
            // Cerrar otros dropdowns
            document.querySelectorAll('[id^="dropdown-"]').forEach(el => {
                el.classList.add('hidden');
            });
            dropdown.classList.remove('hidden');
        } else {
            dropdown.classList.add('hidden');
        }
    }

    // Cerrar dropdown al hacer clic fuera
    document.addEventListener('click', function(event) {
        if (!event.target.closest('[onclick^="toggleDropdown"]')) {
            document.querySelectorAll('[id^="dropdown-"]').forEach(el => {
                el.classList.add('hidden');
            });
        }
    });
</script>