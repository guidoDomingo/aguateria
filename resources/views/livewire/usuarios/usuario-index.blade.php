<div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div></div>
        <a href="{{ route('usuarios.crear') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
            <i class="fas fa-plus"></i> Nuevo Usuario
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <input wire:model.live.debounce.300ms="buscar" type="text"
                       placeholder="Nombre, email, cédula..."
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                <select wire:model.live="filtroTipo" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="">Todos los roles</option>
                    <option value="admin_empresa">Administrador</option>
                    <option value="supervisor">Supervisor</option>
                    <option value="cajero">Cajero</option>
                    <option value="cobrador">Cobrador</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select wire:model.live="filtroEstado" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="">Todos</option>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                    <option value="suspendido">Suspendido</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rol</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Módulos con acceso</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($usuarios as $usuario)
                <tr class="hover:bg-gray-50 {{ $usuario->id === auth()->id() ? 'bg-blue-50' : '' }}">
                    <!-- Usuario -->
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-sm flex-shrink-0">
                                {{ strtoupper(substr($usuario->name, 0, 1)) }}{{ strtoupper(substr($usuario->apellido ?? '', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $usuario->name }} {{ $usuario->apellido }}
                                    @if($usuario->id === auth()->id())
                                        <span class="text-xs text-blue-600 font-normal">(tú)</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500">{{ $usuario->email }}</p>
                                @if($usuario->cedula)
                                    <p class="text-xs text-gray-400">CI: {{ $usuario->cedula }}</p>
                                @endif
                            </div>
                        </div>
                    </td>

                    <!-- Rol -->
                    <td class="px-6 py-4">
                        @php
                            $rolConfig = [
                                'admin_empresa' => ['label' => 'Administrador', 'class' => 'bg-purple-100 text-purple-800'],
                                'supervisor'    => ['label' => 'Supervisor',    'class' => 'bg-blue-100 text-blue-800'],
                                'cajero'        => ['label' => 'Cajero',        'class' => 'bg-green-100 text-green-800'],
                                'cobrador'      => ['label' => 'Cobrador',      'class' => 'bg-yellow-100 text-yellow-800'],
                            ];
                            $rol = $rolConfig[$usuario->tipo_usuario] ?? ['label' => $usuario->tipo_usuario, 'class' => 'bg-gray-100 text-gray-800'];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $rol['class'] }}">
                            {{ $rol['label'] }}
                        </span>
                    </td>

                    <!-- Módulos -->
                    <td class="px-6 py-4">
                        @if(in_array($usuario->tipo_usuario, ['admin_empresa', 'super_admin']))
                            <span class="text-xs text-purple-700 font-medium">
                                <i class="fas fa-infinity mr-1"></i> Acceso total
                            </span>
                        @else
                            @php $permisos = $usuario->permisos ?? []; @endphp
                            @if(count($permisos) > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach(array_slice($permisos, 0, 4) as $modulo)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-gray-100 text-gray-700">
                                            {{ $modulos[$modulo]['label'] ?? $modulo }}
                                        </span>
                                    @endforeach
                                    @if(count($permisos) > 4)
                                        <span class="text-xs text-gray-500">+{{ count($permisos) - 4 }} más</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400">Sin permisos</span>
                            @endif
                        @endif
                    </td>

                    <!-- Estado -->
                    <td class="px-6 py-4">
                        @if($usuario->estado === 'activo')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Activo
                            </span>
                        @elseif($usuario->estado === 'inactivo')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                <i class="fas fa-times-circle mr-1"></i> Inactivo
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                <i class="fas fa-ban mr-1"></i> Suspendido
                            </span>
                        @endif
                        @if($usuario->last_login_at)
                            <p class="text-xs text-gray-400 mt-1">Último: {{ $usuario->last_login_at->diffForHumans() }}</p>
                        @endif
                    </td>

                    <!-- Acciones -->
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('usuarios.editar', $usuario->id) }}"
                               class="text-blue-600 hover:text-blue-800 p-1.5 rounded hover:bg-blue-50 transition-colors"
                               title="Editar">
                                <i class="fas fa-edit text-sm"></i>
                            </a>

                            @if($usuario->id !== auth()->id())
                                <button wire:click="toggleEstado({{ $usuario->id }})"
                                        wire:confirm="{{ $usuario->estado === 'activo' ? '¿Desactivar este usuario?' : '¿Activar este usuario?' }}"
                                        class="{{ $usuario->estado === 'activo' ? 'text-orange-500 hover:text-orange-700 hover:bg-orange-50' : 'text-green-600 hover:text-green-800 hover:bg-green-50' }} p-1.5 rounded transition-colors"
                                        title="{{ $usuario->estado === 'activo' ? 'Desactivar' : 'Activar' }}">
                                    <i class="fas {{ $usuario->estado === 'activo' ? 'fa-toggle-on' : 'fa-toggle-off' }} text-sm"></i>
                                </button>

                                <button wire:click="eliminar({{ $usuario->id }})"
                                        wire:confirm="¿Eliminar este usuario? Esta acción no se puede deshacer."
                                        class="text-red-500 hover:text-red-700 p-1.5 rounded hover:bg-red-50 transition-colors"
                                        title="Eliminar">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <i class="fas fa-users text-gray-300 text-4xl mb-3"></i>
                        <p class="text-gray-500 text-sm">No se encontraron usuarios.</p>
                        <a href="{{ route('usuarios.crear') }}" class="text-blue-600 hover:underline text-sm mt-1 inline-block">
                            Crear el primero
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($usuarios->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $usuarios->links() }}
        </div>
        @endif
    </div>
</div>
