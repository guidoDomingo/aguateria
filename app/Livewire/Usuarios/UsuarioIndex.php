<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UsuarioIndex extends Component
{
    use WithPagination;

    public string $buscar = '';
    public string $filtroTipo = '';
    public string $filtroEstado = '';

    public function updatingBuscar(): void { $this->resetPage(); }
    public function updatingFiltroTipo(): void { $this->resetPage(); }
    public function updatingFiltroEstado(): void { $this->resetPage(); }

    public function toggleEstado(int $userId): void
    {
        $user = User::where('empresa_id', Auth::user()->empresa_id)->findOrFail($userId);

        if ($user->id === Auth::id()) {
            $this->dispatch('toast', ['message' => 'No puedes desactivar tu propio usuario.', 'type' => 'error']);
            return;
        }

        $user->update(['estado' => $user->estado === 'activo' ? 'inactivo' : 'activo']);

        $this->dispatch('toast', [
            'message' => "Usuario {$user->estado}.",
            'type'    => 'success',
        ]);
    }

    public function eliminar(int $userId): void
    {
        $user = User::where('empresa_id', Auth::user()->empresa_id)->findOrFail($userId);

        if ($user->id === Auth::id()) {
            $this->dispatch('toast', ['message' => 'No puedes eliminar tu propio usuario.', 'type' => 'error']);
            return;
        }

        $user->delete();
        $this->dispatch('toast', ['message' => 'Usuario eliminado.', 'type' => 'success']);
    }

    public function render()
    {
        $query = User::where('empresa_id', Auth::user()->empresa_id)
            ->when($this->buscar, fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('name', 'like', "%{$this->buscar}%")
                       ->orWhere('apellido', 'like', "%{$this->buscar}%")
                       ->orWhere('email', 'like', "%{$this->buscar}%")
                       ->orWhere('cedula', 'like', "%{$this->buscar}%")
                )
            )
            ->when($this->filtroTipo, fn($q) => $q->where('tipo_usuario', $this->filtroTipo))
            ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.usuarios.usuario-index', [
            'usuarios' => $query,
            'modulos'  => User::MODULOS,
        ])->layout('layouts.app', [
            'titulo'    => 'Usuarios',
            'subtitulo' => 'Gestión de usuarios y permisos',
        ]);
    }
}
