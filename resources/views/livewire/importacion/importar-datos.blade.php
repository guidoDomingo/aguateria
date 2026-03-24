<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Importar Datos</h1>
            <p class="text-sm text-gray-500 mt-1">Cargá clientes y deudas retroactivas desde Excel o manualmente.</p>
        </div>
    </div>

    {{-- TABS --}}
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex gap-6">
            <button wire:click="$set('tab', 'clientes')"
                class="pb-3 text-sm font-medium border-b-2 transition-colors
                    {{ $tab === 'clientes' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                <i class="fas fa-users mr-2"></i>Importar Clientes
            </button>
            <button wire:click="$set('tab', 'deudas')"
                class="pb-3 text-sm font-medium border-b-2 transition-colors
                    {{ $tab === 'deudas' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                <i class="fas fa-file-invoice-dollar mr-2"></i>Deudas Retroactivas
            </button>
        </nav>
    </div>

    {{-- ======= TAB CLIENTES ======= --}}
    @if($tab === 'clientes')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Upload Excel --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-1">
                <i class="fas fa-file-excel text-green-600 mr-2"></i>Importar desde Excel / CSV
            </h2>
            <p class="text-sm text-gray-500 mb-4">Subí un archivo con los datos de los clientes.</p>

            <button wire:click="descargarPlantillaClientes"
                    class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800 mb-4 font-medium">
                <i class="fas fa-download"></i> Descargar plantilla CSV
            </button>

            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center mb-4">
                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                <p class="text-sm text-gray-600 mb-2">Seleccioná tu archivo Excel o CSV</p>
                <input wire:model="archivoClientes" type="file" accept=".xlsx,.xls,.csv"
                       class="text-sm text-gray-500">
            </div>

            @error('archivoClientes')
                <p class="text-red-600 text-sm mb-2">{{ $message }}</p>
            @enderror

            <button wire:click="importarClientes" wire:loading.attr="disabled"
                    style="background-color:#16a34a;color:white;"
                    class="w-full py-2 px-4 rounded-lg font-medium text-sm hover:opacity-90 disabled:opacity-50">
                <span wire:loading.remove wire:target="importarClientes">
                    <i class="fas fa-upload mr-2"></i>Importar clientes
                </span>
                <span wire:loading wire:target="importarClientes">
                    <i class="fas fa-spinner fa-spin mr-2"></i>Importando...
                </span>
            </button>

            @if($resultadoClientes !== null)
            <div class="mt-4 rounded-lg border p-4 {{ count($resultadoClientes['errores']) > 0 ? 'bg-yellow-50 border-yellow-300' : 'bg-green-50 border-green-300' }}">
                <p class="font-semibold text-sm mb-1">Resultado:</p>
                <p class="text-sm text-green-700"><i class="fas fa-check-circle mr-1"></i>Creados: <strong>{{ $resultadoClientes['creados'] }}</strong></p>
                <p class="text-sm text-gray-600"><i class="fas fa-minus-circle mr-1"></i>Omitidos (ya existían): <strong>{{ $resultadoClientes['omitidos'] }}</strong></p>
                @foreach($resultadoClientes['errores'] as $error)
                    <p class="text-sm text-red-600 mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $error }}</p>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Info columnas --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>Columnas del archivo
            </h2>
            <div class="space-y-3">
                @foreach([
                    ['nombre', 'Nombre del cliente', true],
                    ['apellido', 'Apellido', false],
                    ['cedula', 'Cédula de identidad (debe ser única)', false],
                    ['telefono', 'Teléfono de contacto', false],
                    ['email', 'Correo electrónico', false],
                    ['direccion', 'Dirección del domicilio', false],
                    ['barrio', 'Nombre del barrio (se crea si no existe)', false],
                    ['tarifa', 'Nombre de la tarifa asignada', false],
                ] as [$col, $desc, $req])
                <div class="flex items-start gap-3">
                    <code class="bg-gray-100 text-gray-800 px-2 py-0.5 rounded text-xs font-mono min-w-[110px]">{{ $col }}</code>
                    <span class="text-sm text-gray-600">
                        {{ $desc }}
                        @if($req) <span class="text-red-500 font-bold ml-1">*</span> @endif
                    </span>
                </div>
                @endforeach
            </div>
            <p class="text-xs text-gray-400 mt-4">* obligatorio. Si la cédula ya existe en el sistema, la fila se omite.</p>
        </div>

    </div>
    @endif

    {{-- ======= TAB DEUDAS ======= --}}
    @if($tab === 'deudas')
    <div class="space-y-6">

        {{-- Upload Excel Deudas --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-1">
                <i class="fas fa-file-excel text-green-600 mr-2"></i>Importar deudas desde Excel / CSV
            </h2>
            <p class="text-sm text-gray-500 mb-4">El archivo debe contener la cédula del cliente y el monto adeudado.</p>

            <div class="flex items-center gap-4 mb-4">
                <button wire:click="descargarPlantillaDeudas"
                        class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800 font-medium">
                    <i class="fas fa-download"></i> Descargar plantilla CSV
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center mb-3">
                        <input wire:model="archivoDeudas" type="file" accept=".xlsx,.xls,.csv" class="text-sm text-gray-500">
                    </div>
                    @error('archivoDeudas')
                        <p class="text-red-600 text-sm mb-2">{{ $message }}</p>
                    @enderror
                    <button wire:click="importarDeudas" wire:loading.attr="disabled"
                            style="background-color:#d97706;color:white;"
                            class="w-full py-2 px-4 rounded-lg font-medium text-sm hover:opacity-90 disabled:opacity-50">
                        <span wire:loading.remove wire:target="importarDeudas">
                            <i class="fas fa-upload mr-2"></i>Importar deudas
                        </span>
                        <span wire:loading wire:target="importarDeudas">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Importando...
                        </span>
                    </button>
                </div>
                <div class="text-sm text-gray-600 space-y-2">
                    <p class="font-semibold">Columnas requeridas:</p>
                    <div class="space-y-1">
                        @foreach([
                            ['cedula_cliente', 'Cédula del cliente *'],
                            ['mes', 'Mes (1-12) *'],
                            ['año', 'Año (ej: 2025) *'],
                            ['monto', 'Monto en Gs. *'],
                            ['fecha_vencimiento', 'Fecha venc. dd/mm/yyyy (opcional)'],
                            ['observaciones', 'Observación (opcional)'],
                        ] as [$col, $desc])
                        <div class="flex gap-2">
                            <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs font-mono">{{ $col }}</code>
                            <span class="text-xs text-gray-500">{{ $desc }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @if($resultadoDeudas !== null)
            <div class="mt-4 rounded-lg border p-4 {{ count($resultadoDeudas['errores']) > 0 ? 'bg-yellow-50 border-yellow-300' : 'bg-green-50 border-green-300' }}">
                <p class="font-semibold text-sm mb-1">Resultado importación:</p>
                <p class="text-sm text-green-700"><i class="fas fa-check-circle mr-1"></i>Deudas creadas: <strong>{{ $resultadoDeudas['creados'] }}</strong></p>
                @foreach($resultadoDeudas['errores'] as $error)
                    <p class="text-sm text-red-600 mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $error }}</p>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Carga manual --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-keyboard text-purple-600 mr-2"></i>Carga manual
                </h2>
                <button wire:click="agregarFila"
                        style="background-color:#7c3aed;color:white;"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium hover:opacity-90">
                    <i class="fas fa-plus"></i> Agregar fila
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 pr-3 text-xs font-medium text-gray-500 uppercase min-w-[200px]">Cliente</th>
                            <th class="text-left py-2 pr-3 text-xs font-medium text-gray-500 uppercase w-20">Mes</th>
                            <th class="text-left py-2 pr-3 text-xs font-medium text-gray-500 uppercase w-24">Año</th>
                            <th class="text-left py-2 pr-3 text-xs font-medium text-gray-500 uppercase w-32">Monto Gs.</th>
                            <th class="text-left py-2 pr-3 text-xs font-medium text-gray-500 uppercase w-32">Vencimiento</th>
                            <th class="text-left py-2 pr-3 text-xs font-medium text-gray-500 uppercase">Observación</th>
                            <th class="w-8"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($filasDeuda as $i => $fila)
                        <tr>
                            <td class="py-2 pr-3">
                                <select wire:model="filasDeuda.{{ $i }}.cliente_id"
                                        class="w-full rounded border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($clientes as $c)
                                        <option value="{{ $c['id'] }}">{{ $c['label'] }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="py-2 pr-3">
                                <select wire:model="filasDeuda.{{ $i }}.mes"
                                        class="w-full rounded border-gray-300 text-sm">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}">{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                            </td>
                            <td class="py-2 pr-3">
                                <input wire:model="filasDeuda.{{ $i }}.anio" type="number" min="2000" max="2099"
                                       class="w-full rounded border-gray-300 text-sm" placeholder="2025">
                            </td>
                            <td class="py-2 pr-3">
                                <input wire:model="filasDeuda.{{ $i }}.monto" type="text"
                                       class="w-full rounded border-gray-300 text-sm" placeholder="25000">
                            </td>
                            <td class="py-2 pr-3">
                                <input wire:model="filasDeuda.{{ $i }}.fecha_vencimiento" type="text"
                                       class="w-full rounded border-gray-300 text-sm" placeholder="15/02/2025">
                            </td>
                            <td class="py-2 pr-3">
                                <input wire:model="filasDeuda.{{ $i }}.observaciones" type="text"
                                       class="w-full rounded border-gray-300 text-sm" placeholder="Deuda enero 2025">
                            </td>
                            <td class="py-2">
                                <button wire:click="eliminarFila({{ $i }})" class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                <p class="text-sm text-gray-500">{{ count($filasDeuda) }} fila(s) cargada(s)</p>
                <button wire:click="guardarDeudasManuales" wire:loading.attr="disabled"
                        style="background-color:#7c3aed;color:white;"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-lg font-medium text-sm hover:opacity-90 disabled:opacity-50">
                    <span wire:loading.remove wire:target="guardarDeudasManuales">
                        <i class="fas fa-save mr-1"></i>Guardar deudas
                    </span>
                    <span wire:loading wire:target="guardarDeudasManuales">
                        <i class="fas fa-spinner fa-spin mr-1"></i>Guardando...
                    </span>
                </button>
            </div>

            @if($resultadoManual !== null)
            <div class="mt-4 rounded-lg border p-4 {{ count($resultadoManual['errores']) > 0 ? 'bg-yellow-50 border-yellow-300' : 'bg-green-50 border-green-300' }}">
                <p class="font-semibold text-sm mb-1">Resultado:</p>
                <p class="text-sm text-green-700"><i class="fas fa-check-circle mr-1"></i>Deudas creadas: <strong>{{ $resultadoManual['creados'] }}</strong></p>
                @foreach($resultadoManual['errores'] as $error)
                    <p class="text-sm text-red-600 mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $error }}</p>
                @endforeach
            </div>
            @endif
        </div>

    </div>
    @endif
</div>
