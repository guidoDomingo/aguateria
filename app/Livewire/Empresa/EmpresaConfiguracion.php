<?php

namespace App\Livewire\Empresa;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class EmpresaConfiguracion extends Component
{
    use WithFileUploads;

    // Datos generales
    public string  $nombre       = '';
    public string  $razon_social = '';
    public string  $ruc          = '';
    public string  $direccion    = '';
    public string  $telefono     = '';
    public string  $email        = '';
    public string  $ciudad       = '';
    public string  $departamento = '';
    public string  $pais         = 'Paraguay';

    // Logo
    public $logo_nuevo = null;
    public string $logo_actual = '';

    public $empresa;

    protected $rules = [
        'nombre'       => 'required|string|max:150',
        'razon_social' => 'nullable|string|max:150',
        'ruc'          => 'nullable|string|max:20',
        'direccion'    => 'nullable|string|max:255',
        'telefono'     => 'nullable|string|max:30',
        'email'        => 'nullable|email|max:100',
        'ciudad'       => 'nullable|string|max:100',
        'departamento' => 'nullable|string|max:100',
        'pais'         => 'nullable|string|max:100',
        'logo_nuevo'   => 'nullable|image|max:2048',
    ];

    protected $messages = [
        'nombre.required'   => 'El nombre de la empresa es obligatorio',
        'email.email'       => 'Ingrese un email válido',
        'logo_nuevo.image'  => 'El logo debe ser una imagen',
        'logo_nuevo.max'    => 'El logo no puede superar 2MB',
    ];

    public function mount(): void
    {
        $this->empresa = Auth::user()->empresa;

        $this->nombre       = $this->empresa->nombre       ?? '';
        $this->razon_social = $this->empresa->razon_social ?? '';
        $this->ruc          = $this->empresa->ruc          ?? '';
        $this->direccion    = $this->empresa->direccion    ?? '';
        $this->telefono     = $this->empresa->telefono     ?? '';
        $this->email        = $this->empresa->email        ?? '';
        $this->ciudad       = $this->empresa->ciudad       ?? '';
        $this->departamento = $this->empresa->departamento ?? '';
        $this->pais         = $this->empresa->pais         ?? 'Paraguay';
        $this->logo_actual  = $this->empresa->logo         ?? '';
    }

    public function guardar(): void
    {
        $this->validate();

        try {
            $data = [
                'nombre'       => $this->nombre,
                'razon_social' => $this->razon_social,
                'ruc'          => $this->ruc,
                'direccion'    => $this->direccion,
                'telefono'     => $this->telefono,
                'email'        => $this->email,
                'ciudad'       => $this->ciudad,
                'departamento' => $this->departamento,
                'pais'         => $this->pais,
            ];

            if ($this->logo_nuevo) {
                $path = $this->redimensionarYGuardarLogo($this->logo_nuevo);
                $data['logo'] = $path;
                $this->logo_actual = $path;
                $this->logo_nuevo  = null;
            }

            $this->empresa->update($data);

            $this->dispatch('toast', message: 'Datos de la empresa guardados correctamente', type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Error al guardar: ' . $e->getMessage(), type: 'error');
        }
    }

    /**
     * Redimensiona el logo a máx 300x300px usando Intervention Image.
     * Funciona en local y producción sin importar si hay GD o Imagick.
     */
    private function redimensionarYGuardarLogo($archivo): string
    {
        $filename = 'logos/' . \Illuminate\Support\Str::uuid() . '.png';
        $fullPath = storage_path('app/public/' . $filename);

        if (!is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0775, true);
        }

        $manager = new \Intervention\Image\ImageManager(
            new \Intervention\Image\Drivers\Gd\Driver()
        );

        $manager->read($archivo->getRealPath())
                ->scaleDown(width: 300, height: 300)
                ->toPng()
                ->save($fullPath);

        return $filename;
    }

    public function render()
    {
        return view('livewire.empresa.empresa-configuracion')
            ->layout('layouts.app', ['title' => 'Mi Empresa']);
    }
}
