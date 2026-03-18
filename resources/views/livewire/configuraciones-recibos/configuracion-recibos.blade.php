<div>
    <!-- Header -->
    <div class="bg-white p-6 rounded-lg shadow-sm border mb-6">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Configuración de Recibos</h1>
                <p class="text-gray-600">Personaliza el diseño y contenido de tus recibos de pago</p>
            </div>
            <div class="flex gap-3">
                <button wire:click="togglePreview" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas {{ $mostrarPreview ? 'fa-eye-slash' : 'fa-eye' }} mr-2"></i>
                    {{ $mostrarPreview ? 'Ocultar Vista Previa' : 'Ver Vista Previa' }}
                </button>
            </div>
        </div>
    </div>

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

    <div class="grid grid-cols-1 {{ $mostrarPreview ? 'lg:grid-cols-2' : '' }} gap-6">
        <!-- Panel de Configuración -->
        <div class="space-y-6">
            <!-- Diseño General -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-palette mr-2"></i>Diseño General
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <!-- Template -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estilo de Template</label>
                        <select wire:model.live="template" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            @foreach($this->templatesDisponibles as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('template') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Tamaño de Papel -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tamaño de Papel</label>
                        <select wire:model.live="tamaño_papel" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            @foreach($this->tamañosPapel as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('tamaño_papel') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Orientación -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Orientación</label>
                        <div class="flex gap-4">
                            <label class="flex items-center">
                                <input type="radio" wire:model.live="orientacion" value="portrait" class="form-radio">
                                <span class="ml-2">Vertical</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" wire:model.live="orientacion" value="landscape" class="form-radio">
                                <span class="ml-2">Horizontal</span>
                            </label>
                        </div>
                        @error('orientacion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Contenido del Recibo -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-list-check mr-2"></i>Elementos a Mostrar
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="flex items-center">
                            <input type="checkbox" wire:model.live="mostrar_logo" class="form-checkbox">
                            <span class="ml-2">Logo de la empresa</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" wire:model.live="mostrar_cedula_cliente" class="form-checkbox">
                            <span class="ml-2">Cédula del cliente</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" wire:model.live="mostrar_direccion_cliente" class="form-checkbox">
                            <span class="ml-2">Dirección del cliente</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" wire:model.live="mostrar_telefono_empresa" class="form-checkbox">
                            <span class="ml-2">Teléfono de la empresa</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" wire:model.live="mostrar_detalle_facturas" class="form-checkbox">
                            <span class="ml-2">Detalle de facturas</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" wire:model.live="mostrar_firma" class="form-checkbox">
                            <span class="ml-2">Línea para firma</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Textos Personalizados -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-edit mr-2"></i>Textos Personalizados
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <!-- Título Personalizado -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Título Personalizado</label>
                        <input wire:model="titulo_personalizado" type="text" 
                               placeholder="Ej: RECIBO DE PAGO MENSUAL" 
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <small class="text-gray-500">Si está vacío, se usará "RECIBO DE PAGO"</small>
                        @error('titulo_personalizado') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Mensaje de Agradecimiento -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mensaje de Agradecimiento</label>
                        <textarea wire:model="mensaje_agradecimiento" rows="2" 
                                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"></textarea>
                        @error('mensaje_agradecimiento') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Pie de Página -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pie de Página</label>
                        <textarea wire:model="pie_pagina" rows="2" 
                                  placeholder="Texto adicional al final del recibo" 
                                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"></textarea>
                        @error('pie_pagina') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Colores y Tipografía -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-font mr-2"></i>Colores y Tipografía
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Color Principal -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Color Principal</label>
                            <input wire:model.live="color_principal" type="color" 
                                   class="w-full h-10 rounded-lg border-gray-300">
                            @error('color_principal') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Color Secundario -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Color Secundario</label>
                            <input wire:model.live="color_secundario" type="color" 
                                   class="w-full h-10 rounded-lg border-gray-300">
                            @error('color_secundario') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Fuente -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Fuente</label>
                            <select wire:model.live="fuente" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                @foreach($this->fuentesDisponibles as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('fuente') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Tamaño de Fuente -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tamaño de Fuente</label>
                            <select wire:model.live="tamaño_fuente" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                @for($i = 8; $i <= 20; $i++)
                                    <option value="{{ $i }}">{{ $i }}px</option>
                                @endfor
                            </select>
                            @error('tamaño_fuente') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex flex-col sm:flex-row gap-3">
                    <button wire:click="guardarConfiguracion" 
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition-colors font-medium">
                        <i class="fas fa-save mr-2"></i>Guardar Configuración
                    </button>
                    
                    <button wire:click="restaurarDefecto" 
                            class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg transition-colors font-medium"
                            onclick="return confirm('¿Estás seguro de restaurar la configuración predeterminada?')">
                        <i class="fas fa-undo mr-2"></i>Restaurar Predeterminado
                    </button>
                </div>
            </div>
        </div>

        <!-- Vista Previa -->
        @if($mostrarPreview)
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-eye mr-2"></i>Vista Previa del Recibo
                </h3>
            </div>
            <div class="p-6">
                <div class="border border-gray-200 rounded-lg p-4 bg-white" 
                     style="max-width: {{ $template === 'compacto' ? '350px' : ($template === 'standard' ? '500px' : '600px') }}; margin: 0 auto; font-family: {{ $fuente }}, sans-serif; font-size: {{ $tamaño_fuente }}px; color: {{ $color_principal }};">
                    
                    <!-- Header -->
                    <div class="text-center mb-4 pb-2" style="border-bottom: 2px solid {{ $color_principal }};">
                        <div style="font-size: {{ $tamaño_fuente + 6 }}px; font-weight: bold; margin-bottom: 5px;">EMPRESA DEMO</div>
                        <div style="font-size: {{ $tamaño_fuente - 2 }}px; color: {{ $color_secundario }};">Dirección de la Empresa</div>
                        @if($mostrar_telefono_empresa)
                        <div style="font-size: {{ $tamaño_fuente - 2 }}px; color: {{ $color_secundario }};">Tel: (021) 123-456</div>
                        @endif
                    </div>

                    <!-- Título -->
                    <div class="text-center mb-4" style="font-size: {{ $tamaño_fuente + 4 }}px; font-weight: bold; color: {{ $color_principal }};">
                        {{ $titulo_personalizado ?: 'RECIBO DE PAGO' }}
                    </div>

                    <!-- Datos -->
                    <div class="mb-4" style="font-size: {{ $tamaño_fuente }}px;">
                        <div class="flex justify-between mb-1">
                            <span style="font-weight: bold; color: {{ $color_secundario }};">Recibo N°:</span>
                            <span>#REC000001</span>
                        </div>
                        <div class="flex justify-between mb-1">
                            <span style="font-weight: bold; color: {{ $color_secundario }};">Fecha:</span>
                            <span>{{ now()->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between mb-1">
                            <span style="font-weight: bold; color: {{ $color_secundario }};">Cliente:</span>
                            <span>Cliente Demo</span>
                        </div>
                        @if($mostrar_cedula_cliente)
                        <div class="flex justify-between mb-1">
                            <span style="font-weight: bold; color: {{ $color_secundario }};">Cédula:</span>
                            <span>1.234.567</span>
                        </div>
                        @endif
                        @if($mostrar_direccion_cliente)
                        <div class="flex justify-between mb-1">
                            <span style="font-weight: bold; color: {{ $color_secundario }};">Dirección:</span>
                            <span>Dirección del Cliente</span>
                        </div>
                        @endif
                    </div>

                    <!-- Monto -->
                    <div class="text-center p-3 mb-4" style="font-size: {{ $tamaño_fuente + 6 }}px; font-weight: bold; background: #f5f5f5; border: 2px solid {{ $color_principal }};">
                        50.000 Gs.
                    </div>

                    @if($mostrar_detalle_facturas && $template !== 'compacto')
                    <!-- Detalles de Facturas -->
                    <div class="mb-4" style="font-size: {{ $tamaño_fuente - 1 }}px;">
                        <div style="font-weight: bold; color: {{ $color_secundario }}; margin-bottom: 8px;">Facturas Pagadas:</div>
                        <div class="flex justify-between">
                            <span>#001-000001</span>
                            <span>50.000 Gs.</span>
                        </div>
                    </div>
                    @endif

                    @if($mostrar_firma)
                    <!-- Firma -->
                    <div class="text-center mt-6">
                        <div style="border-top: 1px solid {{ $color_principal }}; width: 200px; margin: 0 auto 5px auto;"></div>
                        <div style="font-size: {{ $tamaño_fuente - 2 }}px; color: {{ $color_secundario }};">Firma y Sello</div>
                    </div>
                    @endif

                    <!-- Footer -->
                    <div class="text-center mt-4 pt-2" style="border-top: 1px solid {{ $color_secundario }}; font-size: {{ $tamaño_fuente - 2 }}px; color: {{ $color_secundario }};">
                        <div>{{ $mensaje_agradecimiento }}</div>
                        @if($pie_pagina)
                        <div class="mt-2">{{ $pie_pagina }}</div>
                        @endif
                        <div class="mt-2">{{ now()->format('d/m/Y H:i') }}</div>
                    </div>
                </div>  
            </div>
        </div>
        @endif
    </div>
</div>
