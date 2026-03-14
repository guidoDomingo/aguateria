<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('zonas.index') }}" 
               class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $zonaId ? 'Editar Zona' : 'Nueva Zona' }}
                </h1>
                <p class="text-gray-600">
                    {{ $zonaId ? 'Modifica la información de la zona' : 'Crea una nueva zona de cobranza' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow">
        <form wire:submit="guardar">
            <div class="p-6 space-y-6">
                <!-- Información Básica -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        Información Básica
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre de la Zona *
                            </label>
                            <input type="text" 
                                   id="nombre"
                                   wire:model="nombre"
                                   placeholder="ej: Centro Norte, Zona Industrial, etc."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nombre') border-red-500 @enderror">
                            @error('nombre')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="barrio_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Barrio *
                            </label>
                            <select id="barrio_id" 
                                    wire:model="barrio_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('barrio_id') border-red-500 @enderror">
                                <option value="">Selecciona un barrio...</option>
                                @foreach($barrios as $barrio)
                                    <option value="{{ $barrio->id }}">{{ $barrio->nombre }}</option>
                                @endforeach
                            </select>
                            @error('barrio_id')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">
                            Descripción
                        </label>
                        <textarea id="descripcion"
                                  wire:model="descripcion" 
                                  rows="3"
                                  placeholder="Describe las características de esta zona..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('descripcion') border-red-500 @enderror"></textarea>
                        @error('descripcion')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Configuración Visual -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-palette text-green-500 mr-2"></i>
                        Configuración Visual
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="color" class="block text-sm font-medium text-gray-700 mb-1">
                                Color de Identificación
                            </label>
                            <div class="flex items-center gap-3">
                                <input type="color" 
                                       id="color"
                                       wire:model="color"
                                       class="w-16 h-10 border border-gray-300 rounded cursor-pointer">
                                <input type="text"
                                       wire:model="color"
                                       placeholder="#3B82F6"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('color') border-red-500 @enderror">
                            </div>
                            @error('color')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Color para identificar la zona en mapas y reportes</p>
                        </div>

                        <div>
                            <label for="orden" class="block text-sm font-medium text-gray-700 mb-1">
                                Orden de Prioridad *
                            </label>
                            <input type="number" 
                                   id="orden"
                                   wire:model="orden"
                                   min="0"
                                   max="999"
                                   placeholder="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('orden') border-red-500 @enderror">
                            @error('orden')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Orden de aparición (0 = primera posición)</p>
                        </div>
                    </div>
                </div>

                <!-- Estado -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-toggle-on text-purple-500 mr-2"></i>
                        Estado
                    </h3>
                    
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   wire:model="activo"
                                   class="sr-only">
                            <div class="relative">
                                <div class="block bg-gray-600 w-14 h-8 rounded-full {{ $activo ? 'bg-blue-600' : 'bg-gray-400' }}"></div>
                                <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition {{ $activo ? 'transform translate-x-6' : '' }}"></div>
                            </div>
                            <div class="ml-3">
                                <span class="text-sm font-medium text-gray-900">Zona Activa</span>
                                <p class="text-xs text-gray-500">Las zonas inactivas no aparecen en la asignación de cobradores</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                <button type="button" 
                        wire:click="cancelar"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" 
                        wire:loading.attr="disabled"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex items-center gap-2 disabled:opacity-50">
                    <span wire:loading wire:target="guardar">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                    <span wire:loading.remove wire:target="guardar">
                        <i class="fas fa-save"></i>
                    </span>
                    {{ $cargando ? 'Guardando...' : ($zonaId ? 'Actualizar Zona' : 'Crear Zona') }}
                </button>
            </div>
        </form>
    </div>

    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-lightbulb text-yellow-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Consejos para crear zonas</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <ul class="list-disc list-inside">
                        <li>Usa nombres descriptivos y fáciles de recordar</li>
                        <li>Asigna colores distintos para facilitar la identificación visual</li>
                        <li>Ordena las zonas según la prioridad de cobranza</li>
                        <li>Incluye referencias geográficas en la descripción</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Campos obligatorios
        const camposObligatorios = ['nombre', 'barrio_id', 'orden'];
    </script>
</div>