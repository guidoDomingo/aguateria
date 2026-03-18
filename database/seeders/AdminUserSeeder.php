<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Empresa;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener o crear la empresa
        $empresa = Empresa::first() ?? Empresa::create([
            'nombre'    => 'Aguatería Modelo S.A.',
            'ruc'       => '80012345-6',
            'email'     => 'admin@aguateria.com',
            'telefono'  => '021-123456',
            'direccion' => 'Av. Mariscal López 1234, Asunción',
            'estado'    => 'activa',
        ]);

        // Admin de la empresa (acceso total)
        User::updateOrCreate(
            ['email' => 'admin@aguateria.com'],
            [
                'empresa_id'         => $empresa->id,
                'name'               => 'Administrador',
                'apellido'           => 'Sistema',
                'password'           => 'Admin2024!',
                'tipo_usuario'       => 'admin_empresa',
                'estado'             => 'activo',
                'email_verified_at'  => now(),
            ]
        );

        $this->command->info('✓ Usuario admin creado:');
        $this->command->table(
            ['Campo', 'Valor'],
            [
                ['Email',       'admin@aguateria.com'],
                ['Contraseña',  'Admin2024!'],
                ['Rol',         'Administrador'],
                ['Empresa',     $empresa->nombre],
            ]
        );
    }
}
