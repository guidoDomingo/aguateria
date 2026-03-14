<div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $esEdicion ? 'Editar Cliente' : 'Nuevo Cliente' }}
            </h1>
            <p class="text-gray-600">
                {{ $esEdicion ? 'Modifica los datos del cliente' : 'Completa los datos del nuevo cliente' }}
            </p>
        </div>
        <button wire:click="cancelar" 
                class="text-gray-600 hover:text-gray-800">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <!-- Formulario -->
    <form wire:submit="guardar">
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            <!-- Datos Personales -->
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-user mr-2"></i>
                    Datos Personales
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre *
                        </label>
                        <input wire:model.blur="nombre" 
                               id="nombre" 
                               type="text" 
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('nombre') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                        @error('nombre')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Apellido -->
                    <div>
                        <label for="apellido" class="block text-sm font-medium text-gray-700 mb-2">
                            Apellido *
                        </label>
                        <input wire:model.blur="apellido" 
                               id="apellido" 
                               type="text" 
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('apellido') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                        @error('apellido')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Cédula -->
                    <div>
                        <label for="cedula" class="block text-sm font-medium text-gray-700 mb-2">
                            Cédula/CI *
                        </label>
                        <input wire:model.blur="cedula" 
                               id="cedula" 
                               type="text" 
                               placeholder="Ej: 1234567"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('cedula') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                        @error('cedula')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Estado -->
                    <div>
                        <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                            Estado *
                        </label>
                        <select wire:model="estado" 
                                id="estado" 
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('estado') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                            <option value="activo">Activo</option>
                            <option value="suspendido">Suspendido</option>
                            <option value="cortado">Cortado</option>
                            <option value="retirado">Retirado</option>
                        </select>
                        @error('estado')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Información de Contacto -->
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-phone mr-2"></i>
                    Información de Contacto
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Teléfono -->
                    <div>
                        <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">
                            Teléfono
                        </label>
                        <input wire:model.blur="telefono" 
                               id="telefono" 
                               type="text" 
                               placeholder="Ej: 0981123456"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('telefono') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                        @error('telefono')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input wire:model.blur="email" 
                               id="email" 
                               type="email" 
                               placeholder="cliente@ejemplo.com"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Dirección -->
                    <div class="md:col-span-2">
                        <label for="direccion" class="block text-sm font-medium text-gray-700 mb-2">
                            Dirección *
                        </label>
                        <input wire:model.blur="direccion" 
                               id="direccion" 
                               type="text" 
                               placeholder="Calle y número, referencias"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('direccion') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                        @error('direccion')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Configuración del Servicio -->
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-tint mr-2"></i>
                    Configuración del Servicio
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Barrio -->
                    <div>
                        <label for="barrio_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Barrio *
                        </label>
                        <select wire:model.blur="barrio_id" 
                                id="barrio_id" 
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('barrio_id') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                            <option value="">Selecciona un barrio...</option>
                            @foreach($barrios as $barrio)
                                <option value="{{ $barrio->id }}">{{ $barrio->nombre }}</option>
                            @endforeach
                        </select>
                        @error('barrio_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Cobrador -->
                    <div>
                        <label for="cobrador_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Cobrador Asignado *
                        </label>
                        <select wire:model.blur="cobrador_id" 
                                id="cobrador_id" 
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('cobrador_id') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                            <option value="">Selecciona un cobrador...</option>
                            @foreach($cobradores as $cobrador)
                                <option value="{{ $cobrador->id }}">{{ $cobrador->nombre }}</option>
                            @endforeach
                        </select>
                        @error('cobrador_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tarifa -->
                    <div>
                        <label for="tarifa_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Tarifa *
                        </label>
                        <select wire:model.blur="tarifa_id" 
                                id="tarifa_id" 
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('tarifa_id') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                            <option value="">Selecciona una tarifa...</option>
                            @foreach($tarifas as $tarifa)
                                <option value="{{ $tarifa->id }}">
                                    {{ $tarifa->nombre }} - {{ number_format($tarifa->monto_mensual, 0, ',', '.') }} Gs.
                                </option>
                            @endforeach
                        </select>
                        @error('tarifa_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Descuento Especial -->
                    <div>
                        <label for="descuento_especial" class="block text-sm font-medium text-gray-700 mb-2">
                            Descuento Especial (%)
                        </label>
                        <input wire:model.blur="descuento_especial" 
                               id="descuento_especial" 
                               type="number" 
                               min="0" 
                               max="100" 
                               step="0.01"
                               placeholder="0.00"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('descuento_especial') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                        @error('descuento_especial')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Descuento aplicado además del descuento base de la tarifa</p>
                    </div>

                    <!-- Observaciones -->
                    <div class="md:col-span-2">
                        <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-2">
                            Observaciones
                        </label>
                        <textarea wire:model.blur="observaciones" 
                                  id="observaciones" 
                                  rows="3" 
                                  placeholder="Notas adicionales sobre el cliente..."
                                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('observaciones') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"></textarea>
                        @error('observaciones')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="bg-gray-50 px-6 py-4 flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    <span class="text-red-500">*</span> Campos obligatorios
                </div>
                <div class="flex space-x-3">
                    <button type="button" 
                            wire:click="cancelar"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                            {{ $cargando ? 'disabled' : '' }}>
                        @if($cargando)
                            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                            Guardando...
                        @else
                            <i class="fas fa-save mr-2"></i>
                            {{ $esEdicion ? 'Actualizar Cliente' : 'Crear Cliente' }}
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Vista previa de información si estamos editando -->
    @if($esEdicion)
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="text-sm font-medium text-blue-900 mb-2">
                <i class="fas fa-info-circle mr-1"></i>
                Información del Cliente
            </h4>
            <div class="text-sm text-blue-800">
                <p><strong>Cliente:</strong> {{ $nombre }} {{ $apellido }}</p>
                <p><strong>Cédula:</strong> {{ $cedula }}</p>
                @if($descuento_especial > 0)
                    <p><strong>Descuento especial:</strong> {{ $descuento_especial }}%</p>
                @endif
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