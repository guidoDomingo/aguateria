<div>
    <form wire:submit="guardar">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ $modo === 'crear' ? 'Crear Barrio' : 'Editar Barrio' }}
                    </h1>
                    <p class="text-gray-600">Complete la información del barrio</p>
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
                        {{ $modo === 'crear' ? 'Crear Barrio' : 'Guardar Cambios' }}
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Información Básica -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-map-marker-alt"></i>
                        Información Básica
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Nombre -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre *
                            </label>
                            <input type="text" wire:model="nombre" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nombre') border-red-500 @enderror"
                                   placeholder="Centro, Villa Morra, etc.">
                            @error('nombre')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Ciudad -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Ciudad *
                            </label>
                            <select wire:model="ciudad_id" 
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('ciudad_id') border-red-500 @enderror">
                                <option value="">Selecciona una ciudad...</option>
                                @foreach($ciudades as $ciudad)
                                    <option value="{{ $ciudad->id }}">{{ $ciudad->nombre }}</option>
                                @endforeach
                            </select>
                            @error('ciudad_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Referencia -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Referencia
                            </label>
                            <input type="text" wire:model="referencia" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('referencia') border-red-500 @enderror"
                                   placeholder="Cerca del shopping, próximo al hospital, etc.">
                            @error('referencia')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Descripción
                            </label>
                            <textarea wire:model="descripcion" rows="3"
                                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('descripcion') border-red-500 @enderror"
                                      placeholder="Descripción adicional del barrio..."></textarea>
                            @error('descripcion')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Coordenadas -->
                <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-globe"></i>
                        Coordenadas GPS (Opcional)
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Latitud -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Latitud
                            </label>
                            <input type="number" wire:model="latitud" 
                                   step="0.0000001" min="-90" max="90"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('latitud') border-red-500 @enderror"
                                   placeholder="-25.2637">
                            @error('latitud')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Longitud -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Longitud
                            </label>
                            <input type="number" wire:model="longitud" 
                                   step="0.0000001" min="-180" max="180"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('longitud') border-red-500 @enderror"
                                   placeholder="-57.5759">
                            @error('longitud')
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
                            <input type="radio" wire:model="activo" value="1" class="mr-2">
                            <span class="text-sm">Activo</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" wire:model="activo" value="0" class="mr-2">
                            <span class="text-sm">Inactivo</span>
                        </label>
                    </div>
                    @error('activo')
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