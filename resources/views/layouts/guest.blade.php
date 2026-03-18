<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Sistema de Gestión - Aguatería</title>

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Bootstrap Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Livewire Styles -->
        @livewireStyles

        <style>
            body {
                font-family: 'Inter', sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
            }
            .glass-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(15px);
                border: 1px solid rgba(255, 255, 255, 0.3);
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            }
            .logo-container {
                background: rgba(255, 255, 255, 0.2);
                border: 1px solid rgba(255, 255, 255, 0.3);
            }
            .btn-gradient {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                transition: all 0.3s ease;
            }
            .btn-gradient:hover {
                background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
                transform: translateY(-1px);
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            }
            [x-cloak] { display: none !important; }
            .spinner-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 0.375rem;
                z-index: 10;
            }
        </style>
    </head>
    <body>
        <div class="container-fluid vh-100 d-flex align-items-center justify-content-center p-3">
            <div class="row justify-content-center w-100">
                <div class="col-12 col-sm-8 col-md-6 col-lg-4 col-xl-3">
                    <!-- Logo y título -->
                    <div class="text-center mb-4">
                        <div class="logo-container d-inline-flex align-items-center justify-content-center rounded p-2 mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-droplet-fill text-white" style="font-size: 1.5rem;"></i>
                        </div>
                        <h1 class="text-white fw-semibold mb-1" style="font-size: 1.1rem;">Sistema Aguatería</h1>
                        <p class="text-white-50 small mb-0">Sistema de Gestión</p>
                    </div>

                    <!-- Tarjeta de login -->
                    <div class="glass-card rounded-3 p-4 position-relative">
                        {{ $slot }}
                    </div>

                    <!-- Footer -->
                    <div class="text-center mt-3">
                        <p class="text-white-50 small mb-0">© {{ date('Y') }} Aguatería</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Livewire Scripts -->
        @livewireScripts
    </body>
</html>
