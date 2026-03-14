<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $lastname = '';
    public string $email = '';
    public string $telefono = '';
    public string $cedula = '';
    public string $direccion = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->lastname = $user->apellido ?? '';
        $this->email = $user->email;
        $this->telefono = $user->telefono ?? '';
        $this->cedula = $user->cedula ?? '';
        $this->direccion = $user->direccion ?? '';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'telefono' => ['nullable', 'string', 'max:20'],
            'cedula' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:255']
        ]);

        $user->fill([
            'name' => $validated['name'],
            'apellido' => $validated['lastname'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'],
            'cedula' => $validated['cedula'],
            'direccion' => $validated['direccion']
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(): void
    {
        $this->validate([
            'password' => ['required', 'current_password'],
        ]);

        tap(Auth::user(), function ($user) {
            Auth::logout();
            $user->delete();
        });

        Session::invalidate();
        Session::regenerateToken();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section>
    <div class="max-w-xl">
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </div>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>
            
            <div>
                <x-input-label for="lastname" :value="__('Apellido')" />
                <x-text-input wire:model="lastname" id="lastname" name="lastname" type="text" class="mt-1 block w-full" autocomplete="family-name" />
                <x-input-error class="mt-2" :messages="$errors->get('lastname')" />
            </div>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>
        
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <x-input-label for="telefono" :value="__('Teléfono')" />
                <x-text-input wire:model="telefono" id="telefono" name="telefono" type="text" class="mt-1 block w-full" autocomplete="tel" />
                <x-input-error class="mt-2" :messages="$errors->get('telefono')" />
            </div>
            
            <div>
                <x-input-label for="cedula" :value="__('Cédula')" />
                <x-text-input wire:model="cedula" id="cedula" name="cedula" type="text" class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->get('cedula')" />
            </div>
        </div>
        
        <div>
            <x-input-label for="direccion" :value="__('Dirección')" />
            <x-text-input wire:model="direccion" id="direccion" name="direccion" type="text" class="mt-1 block w-full" autocomplete="address-line1" />
            <x-input-error class="mt-2" :messages="$errors->get('direccion')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>