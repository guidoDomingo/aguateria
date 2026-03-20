<div>
    <form wire:submit="guardar">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ $modo === 'crear' ? 'Crear Cobrador' : 'Editar Cobrador' }}
                    </h1>
                    <p class="text-gray-600">Complete la información del cobrador</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" wire:click="cancelar" 
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        {{ $modo === 'crear' ? 'Crear Cobrador' : 'Guardar Cambios' }}
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Información Personal -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-user"></i>
                        Información Personal
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Código -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Código *
                            </label>
                            <input type="text" wire:model="codigo" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('codigo') border-red-500 @enderror"
                                   placeholder="COB001">
                            @error('codigo')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nombre -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre *
                            </label>
                            <input type="text" wire:model="nombre" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nombre') border-red-500 @enderror"
                                   placeholder="Juan Carlos">
                            @error('nombre')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Apellido -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Apellido *
                            </label>
                            <input type="text" wire:model="apellido" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('apellido') border-red-500 @enderror"
                                   placeholder="García López">
                            @error('apellido')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Cédula -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Cédula de Identidad *
                            </label>
                            <input type="text" wire:model="cedula" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('cedula') border-red-500 @enderror"
                                   placeholder="1234567">
                            @error('cedula')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Teléfono
                            </label>
                            <input type="text" wire:model="telefono" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('telefono') border-red-500 @enderror"
                                   placeholder="0981123456">
                            @error('telefono')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Email
                            </label>
                            <input type="email" wire:model="email" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                                   placeholder="cobrador@aguateria.com">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Dirección -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Dirección
                            </label>
                            <input type="text" wire:model="direccion" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('direccion') border-red-500 @enderror"
                                   placeholder="Dirección completa">
                            @error('direccion')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Configuración Laboral -->
                <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-briefcase"></i>
                        Configuración Laboral
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Zona -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Zona Asignada *
                            </label>
                            <select wire:model="zona_id" 
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('zona_id') border-red-500 @enderror">
                                <option value="">Selecciona una zona...</option>
                                @foreach($zonas as $zona)
                                    <option value="{{ $zona->id }}">{{ $zona->nombre }}</option>
                                @endforeach
                            </select>
                            @error('zona_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        <!-- Comisión Porcentaje -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Comisión Porcentaje (%)
                            </label>
                            <input type="number" wire:model="comision_porcentaje" 
                                   step="0.01" min="0" max="100"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('comision_porcentaje') border-red-500 @enderror"
                                   placeholder="0.00">
                            @error('comision_porcentaje')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Comisión Fija -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Comisión Fija (Gs.)
                            </label>
                            <input type="number" wire:model="comision_fija" 
                                   step="1" min="0"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('comision_fija') border-red-500 @enderror"
                                   placeholder="0">
                            @error('comision_fija')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fecha Ingreso -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Fecha de Ingreso *
                            </label>
                            <input type="date" wire:model="fecha_ingreso" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('fecha_ingreso') border-red-500 @enderror">
                            @error('fecha_ingreso')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fecha Salida -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Fecha de Salida
                            </label>
                            <input type="date" wire:model="fecha_salida" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('fecha_salida') border-red-500 @enderror">
                            @error('fecha_salida')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Lateral -->
            <div class="lg:col-span-1">
                <!-- Estado -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-toggle-on"></i>
                        Estado
                    </h3>
                    
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" wire:model="estado" value="activo" class="mr-2">
                            <span class="text-sm">Activo</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" wire:model="estado" value="inactivo" class="mr-2">
                            <span class="text-sm">Inactivo</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" wire:model="estado" value="suspendido" class="mr-2">
                            <span class="text-sm">Suspendido</span>
                        </label>
                    </div>
                    @error('estado')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Observaciones -->
                <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-sticky-note"></i>
                        Observaciones
                    </h3>
                    
                    <textarea wire:model="observaciones" rows="4"
                              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('observaciones') border-red-500 @enderror"
                              placeholder="Notas adicionales sobre el cobrador..."></textarea>
                    @error('observaciones')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Campos Obligatorios -->
        <div class="mt-6">
            <p class="text-sm text-gray-600">* Campos obligatorios</p>
        </div>
    </form>
</div>