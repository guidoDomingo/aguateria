<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ejecutar seeders en orden de dependencias
        $this->call([
            PlanesSeeder::class,
            EmpresaSeeder::class,
            AdminUserSeeder::class,
            PeriodoFacturacionSeeder::class,
        ]);

        $this->command->info('Base de datos poblada exitosamente!');
        $this->command->info('Usuarios de prueba creados:');
        $this->command->info('- Super Admin: superadmin@aguateria.com / admin123');
        $this->command->info('- Admin Empresa: admin@aguateriamodelo.com.py / admin123');
        $this->command->info('- Cajero: cajero@aguateriamodelo.com.py / cajero123');
        $this->command->info('- Cobrador: cobrador@aguateriamodelo.com.py / cobrador123');
    }
}
