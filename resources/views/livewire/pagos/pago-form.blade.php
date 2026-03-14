<div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Registrar Pago</h1>
            <p class="text-gray-600">Registra un nuevo pago y aplicalo a las facturas del cliente</p>
        </div>
        <button wire:click="cancelar" 
                class="text-gray-600 hover:text-gray-800">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <!-- Mensajes de error generales -->
    @if ($errors->has('general'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex">
                <i class="fas fa-exclamation-triangle text-red-400 mr-3 mt-1"></i>
                <div>
                    <h3 class="text-sm font-medium text-red-800">Error al registrar el pago</h3>
                    <div class="mt-1 text-sm text-red-700">
                        {{ $errors->first('general') }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->has('facturas'))
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex">
                <i class="fas fa-exclamation-circle text-yellow-400 mr-3 mt-1"></i>
                <div>
                    <h3 class="text-sm font-medium text-yellow-800">Facturas requeridas</h3>
                    <div class="mt-1 text-sm text-yellow-700">
                        {{ $errors->first('facturas') }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    <form wire:submit="registrarPago">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Formulario Principal -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Datos del Pago -->
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-money-bill-wave mr-2"></i>
                            Datos del Pago
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Búsqueda por cédula -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Buscar cliente por cédula
                                </label>
                                <div class="flex gap-2">
                                    <input wire:model="buscarClientePorDocumento" 
                                           type="text" 
                                           placeholder="Ej: 1234567"
                                           class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    <button type="button" 
                                            wire:click="buscarClientePorDoc"
                                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Cliente -->
                            <div class="md:col-span-2">
                                <label for="cliente_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Cliente *
                                </label>
                                <select wire:model.live="cliente_id" 
                                        id="cliente_id" 
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('cliente_id') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                                    <option value="">Selecciona un cliente...</option>
                                    @foreach($clientes as $clienteOption)
                                        <option value="{{ $clienteOption['id'] }}">
                                            {{ $clienteOption['nombre_completo'] }} - {{ $clienteOption['direccion'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('cliente_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                
                                @if($cliente)
                                    <div class="mt-2 p-3 bg-blue-50 rounded-lg">
                                        <div class="text-sm text-blue-800">
                                            <p><strong>Cliente:</strong> {{ $cliente->nombre }} {{ $cliente->apellido }}</p>
                                            <p><strong>Cédula:</strong> {{ $cliente->cedula }}</p>
                                            <p><strong>Dirección:</strong> {{ $cliente->direccion }}</p>
                                            <p><strong>Barrio:</strong> {{ $cliente->barrio->nombre ?? 'Sin barrio' }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Monto -->
                            <div>
                                <label for="monto" class="block text-sm font-medium text-gray-700 mb-2">
                                    💰 Monto Total Recibido del Cliente *
                                </label>
                                <input wire:model.blur="monto" 
                                       id="monto" 
                                       type="number" 
                                       step="1" 
                                       min="0"
                                       placeholder="Ej: 25000"
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('monto') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                                <p class="mt-1 text-xs text-gray-500">💡 Este es el dinero total que recibió del cliente. Luego distribuya este monto entre las facturas pendientes.</p>
                                @error('monto')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Fecha de Pago -->
                            <div>
                                <label for="fecha_pago" class="block text-sm font-medium text-gray-700 mb-2">
                                    Fecha de Pago *
                                </label>
                                <input wire:model.blur="fecha_pago" 
                                       id="fecha_pago" 
                                       type="date" 
                                       max="{{ now()->format('Y-m-d') }}"
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('fecha_pago') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                                @error('fecha_pago')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Método de Pago -->
                            <div>
                                <label for="metodo_pago_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Método de Pago *
                                </label>
                                <select wire:model.blur="metodo_pago_id" 
                                        id="metodo_pago_id" 
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('metodo_pago_id') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                                    <option value="">Selecciona método...</option>
                                    @foreach($metodosPago as $metodo)
                                        <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('metodo_pago_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Cobrador -->
                            <div>
                                <label for="cobrador_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Cobrador *
                                </label>
                                <select wire:model.blur="cobrador_id" 
                                        id="cobrador_id" 
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('cobrador_id') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                                    <option value="">Selecciona cobrador...</option>
                                    @foreach($cobradores as $cobrador)
                                        <option value="{{ $cobrador->id }}">{{ $cobrador->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('cobrador_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Observaciones -->
                            <div class="md:col-span-2">
                                <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-2">
                                    Observaciones
                                </label>
                                <textarea wire:model.blur="observaciones" 
                                          id="observaciones" 
                                          rows="3" 
                                          placeholder="Notas adicionales sobre el pago..."
                                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('observaciones') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"></textarea>
                                @error('observaciones')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Facturas del Cliente -->
                @if(!empty($facturasCliente))
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-file-invoice mr-2"></i>
                            📋 Distribución del Pago ({{ count($facturasCliente) }} facturas pendientes)
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">🎯 Distribuya el monto recibido entre estas facturas pendientes. El total distribuido debe coincidir con el monto recibido.</p>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Seleccionar
                                        </th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Factura
                                        </th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Período
                                        </th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Vencimiento
                                        </th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Total
                                        </th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Pendiente
                                        </th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            💸 Monto a Aplicar
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($facturasCliente as $index => $factura)
                                        <tr class="{{ $factura['esta_vencida'] ? 'bg-red-50' : '' }}">
                                            <td class="px-4 py-3">
                                                <input type="checkbox" 
                                                       wire:click="toggleFactura({{ $index }}, $event.target.checked)"
                                                       {{ $factura['seleccionada'] ? 'checked' : '' }}
                                                       class="rounded border-gray-300 text-blue-600 focus:border-blue-500 focus:ring-blue-500">
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="text-sm font-medium text-gray-900">
                                                    #{{ $factura['numero'] }}
                                                </div>
                                                @if($factura['esta_vencida'])
                                                    <span class="text-xs text-red-600 font-medium">VENCIDA</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                {{ $factura['periodo'] }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($factura['vencimiento'])->format('d/m/Y') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                {{ number_format($factura['total'], 0, ',', '.') }} Gs.
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="text-sm font-medium text-red-600">
                                                    {{ number_format($factura['pendiente'], 0, ',', '.') }} Gs.
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex flex-col">
                                                    <input type="number" 
                                                           wire:model.blur="facturasCliente.{{ $index }}.monto_a_pagar"
                                                           wire:change="updatedFacturasMonto($event.target.value, 'facturas.{{ $index }}.monto_a_pagar')"
                                                           min="0" 
                                                           max="{{ $factura['pendiente'] }}"
                                                           step="1"
                                                           placeholder="0"
                                                           class="w-24 rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                                                    <button type="button" 
                                                            wire:click="aplicarMontoCompleto({{ $index }})"
                                                            class="text-xs text-blue-600 hover:text-blue-800 mt-1">
                                                        Aplicar todo
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Botones -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <div class="flex items-center justify-between">
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
                                    <i class="fas fa-save mr-2"></i>
                                    Registrar Pago
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen Lateral -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden sticky top-6">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-calculator mr-2"></i>
                            Resumen del Pago
                        </h3>
                    </div>
                    
                    @if($cliente)
                        <div class="p-6">
                            <!-- Información del Cliente -->
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <h4 class="font-medium text-gray-900 mb-2">👤 Cliente</h4>
                                <div class="text-sm text-gray-600">
                                    <p>{{ $cliente->nombre }} {{ $cliente->apellido }}</p>
                                    <p>{{ $cliente->cedula }}</p>
                                </div>
                            </div>

                            <!-- Cálculos del Pago -->
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">💰 Monto Recibido:</span>
                                    <span class="font-medium">{{ number_format(floatval($monto), 0, ',', '.') }} Gs.</span>
                                </div>

                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">📊 Distribuido en Facturas:</span>
                                    <span class="font-medium text-blue-600">{{ number_format($montoTotal, 0, ',', '.') }} Gs.</span>
                                </div>

                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ $montoRestante < 0 ? '⚠️ Exceso:' : ($montoRestante > 0 ? '💸 Por Distribuir:' : '✅ Balance:') }}</span>
                                    <span class="font-medium {{ $montoRestante < 0 ? 'text-red-600' : ($montoRestante > 0 ? 'text-orange-600' : 'text-green-600') }}">
                                        {{ number_format(abs($montoRestante), 0, ',', '.') }} Gs.
                                    </span>
                                </div>

                                @if($montoRestante != 0)
                                    <div class="text-xs text-gray-500 bg-gray-50 p-2 rounded">
                                        @if($montoRestante > 0)
                                            💡 Aún puede distribuir {{ number_format($montoRestante, 0, ',', '.') }} Gs. adicionales
                                        @else
                                            ⚠️ Ha excedido el monto recibido en {{ number_format(abs($montoRestante), 0, ',', '.') }} Gs.
                                        @endif
                                    </div>
                                @endif

                                <hr class="my-3">

                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Facturas Seleccionadas:</span>
                                    <span class="font-medium">{{ count($facturasSeleccionadas) }}</span>
                                </div>
                            </div>

                            <!-- Alertas -->
                            @if($montoRestante < 0)
                                <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <div class="flex">
                                        <i class="fas fa-exclamation-triangle text-red-400 mr-2 mt-0.5"></i>
                                        <div class="text-sm text-red-700">
                                            <p class="font-medium">Monto insuficiente</p>
                                            <p>El monto del pago es menor al total de las facturas seleccionadas.</p>
                                        </div>
                                    </div>
                                </div>
                            @elseif($montoRestante > 0 && count($facturasSeleccionadas) > 0)
                                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <div class="flex">
                                        <i class="fas fa-info-circle text-yellow-400 mr-2 mt-0.5"></i>
                                        <div class="text-sm text-yellow-700">
                                            <p class="font-medium">Pago parcial</p>
                                            <p>Quedará un saldo a favor del cliente.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Lista de facturas seleccionadas -->
                            @if(count($facturasSeleccionadas) > 0)
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <h4 class="font-medium text-gray-900 mb-2">Facturas a Pagar</h4>
                                    <div class="space-y-2">
                                        @foreach($facturasSeleccionadas as $factura)
                                            @php
                                                $facturaData = collect($facturasCliente)->firstWhere('id', $factura['factura_id']);
                                            @endphp
                                            @if($facturaData)
                                                <div class="flex justify-between text-xs">
                                                    <span class="text-gray-600">#{{ $facturaData['numero'] }}</span>
                                                    <span class="font-medium">{{ number_format($factura['monto'], 0, ',', '.') }} Gs.</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="p-6 text-center text-gray-500">
                            <i class="fas fa-user-plus text-3xl text-gray-300 mb-3"></i>
                            <p>Selecciona un cliente para comenzar</p>
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