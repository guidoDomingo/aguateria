<div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $esEdicion ? 'Editar Factura' : 'Nueva Factura' }}
            </h1>
            <p class="text-gray-600">
                {{ $esEdicion ? 'Modifica los datos de la factura' : 'Genera una nueva factura individual' }}
            </p>
        </div>
        <button wire:click="cancelar" 
                class="text-gray-600 hover:text-gray-800">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <!-- Formulario -->
    <form wire:submit="generarFactura">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Formulario Principal -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                    <!-- Selección de Cliente y Período -->
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-user-invoice mr-2"></i>
                            Datos de Facturación
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Cliente -->
                            <div class="md:col-span-2">
                                <label for="cliente_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Cliente *
                                </label>
                                <select wire:model="cliente_id" 
                                        id="cliente_id" 
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('cliente_id') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                                    <option value="">Selecciona un cliente...</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente['id'] }}">
                                            {{ $cliente['nombre_completo'] }} - {{ $cliente['direccion'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('cliente_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                
                                @if($cliente && is_object($cliente))
                                    <div class="mt-2 p-3 bg-blue-50 rounded-lg">
                                        <div class="text-sm text-blue-800">
                                            <p><strong>Barrio:</strong> {{ $cliente->barrio->nombre ?? 'Sin barrio' }}</p>
                                            <p><strong>Tarifa:</strong> {{ $cliente->tarifa->nombre ?? 'Sin tarifa' }}
                                                @if($cliente->tarifa)
                                                    - {{ number_format($cliente->tarifa->monto_mensual, 0, ',', '.') }} Gs.
                                                @endif
                                            </p>
                                            <p><strong>Cobrador:</strong> {{ $cliente->cobrador->nombre ?? 'Sin cobrador' }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Período de Facturación -->
                            <div>
                                <label for="periodo_facturacion_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Período de Facturación *
                                </label>
                                <select wire:model.blur="periodo_facturacion_id" 
                                        id="periodo_facturacion_id" 
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('periodo_facturacion_id') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                                    <option value="">Selecciona un período...</option>
                                    @foreach($periodos as $periodo)
                                        <option value="{{ $periodo->id }}">
                                            {{ $periodo->nombre }} - {{ ucfirst($periodo->estado) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('periodo_facturacion_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Fecha de Vencimiento -->
                            <div>
                                <label for="fecha_vencimiento" class="block text-sm font-medium text-gray-700 mb-2">
                                    Fecha de Vencimiento *
                                </label>
                                <input wire:model.blur="fecha_vencimiento" 
                                       id="fecha_vencimiento" 
                                       type="date" 
                                       min="{{ now()->format('Y-m-d') }}"
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('fecha_vencimiento') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                                @error('fecha_vencimiento')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Descuento Aplicado -->
                            <div>
                                <label for="descuento_aplicado" class="block text-sm font-medium text-gray-700 mb-2">
                                    Descuento Adicional (%)
                                </label>
                                <input wire:model.blur="descuento_aplicado" 
                                       wire:change="updatedDescuentoAplicado"
                                       id="descuento_aplicado" 
                                       type="number" 
                                       min="0" 
                                       max="100" 
                                       step="0.01"
                                       placeholder="0.00"
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('descuento_aplicado') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                                @error('descuento_aplicado')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Descuento adicional al configurado en la tarifa</p>
                            </div>

                            <!-- Observaciones -->
                            <div class="md:col-span-2">
                                <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-2">
                                    Observaciones
                                </label>
                                <textarea wire:model.blur="observaciones" 
                                          id="observaciones" 
                                          rows="3" 
                                          placeholder="Notas adicionales para esta factura..."
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
                                    Procesando...
                                @else
                                    <i class="fas fa-file-invoice mr-2"></i>
                                    {{ $esEdicion ? 'Actualizar Factura' : 'Generar Factura' }}
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vista Previa -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden sticky top-6">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-eye mr-2"></i>
                            Vista Previa
                        </h3>
                    </div>
                    
                    @if(!empty($preview))
                        <div class="p-6">
                            <!-- Información del Cliente -->
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <h4 class="font-medium text-gray-900 mb-2">Cliente</h4>
                                <div class="text-sm text-gray-600">
                                    <p>{{ $cliente->nombre ?? '' }} {{ $cliente->apellido ?? '' }}</p>
                                    <p>{{ $cliente->cedula ?? '' }}</p>
                                    <p>{{ $cliente->direccion ?? '' }}</p>
                                </div>
                            </div>

                            <!-- Período -->
                            @if($periodo)
                                <div class="mb-4 pb-4 border-b border-gray-200">
                                    <h4 class="font-medium text-gray-900 mb-2">Período</h4>
                                    <div class="text-sm text-gray-600">
                                        <p>{{ \Carbon\Carbon::createFromDate($periodo->año, $periodo->mes, 1)->locale('es')->isoFormat('MMMM YYYY') }}</p>
                                    </div>
                                </div>
                            @endif

                            <!-- Cálculos -->
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Precio Base:</span>
                                    <span class="font-medium">{{ number_format($preview['precio_base'] ?? 0, 0, ',', '.') }} Gs.</span>
                                </div>

                                @if(($preview['descuento_tarifa'] ?? 0) > 0)
                                    <div class="flex justify-between text-sm text-green-600">
                                        <span>Desc. Tarifa ({{ $preview['porcentaje_descuento_tarifa'] ?? 0 }}%):</span>
                                        <span>-{{ number_format($preview['descuento_tarifa'] ?? 0, 0, ',', '.') }} Gs.</span>
                                    </div>
                                @endif

                                @if(($preview['descuento_personalizado'] ?? 0) > 0)
                                    <div class="flex justify-between text-sm text-green-600">
                                        <span>Desc. Personal ({{ $preview['porcentaje_descuento_personalizado'] ?? 0 }}%):</span>
                                        <span>-{{ number_format($preview['descuento_personalizado'] ?? 0, 0, ',', '.') }} Gs.</span>
                                    </div>
                                @endif

                                @if(($preview['descuento_adicional'] ?? 0) > 0)
                                    <div class="flex justify-between text-sm text-blue-600">
                                        <span>Desc. Adicional ({{ $descuento_aplicado }}%):</span>
                                        <span>-{{ number_format($preview['descuento_adicional'] ?? 0, 0, ',', '.') }} Gs.</span>
                                    </div>
                                @endif

                                <hr class="my-3">

                                <div class="flex justify-between text-lg font-bold">
                                    <span>Total:</span>
                                    <span>{{ number_format($preview['total'] ?? 0, 0, ',', '.') }} Gs.</span>
                                </div>
                            </div>

                            <!-- Información adicional -->
                            @if($fecha_vencimiento)
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <div class="text-sm text-gray-600">
                                        <p><strong>Vencimiento:</strong> {{ \Carbon\Carbon::parse($fecha_vencimiento)->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="p-6 text-center text-gray-500">
                            <i class="fas fa-calculator text-3xl text-gray-300 mb-3"></i>
                            <p>Selecciona un cliente y período para ver la vista previa</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </form>

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