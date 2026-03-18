<?php

use App\Livewire\Forms\LoginForm;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: RouteServiceProvider::HOME, navigate: true);
    }
}; ?>

<div>
    <!-- Título -->
    <div class="text-center mb-4">
        <h2 class="fw-semibold mb-2" style="font-size: 1.1rem; color: #374151;">¡Bienvenido!</h2>
        <p class="text-muted small mb-0">Ingresa para continuar</p>
    </div>


    <!-- Mensaje de estado de sesión -->
    <x-auth-session-status class="mb-3" :status="session('status')" />

    <!-- Mensaje de error de Livewire -->
    @if (session('error'))
        <div class="alert alert-danger small py-2 mb-3">
            <i class="bi bi-exclamation-triangle me-1"></i>
            {{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger small py-2 mb-3">
            <i class="bi bi-exclamation-triangle me-1"></i>
            {{ $errors->first() }}
        </div>
    @endif

    <form wire:submit="login" x-data="{ loading: false }" @submit="loading = true">
        <!-- Email -->
        <div class="mb-3">
            <label for="email" class="form-label small fw-medium text-muted">Email</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-envelope text-muted"></i>
                </span>
                <input wire:model.blur="form.email" 
                       id="email" 
                       type="email" 
                       name="email" 
                       required 
                       autofocus 
                       autocomplete="username"
                       class="form-control border-start-0 bg-light"
                       placeholder="correo@ejemplo.com"
                       style="font-size: 0.9rem;">
            </div>
            <x-input-error :messages="$errors->get('form.email')" class="text-danger small mt-1" />
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label small fw-medium text-muted">Contraseña</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-lock text-muted"></i>
                </span>
                <input wire:model.blur="form.password" 
                       id="password" 
                       type="password" 
                       name="password" 
                       required 
                       autocomplete="current-password"
                       class="form-control border-start-0 bg-light"
                       placeholder="••••••••"
                       style="font-size: 0.9rem;">
            </div>
            <x-input-error :messages="$errors->get('form.password')" class="text-danger small mt-1" />
        </div>

        <!-- Remember y Forgot -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
                <input wire:model="form.remember" 
                       class="form-check-input" 
                       type="checkbox" 
                       id="remember" 
                       name="remember">
                <label class="form-check-label small text-muted" for="remember">
                    Recordarme
                </label>
            </div>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" 
                   wire:navigate
                   class="text-decoration-none small"
                   style="color: #667eea;">
                    ¿Olvidaste tu contraseña?
                </a>
            @endif
        </div>

        <!-- Login Button con Spinner SOLO en el botón -->
        <div class="d-grid mb-3">
            <button type="submit"
                    class="btn btn-gradient text-white fw-medium py-2 position-relative"
                    style="font-size: 0.9rem;"
                    :disabled="loading">
                <template x-if="!loading">
                    <span>
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Iniciar Sesión
                    </span>
                </template>
                <template x-if="loading">
                    <span class="d-flex align-items-center justify-content-center">
                        <div class="spinner-border spinner-border-sm me-2" role="status" style="width: 1rem; height: 1rem;">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        Iniciando sesión...
                    </span>
                </template>
            </button>
        </div>
    </form>

    <!-- Footer seguro -->
    <div class="text-center pt-3 border-top">
        <small class="text-muted">
            <i class="bi bi-shield-check me-1"></i>
            Acceso seguro
        </small>
    </div>
</div>
