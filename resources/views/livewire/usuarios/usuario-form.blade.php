<div class="max-w-3xl mx-auto">
    <form wire:submit="guardar">

        <!-- Datos personales -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4 flex items-center gap-2">
                <i class="fas fa-user text-blue-500"></i> Datos del Usuario
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input wire:model="name" type="text" placeholder="Nombre"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-300 @enderror">
                    @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                    <input wire:model="apellido" type="text" placeholder="Apellido"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input wire:model="email" type="email" placeholder="email@ejemplo.com"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-300 @enderror">
                    @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Contraseña {{ $esEditar ? '(dejar vacío para no cambiar)' : '*' }}
                    </label>
                    <input wire:model="password" type="password" placeholder="{{ $esEditar ? '••••••••' : 'Mínimo 8 caracteres' }}"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('password') border-red-300 @enderror">
                    @error('password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input wire:model="telefono" type="text" placeholder="0981-000-000"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cédula</label>
                    <input wire:model="cedula" type="text" placeholder="1.234.567"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Rol y estado -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4 flex items-center gap-2">
                <i class="fas fa-shield-alt text-blue-500"></i> Rol y Estado
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol *</label>
                    <select wire:model.live="tipo_usuario"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('tipo_usuario') border-red-300 @enderror">
                        <option value="cajero">Cajero</option>
                        <option value="cobrador">Cobrador</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="admin_empresa">Administrador</option>
                    </select>
                    @error('tipo_usuario') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror

                    <div class="mt-2 text-xs text-gray-500 space-y-0.5">
                        @if($tipo_usuario === 'admin_empresa')
                            <p class="text-purple-700"><i class="fas fa-infinity mr-1"></i> Acceso completo a todos los módulos.</p>
                        @elseif($tipo_usuario === 'supervisor')
                            <p><i class="fas fa-info-circle mr-1"></i> Acceso configurado según permisos abajo.</p>
                        @elseif($tipo_usuario === 'cajero')
                            <p><i class="fas fa-info-circle mr-1"></i> Acceso configurado según permisos abajo.</p>
                        @elseif($tipo_usuario === 'cobrador')
                            <p><i class="fas fa-info-circle mr-1"></i> Acceso configurado según permisos abajo.</p>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select wire:model="estado"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                        <option value="suspendido">Suspendido</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Permisos por módulo -->
        @if($tipo_usuario !== 'admin_empresa')
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide flex items-center gap-2">
                    <i class="fas fa-key text-blue-500"></i> Permisos por Módulo
                </h3>
                <div class="flex gap-2">
                    <button type="button"
                            wire:click="$set('permisos', {{ json_encode(array_keys($modulos)) }})"
                            class="text-xs text-blue-600 hover:text-blue-800 border border-blue-200 px-2 py-1 rounded hover:bg-blue-50">
                        Seleccionar todos
                    </button>
                    <button type="button"
                            wire:click="$set('permisos', [])"
                            class="text-xs text-gray-500 hover:text-gray-700 border border-gray-200 px-2 py-1 rounded hover:bg-gray-50">
                        Quitar todos
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @foreach($modulos as $key => $info)
                <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                              {{ in_array($key, $permisos) ? 'border-blue-300 bg-blue-50' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}">
                    <input type="checkbox"
                           wire:model.live="permisos"
                           value="{{ $key }}"
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <div class="flex items-center gap-2">
                        <i class="{{ $info['icono'] }} text-sm {{ in_array($key, $permisos) ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm {{ in_array($key, $permisos) ? 'text-blue-800 font-medium' : 'text-gray-700' }}">
                            {{ $info['label'] }}
                        </span>
                    </div>
                </label>
                @endforeach
            </div>
        </div>
        @else
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6 flex items-center gap-3">
            <i class="fas fa-infinity text-purple-500 text-xl"></i>
            <div>
                <p class="text-sm font-medium text-purple-800">Acceso total</p>
                <p class="text-xs text-purple-600">Los administradores tienen acceso a todos los módulos sin restricciones.</p>
            </div>
        </div>
        @endif

        <!-- Botones -->
        <div class="flex items-center justify-between">
            <a href="{{ route('usuarios.index') }}"
               class="text-gray-600 hover:text-gray-900 px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 text-sm transition-colors">
                <i class="fas fa-arrow-left mr-1"></i> Cancelar
            </a>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                <span wire:loading.remove wire:target="guardar">
                    <i class="fas fa-save mr-1"></i> {{ $esEditar ? 'Actualizar' : 'Crear Usuario' }}
                </span>
                <span wire:loading wire:target="guardar" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    Guardando...
                </span>
            </button>
        </div>

    </form>
</div>
