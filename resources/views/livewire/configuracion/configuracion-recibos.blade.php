<div class="flex gap-6" style="min-height: calc(100vh - 140px);">

    {{-- ============================================================ --}}
    {{-- PANEL IZQUIERDO: CONFIGURACIÓN --}}
    {{-- ============================================================ --}}
    <div class="w-96 flex-shrink-0 flex flex-col gap-4">

        {{-- Header --}}
        <div class="bg-white rounded-xl shadow-sm border p-4 flex items-center justify-between">
            <div>
                <h1 class="text-base font-bold text-gray-900">Configuración de Recibos</h1>
                <p class="text-xs text-gray-500 mt-0.5">Preview actualizado en tiempo real</p>
            </div>
            <div class="flex gap-2">
                <button wire:click="restaurarDefecto" type="button"
                    class="px-3 py-1.5 text-xs border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">
                    <i class="fas fa-undo mr-1"></i> Restaurar
                </button>
                <button wire:click="guardarConfiguracion" type="button"
                    class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="guardarConfiguracion"><i class="fas fa-save mr-1"></i> Guardar</span>
                    <span wire:loading wire:target="guardarConfiguracion"><i class="fas fa-spinner fa-spin mr-1"></i> Guardando...</span>
                </button>
            </div>
        </div>

        {{-- Pestañas --}}
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden flex-1">
            <div class="flex border-b text-xs">
                @foreach(['formato'=>'Formato','diseño'=>'Diseño','contenido'=>'Contenido','copias'=>'Copias'] as $tab=>$label)
                <button wire:click="$set('pestañaActiva','{{ $tab }}')" type="button"
                    class="flex-1 py-2.5 font-medium transition-colors
                    {{ $pestañaActiva===$tab ? 'border-b-2 border-blue-600 text-blue-600 bg-blue-50' : 'text-gray-500 hover:bg-gray-50' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>

            <div class="p-4 space-y-4 overflow-y-auto" style="max-height: calc(100vh - 300px);">

                {{-- ===== FORMATO ===== --}}
                @if($pestañaActiva==='formato')

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Tamaño de papel</label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach($dimensionesPapel as $key=>$dim)
                        <button wire:click="$set('tamaño_papel','{{ $key }}')" type="button"
                            class="flex flex-col items-center p-2.5 border-2 rounded-lg transition-all text-xs
                            {{ $tamaño_papel===$key ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 hover:border-gray-300 text-gray-600' }}">
                            @if($dim['tipo']==='ticket')
                                <div class="border-2 border-current rounded mb-1" style="width:10px;height:18px;"></div>
                            @else
                                <div class="border-2 border-current rounded mb-1" style="width:14px;height:18px;"></div>
                            @endif
                            <span class="font-medium">{{ $dim['nombre'] }}</span>
                            @if($dim['w']>0)<span class="text-gray-400 text-[9px]">{{ $dim['w'] }}×{{ $dim['h'] }}mm</span>@endif
                        </button>
                        @endforeach
                    </div>
                </div>

                @if($tamaño_papel==='personalizado')
                <div class="grid grid-cols-2 gap-3 p-3 bg-gray-50 rounded-lg">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Ancho (mm)</label>
                        <input wire:model.live="ancho_personalizado" type="number" min="50" max="500"
                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Alto (mm)</label>
                        <input wire:model.live="alto_personalizado" type="number" min="100" max="1000"
                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                    </div>
                </div>
                @endif

                @if(!in_array($tamaño_papel,['80mm','58mm']))
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Orientación</label>
                    <div class="flex gap-4">
                        @foreach(['portrait'=>'Vertical','landscape'=>'Horizontal'] as $val=>$lbl)
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input wire:model.live="orientacion" type="radio" value="{{ $val }}" class="text-blue-600 w-3.5 h-3.5">
                            <span class="text-sm text-gray-700">{{ $lbl }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Márgenes (mm)</label>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                        @foreach(['superior'=>'Superior','inferior'=>'Inferior','izquierdo'=>'Izquierdo','derecho'=>'Derecho'] as $lado=>$lbl)
                        <div>
                            <div class="flex justify-between text-xs text-gray-500 mb-0.5">
                                <span>{{ $lbl }}</span>
                                <span class="font-medium text-gray-700">{{ ${'margenes_'.$lado} }}mm</span>
                            </div>
                            <input wire:model.live="margenes_{{ $lado }}" type="range" min="0" max="30" class="w-full h-1.5 accent-blue-600">
                        </div>
                        @endforeach
                    </div>
                </div>

                @endif

                {{-- ===== DISEÑO ===== --}}
                @if($pestañaActiva==='diseño')

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Plantilla</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['standard'=>'Estándar','modern'=>'Moderno','classic'=>'Clásico','minimal'=>'Minimalista','recibo_dinero'=>'Recibo de Dinero (Paraguayo)'] as $val=>$lbl)
                        <button wire:click="$set('plantilla','{{ $val }}')" type="button"
                            class="py-2 border-2 rounded-lg text-xs font-medium transition-all
                            {{ $plantilla===$val ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 hover:border-gray-300 text-gray-600' }}">
                            {{ $lbl }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Colores</label>
                    <div class="space-y-2">
                        @foreach(['color_header'=>'Encabezado','color_text'=>'Texto','color_background'=>'Fondo'] as $prop=>$lbl)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">{{ $lbl }}</span>
                            <div class="flex items-center gap-2">
                                <input wire:model.live="{{ $prop }}" type="color" class="w-8 h-7 border border-gray-300 rounded cursor-pointer p-0.5">
                                <span class="text-xs text-gray-400 font-mono">{{ $$prop }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Tipografía</label>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Fuente</label>
                            <select wire:model.live="fuente" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                                <option value="Arial">Arial</option>
                                <option value="Times">Times New Roman</option>
                                <option value="Courier">Courier</option>
                            </select>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span>Tamaño</span><span class="font-medium text-gray-700">{{ $tamaño_fuente }}pt</span>
                            </div>
                            <input wire:model.live="tamaño_fuente" type="range" min="8" max="16" class="w-full mt-1 h-1.5 accent-blue-600">
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-semibold text-gray-700">Logo de empresa</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input wire:model.live="mostrar_logo" type="checkbox" class="sr-only peer">
                            <div class="w-8 h-4 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all"></div>
                        </label>
                    </div>
                    @if($mostrar_logo)
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Posición</label>
                            <select wire:model.live="posicion_logo" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                                <option value="left">Izquierda</option>
                                <option value="center">Centro</option>
                                <option value="right">Derecha</option>
                            </select>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span>Tamaño</span><span class="font-medium">{{ $tamaño_logo }}px</span>
                            </div>
                            <input wire:model.live="tamaño_logo" type="range" min="40" max="150" class="w-full mt-1 h-1.5 accent-blue-600">
                        </div>
                    </div>
                    @endif
                </div>

                @endif

                {{-- ===== CONTENIDO ===== --}}
                @if($pestañaActiva==='contenido')

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Campos a mostrar</label>
                    <div class="space-y-1">
                        @foreach([
                            'mostrar_fecha'=>'Fecha',
                            'mostrar_hora'=>'Hora',
                            'mostrar_direccion_empresa'=>'Dirección de empresa',
                            'mostrar_telefono_empresa'=>'Teléfono de empresa',
                            'mostrar_email_empresa'=>'Email de empresa',
                            'mostrar_descripcion_detallada'=>'Descripción detallada',
                            'mostrar_codigo_qr'=>'Código QR',
                        ] as $prop=>$lbl)
                        <label class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <span class="text-sm text-gray-700">{{ $lbl }}</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input wire:model.live="{{ $prop }}" type="checkbox" class="sr-only peer">
                                <div class="w-8 h-4 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all"></div>
                            </label>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="block text-xs font-semibold text-gray-700">Mensajes</label>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Mensaje superior</label>
                        <input wire:model.live="mensaje_superior" type="text" maxlength="255"
                            placeholder="Ej: Empresa certificada..."
                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Mensaje inferior</label>
                        <input wire:model.live="mensaje_inferior" type="text" maxlength="255"
                            placeholder="Ej: Gracias por su preferencia"
                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Términos y condiciones</label>
                        <textarea wire:model.live="terminos_condiciones" rows="3" maxlength="500"
                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-1 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                </div>

                @endif

                {{-- ===== COPIAS ===== --}}
                @if($pestañaActiva==='copias')

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Cantidad de copias</label>
                    <p class="text-xs text-gray-400 mb-3">Hojas que se imprimirán o generarán en el PDF.</p>
                    <div class="grid grid-cols-4 gap-2 mb-4">
                        @foreach([1,2,3,4] as $n)
                        <button wire:click="$set('copias',{{ $n }})" type="button"
                            class="py-3 border-2 rounded-xl transition-all text-center
                            {{ $copias==$n ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 hover:border-gray-300 text-gray-500' }}">
                            <div class="text-xl font-bold">{{ $n }}</div>
                            <div class="text-[10px]">{{ $n===1?'copia':'copias' }}</div>
                        </button>
                        @endforeach
                    </div>

                    <label class="block text-xs font-semibold text-gray-700 mb-2">Destino de copias</label>
                    <div class="space-y-2">
                        <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input wire:model.live="copia_cliente" type="checkbox" class="mt-0.5 w-4 h-4 rounded text-blue-600">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-700">Para el cliente</div>
                                <div class="text-xs text-gray-400">Se entrega al cliente</div>
                                @if($copia_cliente)
                                <input wire:model.live="etiqueta_copia_cliente" type="text" maxlength="30"
                                    placeholder="Etiqueta en el recibo"
                                    class="mt-1.5 w-full border border-gray-300 rounded px-2 py-1 text-xs uppercase font-medium">
                                @endif
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input wire:model.live="copia_empresa" type="checkbox" class="mt-0.5 w-4 h-4 rounded text-blue-600">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-700">Para la empresa</div>
                                <div class="text-xs text-gray-400">Se archiva internamente</div>
                                @if($copia_empresa)
                                <input wire:model.live="etiqueta_copia_empresa" type="text" maxlength="30"
                                    placeholder="Etiqueta en el recibo"
                                    class="mt-1.5 w-full border border-gray-300 rounded px-2 py-1 text-xs uppercase font-medium">
                                @endif
                            </div>
                        </label>
                    </div>

                    {{-- Resumen visual --}}
                    <div class="mt-4 p-3 bg-blue-50 border border-blue-100 rounded-lg">
                        <p class="text-xs font-semibold text-blue-700 mb-2">Al imprimir se generarán:</p>
                        <div class="space-y-1.5">
                            @for($i=1;$i<=$copias;$i++)
                            <div class="flex items-center gap-2">
                                <i class="fas fa-file text-blue-400 text-xs w-3"></i>
                                <span class="text-xs text-blue-700 font-medium">Hoja {{ $i }}:</span>
                                <span class="text-xs text-gray-600">
                                    @if($i===1 && $copia_cliente) {{ $etiqueta_copia_cliente ?: 'ORIGINAL - CLIENTE' }}
                                    @elseif($i===2 && $copia_empresa) {{ $etiqueta_copia_empresa ?: 'COPIA - EMPRESA' }}
                                    @else Copia {{ $i }} @endif
                                </span>
                            </div>
                            @endfor
                        </div>
                    </div>
                </div>

                @endif

            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- PANEL DERECHO: PREVIEW EN VIVO --}}
    {{-- ============================================================ --}}
    <div class="flex-1 min-w-0">
        <div class="bg-white rounded-xl shadow-sm border p-3 mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-eye text-blue-500 text-sm"></i>
                <span class="text-sm font-semibold text-gray-700">Preview en vivo</span>
                <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">
                    {{ $dimensionesPapel[$tamaño_papel]['nombre'] }}
                    @if($dimensionesPapel[$tamaño_papel]['w']>0)
                    — {{ $dimensionesPapel[$tamaño_papel]['w'] }}×{{ $dimensionesPapel[$tamaño_papel]['h'] }}mm
                    @endif
                </span>
            </div>
            @if($copias>1)
            <span class="text-xs text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full font-medium">
                <i class="fas fa-copy mr-1"></i>{{ $copias }} copias
            </span>
            @endif
        </div>

        @php
            $isTicket = in_array($tamaño_papel,['80mm','58mm']);
            $previewWidth = match($tamaño_papel) {
                '58mm'  => '190px',
                '80mm'  => '260px',
                'A4'    => '380px',
                'carta' => '360px',
                'oficio'=> '360px',
                default => '340px',
            };
        @endphp

        <div class="overflow-auto pb-6" style="max-height: calc(100vh - 220px);">
            <div class="{{ $copias>1 ? 'flex gap-6 flex-wrap' : 'flex justify-center' }}">

                @for($copia=1;$copia<=$copias;$copia++)
                <div class="flex-shrink-0">
                    {{-- Etiqueta de copia --}}
                    @if($copias>1)
                    <div class="text-center mb-2">
                        <span class="text-xs font-bold uppercase tracking-widest px-2 py-0.5 rounded-full
                            {{ $copia===1 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                            @if($copia===1 && $copia_cliente) {{ $etiqueta_copia_cliente ?: 'ORIGINAL' }}
                            @elseif($copia===2 && $copia_empresa) {{ $etiqueta_copia_empresa ?: 'EMPRESA' }}
                            @else Copia {{ $copia }} @endif
                        </span>
                    </div>
                    @if($copia>1)
                    <div class="flex items-center gap-1 mb-3 opacity-40">
                        <div class="flex-1 border-t-2 border-dashed border-gray-400"></div>
                        <i class="fas fa-scissors text-gray-400 text-xs"></i>
                        <div class="flex-1 border-t-2 border-dashed border-gray-400"></div>
                    </div>
                    @endif
                    @endif

                    {{-- Recibo --}}
                    <div style="width:{{ $previewWidth }};
                                background:{{ $color_background }};
                                color:{{ $color_text }};
                                font-family:'{{ $fuente }}',sans-serif;
                                font-size:{{ $tamaño_fuente }}px;
                                padding:{{ $margenes_superior }}px {{ $margenes_derecho }}px {{ $margenes_inferior }}px {{ $margenes_izquierdo }}px;
                                border:1px solid #e5e7eb;
                                border-radius:{{ $isTicket?'4px':'8px' }};
                                box-shadow:0 4px 16px rgba(0,0,0,0.10);">

                        @if($mensaje_superior)
                        <div style="text-align:center;font-size:{{ max(7,$tamaño_fuente-3) }}px;color:#9ca3af;padding-bottom:6px;border-bottom:1px dashed #e5e7eb;margin-bottom:8px;">
                            {{ $mensaje_superior }}
                        </div>
                        @endif

                        {{-- Header --}}
                        <div style="background:{{ $color_header }};color:#fff;text-align:{{ $posicion_logo }};padding:{{ $isTicket?'8px':'12px' }};border-radius:4px;margin-bottom:10px;">
                            @if($mostrar_logo)
                            <div style="width:{{ max(20,$tamaño_logo/4) }}px;height:{{ max(20,$tamaño_logo/4) }}px;background:rgba(255,255,255,0.2);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:3px;font-size:{{ max(10,$tamaño_logo/5) }}px;">💧</div><br>
                            @endif
                            <strong style="font-size:{{ max(9,$tamaño_fuente+1) }}px;">{{ $empresa->nombre ?? 'Mi Empresa' }}</strong>
                            @if($mostrar_direccion_empresa && ($empresa->direccion??null))
                            <div style="font-size:{{ max(7,$tamaño_fuente-3) }}px;opacity:0.85;margin-top:1px;">{{ $empresa->direccion }}</div>
                            @endif
                            @if($mostrar_telefono_empresa && ($empresa->telefono??null))
                            <div style="font-size:{{ max(7,$tamaño_fuente-3) }}px;opacity:0.85;">Tel: {{ $empresa->telefono }}</div>
                            @endif
                            @if($mostrar_email_empresa && ($empresa->email??null))
                            <div style="font-size:{{ max(7,$tamaño_fuente-3) }}px;opacity:0.85;">{{ $empresa->email }}</div>
                            @endif
                        </div>

                        <div style="text-align:center;margin-bottom:8px;">
                            <div style="font-weight:bold;font-size:{{ $tamaño_fuente }}px;letter-spacing:1px;">RECIBO DE PAGO</div>
                            <div style="font-size:{{ max(7,$tamaño_fuente-2) }}px;color:#9ca3af;">N° REC00000001</div>
                        </div>

                        <div style="border-top:1px dashed #d1d5db;margin:6px 0;"></div>

                        <table style="width:100%;font-size:{{ max(7,$tamaño_fuente-2) }}px;">
                            @if($mostrar_fecha)
                            <tr><td style="color:#9ca3af;padding:1px 0;">Fecha:</td><td style="text-align:right;font-weight:500;">{{ now()->format('d/m/Y') }}</td></tr>
                            @endif
                            @if($mostrar_hora)
                            <tr><td style="color:#9ca3af;padding:1px 0;">Hora:</td><td style="text-align:right;font-weight:500;">{{ now()->format('H:i') }}</td></tr>
                            @endif
                            <tr><td style="color:#9ca3af;padding:1px 0;">Método:</td><td style="text-align:right;font-weight:500;">Efectivo</td></tr>
                        </table>

                        <div style="border-top:1px dashed #d1d5db;margin:6px 0;"></div>

                        <div style="font-size:{{ max(7,$tamaño_fuente-2) }}px;margin-bottom:6px;">
                            <div><span style="color:#9ca3af;">Cliente:</span> <strong>Juan Pérez</strong></div>
                            <div><span style="color:#9ca3af;">Cédula:</span> 1.234.567</div>
                            <div><span style="color:#9ca3af;">Dirección:</span> Av. Principal 123</div>
                        </div>

                        @if($mostrar_descripcion_detallada)
                        <div style="border-top:1px dashed #d1d5db;margin:6px 0;"></div>
                        <table style="width:100%;font-size:{{ max(7,$tamaño_fuente-2) }}px;">
                            <tr style="color:#9ca3af;font-size:{{ max(6,$tamaño_fuente-3) }}px;">
                                <td>CONCEPTO</td><td style="text-align:right;">MONTO</td>
                            </tr>
                            <tr><td>Servicio mensual Mar. 2026</td><td style="text-align:right;">Gs. 150.000</td></tr>
                        </table>
                        @endif

                        <div style="border-top:1px dashed #d1d5db;margin:6px 0;"></div>

                        <div style="display:flex;justify-content:space-between;align-items:center;padding:4px 0;">
                            <strong style="font-size:{{ $tamaño_fuente }}px;">TOTAL PAGADO:</strong>
                            <strong style="font-size:{{ $tamaño_fuente+3 }}px;color:{{ $color_header }};">Gs. 150.000</strong>
                        </div>

                        @if($mensaje_inferior || $terminos_condiciones)
                        <div style="border-top:1px dashed #d1d5db;margin:6px 0;"></div>
                        @if($mensaje_inferior)
                        <div style="text-align:center;font-size:{{ max(7,$tamaño_fuente-2) }}px;color:#6b7280;margin-bottom:3px;">{{ $mensaje_inferior }}</div>
                        @endif
                        @if($terminos_condiciones)
                        <div style="text-align:center;font-size:{{ max(6,$tamaño_fuente-3) }}px;color:#d1d5db;line-height:1.3;">{{ $terminos_condiciones }}</div>
                        @endif
                        @endif

                        @if($mostrar_codigo_qr)
                        <div style="text-align:center;margin-top:8px;">
                            <div style="display:inline-block;width:44px;height:44px;background:#f3f4f6;border:1px solid #e5e7eb;line-height:44px;font-size:24px;">⬛</div>
                            <div style="font-size:{{ max(6,$tamaño_fuente-4) }}px;color:#d1d5db;margin-top:2px;">QR verificación</div>
                        </div>
                        @endif

                        <div style="text-align:center;font-size:{{ max(6,$tamaño_fuente-4) }}px;color:#e5e7eb;margin-top:10px;">
                            Documento generado electrónicamente
                        </div>
                    </div>
                </div>
                @endfor

            </div>
        </div>
    </div>

</div>
