<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Empresa;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario super admin (sin empresa)
        User::create([
            'name' => 'Super Admin',
            'apellido' => 'Sistema',
            'email' => 'superadmin@aguateria.com',
            'password' => Hash::make('admin123'),
            'telefono' => '0981-123456',
            'cedula' => '1234567',
            'tipo_usuario' => 'super_admin',
            'estado' => 'activo',
            'email_verified_at' => now(),
        ]);

        // Obtener la empresa demo
        $empresa = Empresa::where('codigo', 'AGUADEMO001')->first();
        
        if ($empresa) {
            // Crear admin de la empresa demo
            User::create([
                'empresa_id' => $empresa->id,
                'name' => 'Administrador',
                'apellido' => 'Empresa',
                'email' => 'admin@aguateriamodelo.com.py',
                'password' => Hash::make('admin123'),
                'telefono' => '021-123456',
                'cedula' => '2345678',
                'direccion' => 'Av. Mariscal López 1234',
                'tipo_usuario' => 'admin_empresa',
                'estado' => 'activo',
                'email_verified_at' => now(),
            ]);

            // Crear cajero para la empresa demo
            User::create([
                'empresa_id' => $empresa->id,
                'name' => 'María',
                'apellido' => 'González',
                'email' => 'cajero@aguateriamodelo.com.py',
                'password' => Hash::make('cajero123'),
                'telefono' => '0985-987654',
                'cedula' => '3456789',
                'direccion' => 'Barrio Centro',
                'tipo_usuario' => 'cajero',
                'estado' => 'activo',
                'email_verified_at' => now(),
            ]);

            // Crear cobrador para la empresa demo
            User::create([
                'empresa_id' => $empresa->id,
                'name' => 'Carlos',
                'apellido' => 'Ramírez',
                'email' => 'cobrador@aguateriamodelo.com.py',
                'password' => Hash::make('cobrador123'),
                'telefono' => '0986-555444',
                'cedula' => '4567890',
                'direccion' => 'Barrio San Cristóbal',
                'tipo_usuario' => 'cobrador',
                'estado' => 'activo',
                'email_verified_at' => now(),
            ]);
        }
    }
}
