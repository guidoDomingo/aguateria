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
    @livewireStyles
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

            <x-sidebar-nav />
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm border-b">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <!-- Page Title -->
                        <div>
                            @isset($titulo)
                                <h1 class="text-2xl font-semibold text-gray-900">{{ $titulo }}</h1>
                                @isset($subtitulo)
                                    <p class="text-sm text-gray-600 mt-1">{{ $subtitulo }}</p>
                                @endisset
                            @endisset
                        </div>

                        <!-- User Menu -->
                        <div class="flex items-center space-x-4">
                            <!-- Notifications -->
                            <div class="relative">
                                <button class="p-2 text-gray-600 hover:text-gray-900 relative">
                                    <i class="fas fa-bell"></i>
                                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                                        3
                                    </span>
                                </button>
                            </div>

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

                                <!-- Dropdown -->
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="p-1 text-gray-600 hover:text-gray-900">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </button>
                                    
                                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                                        <div class="py-1">
                                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <i class="fas fa-user-edit mr-2"></i>Mi Perfil
                                            </a>
                                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <i class="fas fa-key mr-2"></i>Cambiar Contraseña
                                            </a>
                                            <div class="border-t border-gray-100"></div>
                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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

    @livewireScripts
    
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
