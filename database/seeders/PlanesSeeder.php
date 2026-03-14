<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $planes = [
            [
                'nombre' => 'Basic',
                'descripcion' => 'Plan básico para aguaterías pequeñas',
                'precio_mensual' => 150000, // 150.000 Gs
                'max_clientes' => 100,
                'max_usuarios' => 2,
                'max_cobradores' => 1,
                'caracteristicas' => [
                    'Gestión de clientes',
                    'Facturación básica',
                    'Reportes básicos',
                    'Soporte por email'
                ],
                'facturacion_automatica' => true,
                'reportes_avanzados' => false,
                'api_acceso' => false,
                'soporte_prioritario' => false,
                'estado' => 'activo',
                'orden' => 1,
            ],
            [
                'nombre' => 'Pro',
                'descripcion' => 'Plan profesional para aguaterías medianas',
                'precio_mensual' => 250000, // 250.000 Gs
                'max_clientes' => 500,
                'max_usuarios' => 5,
                'max_cobradores' => 3,
                'caracteristicas' => [
                    'Todo lo del plan Basic',
                    'Reportes avanzados',
                    'Control de morosidad',
                    'Notificaciones automáticas',
                    'Soporte telefónico'
                ],
                'facturacion_automatica' => true,
                'reportes_avanzados' => true,
                'api_acceso' => false,
                'soporte_prioritario' => true,
                'estado' => 'activo',
                'orden' => 2,
            ],
            [
                'nombre' => 'Premium',
                'descripcion' => 'Plan premium para aguaterías grandes',
                'precio_mensual' => 400000, // 400.000 Gs
                'max_clientes' => 2000,
                'max_usuarios' => 15,
                'max_cobradores' => 10,
                'caracteristicas' => [
                    'Todo lo del plan Pro',
                    'API de integración',
                    'Múltiples cobradores',
                    'Dashboard ejecutivo',
                    'Soporte prioritario 24/7',
                    'Capacitación incluida'
                ],
                'facturacion_automatica' => true,
                'reportes_avanzados' => true,
                'api_acceso' => true,
                'soporte_prioritario' => true,
                'estado' => 'activo',
                'orden' => 3,
            ],
        ];

        foreach ($planes as $plan) {
            Plan::create($plan);
        }
    }
}
