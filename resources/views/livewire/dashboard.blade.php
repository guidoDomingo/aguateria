<div>
    <!-- Header con botón de actualizar -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-600">Panel de control y estadísticas generales</p>
        </div>
        <button 
            wire:click="refrescarEstadisticas" 
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center"
            {{ $cargando ? 'disabled' : '' }}
        >
            <i class="fas fa-sync-alt mr-2 {{ $cargando ? 'animate-spin' : '' }}"></i>
            {{ $cargando ? 'Actualizando...' : 'Actualizar' }}
        </button>
    </div>

    @if($cargando)
        <!-- Loading State -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            @for($i = 0; $i < 4; $i++)
                <div class="bg-white p-6 rounded-lg shadow-sm border animate-pulse">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-gray-200 w-12 h-12"></div>
                        <div class="ml-4 flex-1">
                            <div class="h-4 bg-gray-200 rounded w-20 mb-2"></div>
                            <div class="h-6 bg-gray-200 rounded w-16"></div>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    @else
        <!-- Tarjetas de Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Clientes -->
            <div class="bg-white p-6 rounded-lg shadow-sm border hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Clientes</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ number_format($estadisticas['clientes']['total']) }}
                        </p>
                    </div>
                </div>
                <div class="mt-4 text-sm text-gray-500">
                    <span class="text-green-600">{{ $estadisticas['clientes']['activos'] }} activos</span>
                    @if($estadisticas['clientes']['suspendidos'] > 0)
                        • <span class="text-red-600">{{ $estadisticas['clientes']['suspendidos'] }} suspendidos</span>
                    @endif
                </div>
            </div>

            <!-- Facturación del Mes -->
            <div class="bg-white p-6 rounded-lg shadow-sm border hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-file-invoice-dollar text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Facturado Mes</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ number_format($estadisticas['facturacion_mes']['monto_facturado'], 0, ',', '.') }} Gs.
                        </p>
                    </div>
                </div>
                <div class="mt-4 text-sm text-gray-500">
                    {{ $estadisticas['facturacion_mes']['cantidad_facturas'] }} facturas generadas
                </div>
            </div>

            <!-- Cobranza del Mes -->
            <div class="bg-white p-6 rounded-lg shadow-sm border hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-emerald-100 text-emerald-600">
                        <i class="fas fa-coins text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Cobrado Mes</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ number_format($estadisticas['facturacion_mes']['monto_cobrado'], 0, ',', '.') }} Gs.
                        </p>
                    </div>
                </div>
                <div class="mt-4 text-sm text-gray-500">
                    <span class="text-green-600">
                        {{ number_format($estadisticas['facturacion_mes']['porcentaje_cobranza'], 1) }}% eficiencia
                    </span>
                </div>
            </div>

            <!-- Morosidad -->
            <div class="bg-white p-6 rounded-lg shadow-sm border hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-exclamation-triangle text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Morosidad</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ number_format($estadisticas['morosidad']['monto_vencido'], 0, ',', '.') }} Gs.
                        </p>
                    </div>
                </div>
                <div class="mt-4 text-sm text-gray-500">
                    {{ $estadisticas['morosidad']['clientes_morosos'] }} clientes morosos
                </div>
            </div>
        </div>

        <!-- Section de KPIs -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- KPI Cards -->
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Eficiencia de Cobranza</p>
                        <p class="text-3xl font-bold text-blue-600">
                            {{ number_format($estadisticas['kpis']['eficiencia_cobranza'], 1) }}%
                        </p>
                    </div>
                    <div class="p-3 rounded-full bg-blue-100">
                        <i class="fas fa-percentage text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center text-sm">
                        @if($estadisticas['kpis']['eficiencia_cobranza'] >= 80)
                            <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                            <span class="text-green-600">Excelente</span>
                        @elseif($estadisticas['kpis']['eficiencia_cobranza'] >= 60)
                            <i class="fas fa-arrow-right text-yellow-500 mr-1"></i>
                            <span class="text-yellow-600">Bueno</span>
                        @else
                            <i class="fas fa-arrow-down text-red-500 mr-1"></i>
                            <span class="text-red-600">Necesita mejorar</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Índice de Morosidad</p>
                        <p class="text-3xl font-bold text-red-600">
                            {{ number_format($estadisticas['kpis']['indice_morosidad'], 1) }}%
                        </p>
                    </div>
                    <div class="p-3 rounded-full bg-red-100">
                        <i class="fas fa-chart-line text-red-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center text-sm">
                        @if($estadisticas['kpis']['indice_morosidad'] <= 10)
                            <i class="fas fa-check-circle text-green-500 mr-1"></i>
                            <span class="text-green-600">Bajo</span>
                        @elseif($estadisticas['kpis']['indice_morosidad'] <= 20)
                            <i class="fas fa-exclamation-circle text-yellow-500 mr-1"></i>
                            <span class="text-yellow-600">Moderado</span>
                        @else
                            <i class="fas fa-times-circle text-red-500 mr-1"></i>
                            <span class="text-red-600">Alto</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Crecimiento Clientes</p>
                        <p class="text-3xl font-bold text-green-600">
                            +{{ number_format($estadisticas['kpis']['crecimiento_clientes'], 0) }}
                        </p>
                    </div>
                    <div class="p-3 rounded-full bg-green-100">
                        <i class="fas fa-user-plus text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center text-sm">
                        <i class="fas fa-calendar text-gray-400 mr-1"></i>
                        <span class="text-gray-600">Este mes</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de Acciones Rápidas -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones Rápidas</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="#" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <i class="fas fa-user-plus text-blue-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium text-gray-900">Nuevo Cliente</p>
                        <p class="text-sm text-gray-500">Registrar cliente</p>
                    </div>
                </a>

                <a href="#" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <i class="fas fa-credit-card text-green-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium text-gray-900">Registrar Pago</p>
                        <p class="text-sm text-gray-500">Cobrar factura</p>
                    </div>
                </a>

                <a href="#" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <i class="fas fa-file-invoice text-yellow-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium text-gray-900">Ver Facturas</p>
                        <p class="text-sm text-gray-500">Gestionar facturas</p>
                    </div>
                </a>

                <a href="#" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <i class="fas fa-chart-bar text-purple-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium text-gray-900">Ver Reportes</p>
                        <p class="text-sm text-gray-500">Análisis completo</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Alertas y Notificaciones -->
        @if($estadisticas['morosidad']['facturas_vencidas'] > 0)
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        Atención requerida
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>
                            Tienes {{ $estadisticas['morosidad']['facturas_vencidas'] }} facturas vencidas 
                            por un monto total de {{ number_format($estadisticas['morosidad']['monto_vencido'], 0, ',', '.') }} Gs.
                        </p>
                    </div>
                    <div class="mt-4">
                        <a href="#" class="text-yellow-800 font-medium hover:text-yellow-900">
                            Ver gestión de cobranza →
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif
</div>
