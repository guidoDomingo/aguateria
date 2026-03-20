<div class="max-w-2xl">

    <!-- Info actual -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 flex items-start gap-3">
        <i class="fas fa-calendar-check text-blue-500 mt-0.5 text-lg"></i>
        <div>
            <p class="text-sm font-medium text-blue-800">Próxima facturación automática</p>
            <p class="text-blue-700 font-semibold">
                {{ $proximaFecha->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
                a las <strong>{{ $horaActual }} hs.</strong>
            </p>
            <p class="text-xs text-blue-600 mt-1">
                El sistema genera las facturas automáticamente el día <strong>{{ $diaActual }}</strong> de cada mes.
            </p>
        </div>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-1 flex items-center gap-2">
            <i class="fas fa-cog text-gray-500"></i>
            Programación de facturación automática
        </h2>
        <p class="text-sm text-gray-500 mb-6">
            Las facturas se generarán automáticamente en el día y hora configurados. Máximo día 28 para compatibilidad con febrero.
        </p>

        <form wire:submit="guardar">

            <!-- Día -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Día del mes <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-7 gap-2 mb-2">
                    @for($d = 1; $d <= 28; $d++)
                        <button type="button"
                                wire:click="$set('dia_facturacion', {{ $d }})"
                                class="h-10 w-full rounded-lg text-sm font-medium border transition-colors
                                    {{ $dia_facturacion == $d
                                        ? 'bg-blue-600 text-white border-blue-600'
                                        : 'bg-white text-gray-700 border-gray-200 hover:border-blue-300 hover:bg-blue-50' }}">
                            {{ $d }}
                        </button>
                    @endfor
                </div>
                @error('dia_facturacion')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">
                    Día seleccionado: <strong class="text-gray-800">{{ $dia_facturacion }}</strong> de cada mes
                </p>
            </div>

            <!-- Hora -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Hora de ejecución <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-3">
                    <!-- Horas -->
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">Hora</label>
                        <select wire:model="hora"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm @error('hora') border-red-500 @enderror">
                            @foreach($horas as $h)
                                <option value="{{ $h }}">{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}</option>
                            @endforeach
                        </select>
                        @error('hora')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <span class="text-2xl font-bold text-gray-400 mt-4">:</span>

                    <!-- Minutos -->
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">Minutos</label>
                        <select wire:model="minuto"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm @error('minuto') border-red-500 @enderror">
                            @foreach($minutos as $m)
                                <option value="{{ $m }}">{{ $m }}</option>
                            @endforeach
                        </select>
                        @error('minuto')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Preview -->
                    <div class="mt-4 px-4 py-2 bg-gray-100 rounded-lg text-center min-w-[80px]">
                        <p class="text-xs text-gray-500">Vista previa</p>
                        <p class="text-lg font-bold text-gray-800">
                            {{ str_pad($hora, 2, '0', STR_PAD_LEFT) }}:{{ $minuto }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- TIPO DE COMPROBANTE --}}
            <div class="mb-6">
                <h3 class="text-base font-semibold text-gray-800 mb-3">
                    <i class="fas fa-file-alt mr-2 text-blue-600"></i>
                    Tipo de comprobante para clientes
                </h3>
                <p class="text-sm text-gray-500 mb-3">Elige qué documento se genera al imprimir o ver PDF desde el módulo de facturas.</p>
                <div class="grid grid-cols-2 gap-4 max-w-lg">
                    <label class="cursor-pointer" wire:click="$set('tipo_comprobante', 'factura')">
                        <div class="border-2 rounded-lg p-4 text-center transition-all
                            {{ $tipo_comprobante === 'factura' ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <i class="fas fa-file-invoice text-2xl mb-2 {{ $tipo_comprobante === 'factura' ? 'text-blue-600' : 'text-gray-500' }}"></i>
                            <div class="font-semibold text-gray-800">Factura</div>
                            <div class="text-xs text-gray-500 mt-1">Formato de factura estándar</div>
                        </div>
                    </label>
                    <label class="cursor-pointer" wire:click="$set('tipo_comprobante', 'recibo')">
                        <div class="border-2 rounded-lg p-4 text-center transition-all
                            {{ $tipo_comprobante === 'recibo' ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <i class="fas fa-receipt text-2xl mb-2 {{ $tipo_comprobante === 'recibo' ? 'text-blue-600' : 'text-gray-500' }}"></i>
                            <div class="font-semibold text-gray-800">Recibo</div>
                            <div class="text-xs text-gray-500 mt-1">Usa la plantilla configurada en Config. Recibos</div>
                        </div>
                    </label>
                </div>
                @if($tipo_comprobante === 'recibo')
                <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700 max-w-lg">
                    <i class="fas fa-info-circle mr-1"></i>
                    Se usará la plantilla configurada en <a href="{{ route('configuracion.recibos') }}" class="underline font-medium">Configuración de Recibos</a>.
                    Solo aplica para facturas <strong>pagadas</strong> (que ya tengan un recibo generado).
                </div>
                @endif
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-6">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <strong>Importante:</strong> Los cambios aplican desde el próximo ciclo. Las facturas ya generadas no se ven afectadas.
                </p>
            </div>

            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium flex items-center gap-2">
                <i class="fas fa-save"></i>
                Guardar configuración
            </button>
        </form>
    </div>

    <!-- Ejecución manual -->
    <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-1 flex items-center gap-2">
            <i class="fas fa-play-circle" style="color:#16a34a"></i>
            Ejecución manual
        </h2>
        <p class="text-sm text-gray-500 mb-4">
            Ejecuta la facturación ahora mismo para tu empresa, ignorando el día y hora configurados.
            Útil para pruebas o correcciones puntuales.
        </p>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
            <p class="text-sm text-yellow-800">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                <strong>Atención:</strong> Si el período ya fue facturado, se resetea a "abierto" y se vuelve a procesar.
            </p>
        </div>

        <button type="button"
                wire:click="ejecutarAhora"
                wire:loading.attr="disabled"
                style="background-color:#16a34a"
                class="hover:opacity-90 text-white px-6 py-2 rounded-lg font-medium inline-flex items-center gap-2">
            <span wire:loading.remove wire:target="ejecutarAhora">
                <i class="fas fa-play mr-1"></i> Ejecutar ahora
            </span>
            <span wire:loading wire:target="ejecutarAhora" style="display:none">
                <i class="fas fa-spinner fa-spin mr-1"></i> Procesando...
            </span>
        </button>

        <!-- Output del cron -->
        @if($outputCron)
        <div class="mt-4">
            <p class="text-xs font-medium text-gray-600 mb-1 flex items-center gap-1">
                <i class="fas fa-terminal"></i> Resultado de la ejecución
            </p>
            <pre class="bg-gray-900 text-green-400 text-xs p-4 rounded-lg overflow-x-auto whitespace-pre-wrap font-mono max-h-64 overflow-y-auto">{{ $outputCron }}</pre>
            <button type="button" wire:click="$set('outputCron', null)"
                    class="mt-2 text-xs text-gray-500 hover:text-gray-700">
                <i class="fas fa-times mr-1"></i> Cerrar
            </button>
        </div>
        @endif
    </div>

    <!-- Info del cron -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mt-6">
        <p class="text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
            <i class="fas fa-terminal text-gray-500"></i>
            Configuración del servidor (cron)
        </p>
        <p class="text-xs text-gray-500 mb-2">Agrega esta línea al crontab del servidor para activar la automatización:</p>
        <code class="block bg-gray-800 text-green-400 text-xs p-3 rounded font-mono">
            * * * * * php {{ base_path() }}/artisan schedule:run >> /dev/null 2>&1
        </code>
    </div>
</div>
