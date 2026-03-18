<div>
    <form wire:submit="guardar">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Información Principal -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-city"></i>
                        Información de la Ciudad
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Nombre -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="nombre"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nombre') border-red-500 @enderror"
                                   placeholder="Asunción, Ciudad del Este, Luque...">
                            @error('nombre')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Departamento -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Departamento <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="departamento"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('departamento') border-red-500 @enderror">
                                <option value="">Selecciona un departamento...</option>
                                @foreach(\App\Livewire\Ciudades\CiudadForm::$departamentos as $dep)
                                    <option value="{{ $dep }}">{{ $dep }}</option>
                                @endforeach
                            </select>
                            @error('departamento')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- País -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">País</label>
                            <input type="text" wire:model="pais"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50"
                                   readonly>
                        </div>

                        <!-- Código Postal -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Código Postal</label>
                            <input type="text" wire:model="codigo_postal"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('codigo_postal') border-red-500 @enderror"
                                   placeholder="Ej: 1209">
                            @error('codigo_postal')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Lateral -->
            <div class="lg:col-span-1 space-y-4">
                <!-- Estado -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-toggle-on"></i>
                        Estado
                    </h3>
                    <div class="space-y-2">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" wire:model="activo" value="1" class="mr-2">
                            <span class="text-sm">Activo</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" wire:model="activo" value="0" class="mr-2">
                            <span class="text-sm">Inactivo</span>
                        </label>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex flex-col gap-3">
                        <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i>
                            {{ $modo === 'crear' ? 'Crear Ciudad' : 'Guardar Cambios' }}
                        </button>
                        <button type="button" wire:click="cancelar"
                                class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg flex items-center justify-center gap-2">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <p class="text-sm text-gray-500"><span class="text-red-500">*</span> Campos obligatorios</p>
        </div>
    </form>
</div>
