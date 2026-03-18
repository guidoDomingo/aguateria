<div>
    <!-- Header -->
    <div class="bg-white p-6 rounded-lg shadow-sm border mb-6">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Configuración de Recibos</h1>
                <p class="text-gray-600">Personaliza el diseño y formato de tus recibos de pago</p>
            </div>
            <div class="flex gap-3">
                <button wire:click="togglePreview" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-eye mr-2"></i>
                    {{ $mostrarPreview ? 'Ocultar' : 'Vista' }} Previa
                </button>
                <button wire:click="restaurarDefecto" 
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-undo mr-2"></i>
                    Restaurar Defecto
                </button>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex">
                <i class="fas fa-check-circle text-green-400 mr-3 mt-1"></i>
                <div>
                    <h3 class="text-sm font-medium text-green-800">Éxito</h3>
                    <div class="mt-1 text-sm text-green-700">{{ session('message') }}</div>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex">
                <i class="fas fa-exclamation-circle text-red-400 mr-3 mt-1"></i>
                <div>
                    <h3 class="text-sm font-medium text-red-800">Error</h3>
                    <div class="mt-1 text-sm text-red-700">{{ session('error') }}</div>
                </div>
            </div>
        </div>
    @endif

    <form wire:submit="guardarConfiguracion">
        <div class="grid grid-cols-1 {{ $mostrarPreview ? 'lg:grid-cols-2' : 'lg:grid-cols-1' }} gap-6">
            <!-- Panel de Configuración -->
            <div class="space-y-6">
                
                <!-- Tamaño y Formato -->
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-file-alt mr-2"></i>
                            Tamaño y Formato
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Tamaño de Papel -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tamaño de Papel</label>
                            <select wire:model.live="tamaño_papel" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                @foreach($this->getTamañosPapel() as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tamaño Personalizado -->
                        @if($tamaño_papel === 'personalizado')
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Ancho (mm)</label>
                                    <input type="number" wire:model.live="ancho_personalizado" 
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                           min="50" max="500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Alto (mm)</label>
                                    <input type="number" wire:model.live="alto_personalizado"
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                           min="100" max="1000">
                                </div>
                            </div>
                        @endif

                        <!-- Orientación -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Orientación</label>
                            <div class="flex gap-4">
                                <label class="flex items-center">
                                    <input type="radio" wire:model.live="orientacion" value="portrait" 
                                           class="rounded border-gray-300 text-blue-600 focus:border-blue-500 focus:ring-blue-500">
                                    <span class="ml-2">Vertical</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" wire:model.live="orientacion" value="landscape"
                                           class="rounded border-gray-300 text-blue-600 focus:border-blue-500 focus:ring-blue-500">
                                    <span class="ml-2">Horizontal</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Diseño Visual -->
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-palette mr-2"></i>
                            Diseño Visual
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Plantilla -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Plantilla</label>
                            <select wire:model.live="plantilla" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                @foreach($this->getPlantillas() as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Colores -->
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Color Encabezado</label>
                                <input type="color" wire:model.live="color_header" 
                                       class="w-full h-10 rounded border border-gray-300">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Color Texto</label>
                                <input type="color" wire:model.live="color_text"
                                       class="w-full h-10 rounded border border-gray-300">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Color Fondo</label>
                                <input type="color" wire:model.live="color_background"
                                       class="w-full h-10 rounded border border-gray-300">
                            </div>
                        </div>

                        <!-- Fuente -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fuente</label>
                                <select wire:model.live="fuente" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    <option value="Arial">Arial</option>
                                    <option value="Times">Times New Roman</option>
                                    <option value="Courier">Courier New</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tamaño Fuente</label>
                                <input type="range" wire:model.live="tamaño_fuente" min="8" max="24" 
                                       class="w-full">
                                <div class="text-sm text-gray-500 text-center">{{ $tamaño_fuente }}px</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logo y Encabezado -->
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-image mr-2"></i>
                            Logo y Encabezado
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Mostrar Logo -->
                        <div class="flex items-center">
                            <input type="checkbox" wire:model.live="mostrar_logo" 
                                   class="rounded border-gray-300 text-blue-600 focus:border-blue-500 focus:ring-blue-500">
                            <label class="ml-2 text-sm font-medium text-gray-700">Mostrar logo de la empresa</label>
                        </div>

                        @if($mostrar_logo)
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Posición del Logo</label>
                                    <select wire:model.live="posicion_logo" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        <option value="left">Izquierda</option>
                                        <option value="center">Centro</option>
                                        <option value="right">Derecha</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tamaño del Logo</label>
                                    <input type="range" wire:model.live="tamaño_logo" min="50" max="200" 
                                           class="w-full">
                                    <div class="text-sm text-gray-500 text-center">{{ $tamaño_logo }}px</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Información a Mostrar -->
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-info-circle mr-2"></i>
                            Información a Mostrar
                        </h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" wire:model.live="mostrar_fecha" 
                                       class="rounded border-gray-300 text-blue-600 focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm">Mostrar fecha</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" wire:model.live="mostrar_hora"
                                       class="rounded border-gray-300 text-blue-600 focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm">Mostrar hora</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" wire:model.live="mostrar_direccion_empresa"
                                       class="rounded border-gray-300 text-blue-600 focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm">Dirección empresa</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" wire:model.live="mostrar_telefono_empresa"
                                       class="rounded border-gray-300 text-blue-600 focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm">Teléfono empresa</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" wire:model.live="mostrar_email_empresa"
                                       class="rounded border-gray-300 text-blue-600 focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm">Email empresa</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" wire:model.live="mostrar_codigo_qr"
                                       class="rounded border-gray-300 text-blue-600 focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm">Código QR</span>
                            </label>
                        </div>
                        <label class="flex items-center">
                            <input type="checkbox" wire:model.live="mostrar_descripcion_detallada"
                                   class="rounded border-gray-300 text-blue-600 focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm">Mostrar descripción detallada de facturas</span>
                        </label>
                    </div>
                </div>

                <!-- Mensajes Personalizables -->
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-comment-alt mr-2"></i>
                            Mensajes Personalizables
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mensaje Superior</label>
                            <input type="text" wire:model.live="mensaje_superior" 
                                   placeholder="Mensaje en la parte superior del recibo"
                                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mensaje Inferior</label>
                            <input type="text" wire:model.live="mensaje_inferior" 
                                   placeholder="Gracias por su preferencia"
                                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Términos y Condiciones</label>
                            <textarea wire:model.live="terminos_condiciones" rows="3"
                                      placeholder="Términos y condiciones a mostrar en el recibo"
                                      class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Configuración de Impresión -->
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-print mr-2"></i>
                            Configuración de Impresión
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox" wire:model.live="impresion_automatica" 
                                   class="rounded border-gray-300 text-blue-600 focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Impresión automática al generar recibo</span>
                        </label>

                        <!-- Márgenes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Márgenes (mm)</label>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Superior</label>
                                    <input type="number" wire:model.live="margenes_superior" 
                                           min="0" max="50" class="w-full rounded border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Inferior</label>
                                    <input type="number" wire:model.live="margenes_inferior"
                                           min="0" max="50" class="w-full rounded border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Izquierdo</label>
                                    <input type="number" wire:model.live="margenes_izquierdo"
                                           min="0" max="50" class="w-full rounded border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Derecho</label>
                                    <input type="number" wire:model.live="margenes_derecho"
                                           min="0" max="50" class="w-full rounded border-gray-300">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex justify-between">
                    <button type="button" onclick="window.history.back()" 
                            class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
                            {{ $guardando ? 'disabled' : '' }}>
                        @if($guardando)
                            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2 inline-block"></div>
                            Guardando...
                        @else
                            <i class="fas fa-save mr-2"></i>
                            Guardar Configuración
                        @endif
                    </button>
                </div>
            </div>

            <!-- Panel de Vista Previa -->
            @if($mostrarPreview)
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-eye mr-2"></i>
                            Vista Previa
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="border border-dashed border-gray-300 rounded-lg p-4 min-h-96 bg-gray-50">
                            <div class="text-center text-gray-500 py-8">
                                <i class="fas fa-file-alt text-4xl mb-4"></i>
                                <p>Vista previa del recibo</p>
                                <p class="text-sm">Se mostrará aquí con la configuración actual</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </form>
</div>
