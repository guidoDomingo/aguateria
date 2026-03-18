<div class="max-w-2xl">

    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-1 flex items-center gap-2">
            <i class="fas fa-percentage text-orange-500"></i>
            Configuración de mora
        </h2>
        <p class="text-sm text-gray-500 mb-6">
            La mora se aplica automáticamente a las facturas vencidas. Podés elegir un monto fijo o un porcentaje sobre el subtotal.
        </p>

        <form wire:submit="guardar">

            <!-- Tipo de mora -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Tipo de mora <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model.live="mora_tipo" value="fijo"
                               class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Monto fijo (Gs.)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model.live="mora_tipo" value="porcentaje"
                               class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Porcentaje (%)</span>
                    </label>
                </div>
            </div>

            <!-- Valor -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    @if($mora_tipo === 'porcentaje')
                        Porcentaje de mora (%) <span class="text-red-500">*</span>
                    @else
                        Monto fijo de mora (Gs.) <span class="text-red-500">*</span>
                    @endif
                </label>
                <div class="flex items-center gap-2 max-w-xs">
                    <input type="number" wire:model="mora_valor"
                           min="0" step="{{ $mora_tipo === 'porcentaje' ? '0.01' : '100' }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm @error('mora_valor') border-red-500 @enderror">
                    <span class="text-gray-500 font-medium">{{ $mora_tipo === 'porcentaje' ? '%' : 'Gs.' }}</span>
                </div>
                @error('mora_valor')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                @if($mora_valor == 0)
                    <p class="text-xs text-gray-400 mt-1">Si el valor es 0, no se aplica mora (solo se actualizan los avisos).</p>
                @endif
            </div>

            <!-- Días de gracia -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Días de gracia <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-2 max-w-xs">
                    <input type="number" wire:model="mora_dias_gracia"
                           min="0" max="30"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm @error('mora_dias_gracia') border-red-500 @enderror">
                    <span class="text-gray-500">días</span>
                </div>
                @error('mora_dias_gracia')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">Días después del vencimiento antes de aplicar mora.</p>
            </div>

            <!-- Divisor -->
            <hr class="my-6 border-gray-200">

            <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-bell text-yellow-500"></i>
                Avisos de corte de servicio
            </h3>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Último aviso -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Último aviso a los <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="number" wire:model="meses_ultimo_aviso"
                               min="1" max="12"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm @error('meses_ultimo_aviso') border-red-500 @enderror">
                        <span class="text-gray-500 whitespace-nowrap">meses</span>
                    </div>
                    @error('meses_ultimo_aviso')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Se muestra el badge <span class="bg-orange-100 text-orange-800 px-1 rounded text-xs font-medium">Último aviso</span> en la factura.</p>
                </div>

                <!-- Desconexión -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Desconexión a los <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="number" wire:model="meses_desconexion"
                               min="1" max="12"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm @error('meses_desconexion') border-red-500 @enderror">
                        <span class="text-gray-500 whitespace-nowrap">meses</span>
                    </div>
                    @error('meses_desconexion')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Se muestra el badge <span class="bg-red-100 text-red-800 px-1 rounded text-xs font-medium">Desconexión</span> en la factura.</p>
                </div>
            </div>

            <!-- Ejemplo visual -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                <p class="text-xs font-medium text-gray-600 mb-3">Ejemplo con tu configuración actual:</p>
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="text-center">
                        <span class="block text-xs text-gray-500 mb-1">Mes 1</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Vencida</span>
                    </div>
                    <i class="fas fa-arrow-right text-gray-400 text-xs"></i>
                    <div class="text-center">
                        <span class="block text-xs text-gray-500 mb-1">Mes {{ $meses_ultimo_aviso }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">Último aviso</span>
                    </div>
                    <i class="fas fa-arrow-right text-gray-400 text-xs"></i>
                    <div class="text-center">
                        <span class="block text-xs text-gray-500 mb-1">Mes {{ $meses_desconexion }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Desconexión</span>
                    </div>
                </div>
            </div>

            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium flex items-center gap-2">
                <i class="fas fa-save"></i>
                Guardar configuración
            </button>
        </form>
    </div>

    <!-- Info cron -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
        <p class="text-sm font-medium text-gray-700 mb-1 flex items-center gap-2">
            <i class="fas fa-info-circle text-blue-500"></i>
            ¿Cuándo se aplican las moras?
        </p>
        <p class="text-sm text-gray-500">
            El sistema aplica las moras automáticamente cada día al mediodía mediante el scheduler de Laravel.
            También podés ejecutarlo manualmente desde la consola:
        </p>
        <code class="block bg-gray-800 text-green-400 text-xs p-3 rounded font-mono mt-2">
            php artisan aguateria:aplicar-moras
        </code>
    </div>
</div>
