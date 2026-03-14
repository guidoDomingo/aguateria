<div>
    <form wire:submit="guardar">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ $modo === 'crear' ? 'Crear Tarifa' : 'Editar Tarifa' }}
                    </h1>
                    <p class="text-gray-600">Configure el plan de precios del servicio</p>
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
                        {{ $modo === 'crear' ? 'Crear Tarifa' : 'Guardar Cambios' }}
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Información Básica -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-info-circle"></i>
                        Información Básica
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Código -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Código *
                            </label>
                            <input type="text" wire:model="codigo" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('codigo') border-red-500 @enderror"
                                   placeholder="RES001, COM001, etc.">
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
                                   placeholder="Residencial, Comercial, etc.">
                            @error('nombre')
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
                                      placeholder="Descripción de la tarifa..."></textarea>
                            @error('descripcion')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Monto Mensual -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Monto Mensual (Gs.) *
                            </label>
                            <input type="number" wire:model="monto_mensual" 
                                   step="1" min="0"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('monto_mensual') border-red-500 @enderror"
                                   placeholder="50000">
                            @error('monto_mensual')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Días de Vencimiento -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Días de Vencimiento *
                            </label>
                            <input type="number" wire:model="dias_vencimiento" 
                                   min="1" max="365"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('dias_vencimiento') border-red-500 @enderror"
                                   placeholder="30">
                            @error('dias_vencimiento')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Solo aplica para "Días corridos desde emisión"</p>
                        </div>
                    </div>
                    
                    <!-- Nueva sección: Configuración de Vencimiento -->
                    <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-md font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-calendar-alt"></i>
                            Configuración de Vencimiento
                        </h3>
                        
                        <div class="space-y-4">
                            <!-- Tipo de Vencimiento -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Método de Cálculo de Vencimiento *
                                </label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="radio" wire:model.live="tipo_vencimiento" value="dias_corridos" class="mr-2">
                                        <span class="text-sm">
                                            <strong>Días corridos desde emisión</strong>
                                            <span class="text-gray-500">- Usa los "Días de vencimiento" configurados arriba</span>
                                        </span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" wire:model.live="tipo_vencimiento" value="dia_fijo" class="mr-2">
                                        <span class="text-sm">
                                            <strong>Día fijo del mes</strong>
                                            <span class="text-gray-500">- Todos los clientes pagan el mismo día cada mes</span>
                                        </span>
                                    </label>
                                </div>
                                @error('tipo_vencimiento')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Campo para día fijo (solo si está seleccionado) -->
                            @if($tipo_vencimiento === 'dia_fijo')
                                <div class="ml-6 p-3 border-l-2 border-blue-200 bg-blue-50">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Día del mes para vencimiento *
                                    </label>
                                    <select wire:model="dia_fijo_vencimiento" 
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('dia_fijo_vencimiento') border-red-500 @enderror">
                                        @for($dia = 1; $dia <= 31; $dia++)
                                            <option value="{{ $dia }}">Día {{ $dia }}</option>
                                        @endfor
                                    </select>
                                    @error('dia_fijo_vencimiento')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-xs text-gray-500 mt-2">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Ejemplo:</strong> Si selecciona "Día 5", todas las facturas vencerán el día 5 del mes siguiente al período facturado.
                                    </p>
                                </div>
                            @endif

                            <!-- Explicación del funcionamiento -->
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                <div class="flex items-start">
                                    <i class="fas fa-lightbulb text-yellow-500 mt-0.5 mr-2"></i>
                                    <div class="text-sm text-yellow-800">
                                        <strong>¿Cómo funciona?</strong>
                                        <ul class="mt-1 space-y-1 text-xs">
                                            @if($tipo_vencimiento === 'dias_corridos')
                                                <li>• <strong>Días corridos:</strong> Si emite una factura el 13/03, y configuró 30 días, vencerá el 12/04</li>
                                                <li>• Cada factura tiene diferente fecha de vencimiento según cuándo se emitió</li>
                                            @else
                                                <li>• <strong>Día fijo:</strong> Si configuró día {{ $dia_fijo_vencimiento }}, todas las facturas vencen el día {{ $dia_fijo_vencimiento }} del mes siguiente</li>
                                                <li>• Ejemplo: Facturas de Marzo → Vencen el {{ $dia_fijo_vencimiento }}/04, Facturas de Abril → Vencen el {{ $dia_fijo_vencimiento }}/05</li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuración de Mora (continuación del archivo) -->
                <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle"></i>
                        Configuración de Mora
                    </h2>

                    <div class="space-y-4">
                        <!-- Generar Mora -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" wire:model.live="genera_mora" class="mr-2">
                                <span class="text-sm font-medium">Generar mora por atraso</span>
                            </label>
                        </div>

                        @if($genera_mora)
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pl-6 border-l-2 border-blue-200">
                                <!-- Tipo de Mora -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Tipo de Mora
                                    </label>
                                    <select wire:model="tipo_mora" 
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="fijo">Monto Fijo</option>
                                        <option value="porcentaje">Porcentaje</option>
                                    </select>
                                </div>

                                <!-- Monto de Mora -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ $tipo_mora === 'porcentaje' ? 'Porcentaje (%)' : 'Monto Fijo (Gs.)' }}
                                    </label>
                                    <input type="number" wire:model="monto_mora" 
                                           step="{{ $tipo_mora === 'porcentaje' ? '0.01' : '1' }}" 
                                           min="0" 
                                           max="{{ $tipo_mora === 'porcentaje' ? '100' : '' }}"
                                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="{{ $tipo_mora === 'porcentaje' ? '5.00' : '10000' }}">
                                    @error('monto_mora')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Días de Gracia -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Días de Gracia
                                    </label>
                                    <input type="number" wire:model="dias_gracia" 
                                           min="0" max="30"
                                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="5">
                                    @error('dias_gracia')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Configuración de Cortes -->
                <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-power-off"></i>
                        Configuración de Cortes
                    </h2>

                    <div class="space-y-4">
                        <!-- Corte Automático -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" wire:model.live="corte_automatico" class="mr-2">
                                <span class="text-sm font-medium">Activar corte automático</span>
                            </label>
                        </div>

                        @if($corte_automatico)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pl-6 border-l-2 border-red-200">
                                <!-- Días para Corte -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Días después del vencimiento *
                                    </label>
                                    <input type="number" wire:model="dias_corte" 
                                           min="0" max="365"
                                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="15">
                                    @error('dias_corte')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Costo de Reconexión -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Costo de Reconexión (Gs.)
                                    </label>
                                    <input type="number" wire:model="costo_reconexion" 
                                           step="1" min="0"
                                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="25000">
                                    @error('costo_reconexion')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        @endif
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
                            <input type="radio" wire:model="estado" value="activa" class="mr-2">
                            <span class="text-sm">Activa</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" wire:model="estado" value="inactiva" class="mr-2">
                            <span class="text-sm">Inactiva</span>
                        </label>
                    </div>
                    @error('estado')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Información -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Información</h3>
                            <div class="mt-1 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>La mora se calcula después del período de gracia</li>
                                    <li>El corte automático se ejecuta según los días configurados</li>
                                    <li>Los clientes pueden tener tarifas diferentes</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Campos Obligatorios -->
        <div class="mt-6">
            <p class="text-sm text-gray-600">* Campos obligatorios</p>
        </div>
    </form>
</div>