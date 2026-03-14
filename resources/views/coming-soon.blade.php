<x-app-layout>
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border p-8 text-center">
            <div class="flex justify-center mb-6">
                <div class="p-6 bg-blue-100 rounded-full">
                    <i class="fas fa-tools text-blue-600 text-6xl"></i>
                </div>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $module ?? 'Módulo' }}</h1>
            <p class="text-xl text-gray-600 mb-6">Próximamente disponible</p>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-yellow-400 text-xl"></i>
                    </div>
                    <div class="ml-3 text-left">
                        <p class="text-sm text-yellow-800">
                            <strong>En desarrollo:</strong> Este módulo está siendo desarrollado y estará disponible pronto. 
                            Mientras tanto, puedes explorar las otras funcionalidades del sistema.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Funcionalidades planificadas -->
            <div class="text-left bg-gray-50 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Funcionalidades planificadas:</h3>
                
                @switch($module)
                    @case('Clientes')
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Registro y gestión completa de clientes
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Historial de servicios y pagos
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Búsqueda avanzada y filtros
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Importación masiva de datos
                            </li>
                        </ul>
                        @break
                        
                    @case('Facturas')
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Generación automática de facturas
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Personalización de formatos
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Control de vencimientos
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Envío automático por email
                            </li>
                        </ul>
                        @break
                        
                    @case('Pagos')
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Registro de pagos múltiples métodos
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Generación automática de recibos
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Control de caja diaria
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Conciliación bancaria
                            </li>
                        </ul>
                        @break
                        
                    @case('Reportes')
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Reportes de ingresos y egresos
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Análisis de morosidad
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Estadísticas de clientes
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Exportación a Excel/PDF
                            </li>
                        </ul>
                        @break
                        
                    @default
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Interfaz intuitiva y fácil de usar
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Integración completa con el sistema
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Funcionalidades avanzadas
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Compatibilidad móvil
                            </li>
                        </ul>
                @endswitch
            </div>
            
            <!-- Botones de acción -->
            <div class="space-x-4">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    <i class="fas fa-home mr-2"></i>
                    Volver al Dashboard
                </a>
                
                <button onclick="reportarBug()" class="inline-flex items-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                    <i class="fas fa-envelope mr-2"></i>
                    Contactar Soporte
                </button>
            </div>
            
            <!-- Información adicional -->
            <div class="mt-8 text-sm text-gray-500">
                <p>¿Necesitas ayuda? Contáctanos en <a href="mailto:soporte@aguateria-saas.com" class="text-blue-600 hover:text-blue-800">soporte@aguateria-saas.com</a></p>
                <p class="mt-1">Sistema Aguatería SaaS v1.0 - Desarrollado para empresas de agua en Paraguay</p>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        function reportarBug() {
            // Crear modal o enviar email para reporte de bugs
            const email = 'soporte@aguateria-saas.com';
            const subject = 'Solicitud de información - {{ $module ?? "Sistema" }}';
            const body = `Hola,\n\nMe gustaría recibir más información sobre el módulo "{{ $module ?? "Sistema" }}" y su fecha estimada de lanzamiento.\n\nGracias.`;
            
            window.location.href = `mailto:${email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
        }
    </script>
    @endpush
</x-app-layout>