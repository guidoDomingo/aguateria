<div class="max-w-3xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Mi Empresa</h1>
            <p class="text-sm text-gray-500 mt-0.5">Datos que aparecen en facturas, recibos y tickets de pago</p>
        </div>
        <button wire:click="guardar" type="button"
            class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700"
            wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="guardar"><i class="fas fa-save mr-1.5"></i> Guardar cambios</span>
            <span wire:loading wire:target="guardar"><i class="fas fa-spinner fa-spin mr-1.5"></i> Guardando...</span>
        </button>
    </div>

    {{-- Logo --}}
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
            <i class="fas fa-image text-blue-500"></i> Logo de la empresa
        </h2>
        <div class="flex items-center gap-6">
            <div class="w-24 h-24 border-2 border-dashed border-gray-300 rounded-xl flex items-center justify-center bg-gray-50 flex-shrink-0 overflow-hidden">
                @if($logo_actual)
                    <img src="{{ Storage::url($logo_actual) }}" alt="Logo" class="w-full h-full object-contain p-2">
                @else
                    <div class="text-center">
                        <i class="fas fa-building text-3xl text-gray-300"></i>
                        <div class="text-xs text-gray-400 mt-1">Sin logo</div>
                    </div>
                @endif
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Subir nuevo logo</label>
                <input wire:model="logo_nuevo" type="file" accept="image/*"
                    class="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-gray-400 mt-1.5">PNG, JPG o SVG. Máximo 2MB. Recomendado: fondo transparente.</p>
                @error('logo_nuevo')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                @if($logo_nuevo)
                <div class="mt-2 flex items-center gap-2 text-xs text-green-600">
                    <i class="fas fa-check-circle"></i>
                    <span>Imagen seleccionada — se guardará al hacer clic en "Guardar cambios"</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Datos generales --}}
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
            <i class="fas fa-building text-blue-500"></i> Datos generales
        </h2>
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre de la empresa <span class="text-red-500">*</span>
                </label>
                <input wire:model="nombre" type="text"
                    placeholder="Ej: Aguatería San Juan"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('nombre')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                <p class="text-xs text-gray-400 mt-1">Este nombre aparece en el encabezado de facturas y recibos.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Razón social</label>
                <input wire:model="razon_social" type="text"
                    placeholder="Ej: Aguatería San Juan S.A."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">RUC / NIT</label>
                <input wire:model="ruc" type="text"
                    placeholder="Ej: 80123456-7"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
    </div>

    {{-- Datos de contacto --}}
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
            <i class="fas fa-address-card text-blue-500"></i> Datos de contacto
            <span class="text-xs font-normal text-gray-400">— Aparecen en recibos según la config. de recibos</span>
        </h2>
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                <input wire:model="direccion" type="text"
                    placeholder="Ej: Av. Mariscal López 1234, Asunción"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <input wire:model="telefono" type="text"
                    placeholder="Ej: 021-123456"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input wire:model="email" type="email"
                    placeholder="Ej: info@miempresa.com.py"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                <input wire:model="ciudad" type="text"
                    placeholder="Ej: Asunción"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
                <select wire:model="departamento"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">— Seleccionar —</option>
                    @foreach(['Asunción','Central','Alto Paraná','Itapúa','Caaguazú','San Pedro','Cordillera','Guairá','Caazapá','Misiones','Paraguarí','Ñeembucú','Amambay','Canindeyú','Presidente Hayes','Boquerón','Alto Paraguay'] as $dep)
                    <option value="{{ $dep }}" @selected($departamento===$dep)>{{ $dep }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">País</label>
                <input wire:model="pais" type="text"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500" readonly>
            </div>
        </div>
    </div>

    {{-- Aviso --}}
    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex gap-3">
        <i class="fas fa-info-circle text-blue-500 mt-0.5 flex-shrink-0"></i>
        <p class="text-sm text-blue-700">
            Estos datos se usan en el encabezado de <strong>facturas</strong>, <strong>recibos de pago</strong> y <strong>tickets</strong>.
            Para controlar qué campos aparecen en cada documento, ajustá la
            <a href="{{ route('configuracion.recibos') }}" class="underline font-medium hover:text-blue-900">Configuración de Recibos</a>.
        </p>
    </div>

</div>
