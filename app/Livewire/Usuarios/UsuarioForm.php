<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UsuarioForm extends Component
{
    public ?int $usuarioId = null;
    public string $name        = '';
    public string $apellido    = '';
    public string $email       = '';
    public string $password    = '';
    public string $telefono    = '';
    public string $cedula      = '';
    public string $tipo_usuario = 'cajero';
    public string $estado      = 'activo';
    public array  $permisos    = [];

    public function mount(?int $usuarioId = null): void
    {
        $this->usuarioId = $usuarioId;

        if ($usuarioId) {
            $user = User::where('empresa_id', Auth::user()->empresa_id)->findOrFail($usuarioId);
            $this->name        = $user->name;
            $this->apellido    = $user->apellido ?? '';
            $this->email       = $user->email;
            $this->telefono    = $user->telefono ?? '';
            $this->cedula      = $user->cedula ?? '';
            $this->tipo_usuario = $user->tipo_usuario;
            $this->estado      = $user->estado;
            $this->permisos    = $user->permisos ?? [];
        }
    }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:100',
            'apellido'     => 'nullable|string|max:100',
            'email'        => ['required', 'email', Rule::unique('users', 'email')->ignore($this->usuarioId)],
            'password'     => $this->usuarioId ? 'nullable|min:8' : 'required|min:8',
            'telefono'     => 'nullable|string|max:20',
            'cedula'       => 'nullable|string|max:20',
            'tipo_usuario' => 'required|in:admin_empresa,supervisor,cajero,cobrador',
            'estado'       => 'required|in:activo,inactivo,suspendido',
            'permisos'     => 'nullable|array',
            'permisos.*'   => 'string|in:' . implode(',', array_keys(User::MODULOS)),
        ];
    }

    public function guardar(): void
    {
        $data = $this->validate();

        $empresaId = Auth::user()->empresa_id;

        $payload = [
            'empresa_id'   => $empresaId,
            'name'         => $data['name'],
            'apellido'     => $data['apellido'] ?? null,
            'email'        => $data['email'],
            'telefono'     => $data['telefono'] ?? null,
            'cedula'       => $data['cedula'] ?? null,
            'tipo_usuario' => $data['tipo_usuario'],
            'estado'       => $data['estado'],
            // Los admin_empresa tienen acceso total, no necesitan permisos guardados
            'permisos'     => $data['tipo_usuario'] === 'admin_empresa' ? null : ($data['permisos'] ?? []),
        ];

        if (!empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        if ($this->usuarioId) {
            User::where('empresa_id', $empresaId)->findOrFail($this->usuarioId)->update($payload);
            $msg = 'Usuario actualizado correctamente.';
        } else {
            User::create($payload);
            $msg = 'Usuario creado correctamente.';
        }

        $this->dispatch('toast', ['message' => $msg, 'type' => 'success']);
        $this->redirect(route('usuarios.index'));
    }

    public function render()
    {
        return view('livewire.usuarios.usuario-form', [
            'modulos'  => User::MODULOS,
            'esEditar' => (bool) $this->usuarioId,
        ])->layout('layouts.app', [
            'titulo'    => $this->usuarioId ? 'Editar Usuario' : 'Nuevo Usuario',
            'subtitulo' => 'Datos del usuario y permisos de acceso',
        ]);
    }
}
