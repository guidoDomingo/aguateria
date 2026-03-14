<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' - ' : '' }}Aguatería SaaS</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-sm border-r">
            <!-- Logo -->
            <div class="flex items-center justify-center h-16 px-4 bg-blue-600">
                <h1 class="text-xl font-bold text-white">
                    <i class="fas fa-tint mr-2"></i>
                    Aguatería SaaS
                </h1>
            </div>

            <!-- Navigation -->
            <nav class="mt-5 px-2">
                <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                    <i class="fas fa-chart-pie w-5 h-5 mr-3"></i>
                    Dashboard
                </x-nav-link>

                <x-nav-link href="{{ route('clientes.index') }}" :active="request()->routeIs('clientes.*')">
                    <i class="fas fa-users w-5 h-5 mr-3"></i>
                    Clientes
                </x-nav-link>

                <x-nav-link href="{{ route('facturas.index') }}" :active="request()->routeIs('facturas.*')">
                    <i class="fas fa-file-invoice w-5 h-5 mr-3"></i>
                    Facturación
                </x-nav-link>

                <x-nav-link href="{{ route('pagos.index') }}" :active="request()->routeIs('pagos.*')">
                    <i class="fas fa-credit-card w-5 h-5 mr-3"></i>
                    Pagos
                </x-nav-link>

                <x-nav-link href="{{ route('cobradores.index') }}" :active="request()->routeIs('cobradores.*')">
                    <i class="fas fa-user-tie w-5 h-5 mr-3"></i>
                    Cobradores
                </x-nav-link>

                <x-nav-link href="{{ route('barrios.index') }}" :active="request()->routeIs('barrios.*')">
                    <i class="fas fa-map-marker-alt w-5 h-5 mr-3"></i>
                    Barrios
                </x-nav-link>

                <x-nav-link href="{{ route('zonas.index') }}" :active="request()->routeIs('zonas.*')">
                    <i class="fas fa-map-marked-alt w-5 h-5 mr-3"></i>
                    Zonas
                </x-nav-link>

                <x-nav-link href="{{ route('tarifas.index') }}" :active="request()->routeIs('tarifas.*')">
                    <i class="fas fa-dollar-sign w-5 h-5 mr-3"></i>
                    Tarifas
                </x-nav-link>

                <x-nav-link href="#" :active="request()->routeIs('cortes.*')">
                    <i class="fas fa-power-off w-5 h-5 mr-3"></i>
                    Cortes y Reconexiones
                </x-nav-link>

                <x-nav-link href="#" :active="request()->routeIs('reportes.*')">
                    <i class="fas fa-chart-bar w-5 h-5 mr-3"></i>
                    Reportes
                </x-nav-link>

                @auth
                @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                <div class="mt-4 px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    Administración
                </div>

                <x-nav-link href="#" :active="request()->routeIs('configuracion.*')">
                    <i class="fas fa-cog w-5 h-5 mr-3"></i>
                    Configuración
                </x-nav-link>

                <x-nav-link href="#" :active="request()->routeIs('usuarios.*')">
                    <i class="fas fa-user-cog w-5 h-5 mr-3"></i>
                    Usuarios
                </x-nav-link>
                @endif
                @endauth
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm border-b">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <!-- Page Title -->
                        <div>
                            @if(isset($title))
                                <h1 class="text-2xl font-semibold text-gray-900">{{ $title }}</h1>
                            @endif
                        </div>

                        <!-- User Menu -->
                        @auth
                        <div class="flex items-center space-x-4">
                            <!-- User Info -->
                            <div class="flex items-center space-x-3">
                                <div class="text-sm text-right">
                                    <div class="font-medium text-gray-900">{{ auth()->user()->name }}</div>
                                    <div class="text-gray-500">{{ auth()->user()->empresa->nombre ?? 'Sistema' }}</div>
                                </div>
                                
                                <!-- User Avatar -->
                                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-gray-600 text-sm"></i>
                                </div>

                                <!-- Logout -->
                                <form method="POST" action="{{ route('logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="p-2 text-gray-600 hover:text-gray-900" title="Cerrar Sesión">
                                        <i class="fas fa-sign-out-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endauth
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div id="toast-container" class="fixed top-4 right-4 z-50"></div>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // Toast notification function
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            const bgColor = type === 'success' ? 'bg-green-500' : 
                           type === 'error' ? 'bg-red-500' : 
                           type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';
            
            toast.className = `${bgColor} text-white px-4 py-3 rounded-lg shadow-lg mb-2 transform transition-all duration-300 translate-x-full`;
            toast.innerHTML = `
                <div class="flex items-center">
                    <span class="flex-1">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            container.appendChild(toast);
            
            // Animate in
            setTimeout(() => toast.classList.remove('translate-x-full'), 100);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

        // Listen for Livewire events
        document.addEventListener('livewire:initialized', function () {
            Livewire.on('toast', ({ message, type }) => {
                showToast(message, type);
            });
        });
    </script>

    @stack('scripts')
</body>
</html>