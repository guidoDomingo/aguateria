<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Empresa;
use App\Models\Plan;
use App\Models\Suscripcion;
use App\Models\Ciudad;
use App\Models\Barrio;
use App\Models\Tarifa;
use App\Models\MetodoPago;

class EmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear ciudades de Paraguay
        $asuncion = Ciudad::updateOrCreate(
            ['nombre' => 'Asunción', 'departamento' => 'Capital', 'pais' => 'Paraguay'],
            [
                'codigo_postal' => '1001',
                'activo' => true,
            ]
        );

        $sanLorenzo = Ciudad::updateOrCreate(
            ['nombre' => 'San Lorenzo', 'departamento' => 'Central', 'pais' => 'Paraguay'],
            [
                'codigo_postal' => '2160',
                'activo' => true,
            ]
        );

        // Crear empresa demo
        $empresa = Empresa::updateOrCreate(
            ['codigo' => 'AGUADEMO001'],
            [
                'nombre' => 'Aguatería Modelo S.A.',
                'razon_social' => 'Aguatería Modelo Sociedad Anónima',
                'ruc' => '80012345-1',
                'direccion' => 'Av. Mariscal López 1234',
                'telefono' => '021-123456',
                'email' => 'admin@aguateriamodelo.com.py',
                'ciudad' => 'Asunción',
                'departamento' => 'Capital',
                'pais' => 'Paraguay',
                'moneda' => 'PYG',
                'timezone' => 'America/Asuncion',
                'locale' => 'es',
                'estado' => 'trial',
                'fecha_inicio_trial' => now(),
                'fecha_fin_trial' => now()->addDays(30),
            ]
        );

        // Crear suscripción demo con plan Pro
        $planPro = Plan::where('nombre', 'Pro')->first();
        if ($planPro) {
            Suscripcion::create([
                'empresa_id' => $empresa->id,
                'plan_id' => $planPro->id,
                'fecha_inicio' => now(),
                'fecha_fin' => now()->addYear(),
                'precio_acordado' => $planPro->precio_mensual,
                'tipo_pago' => 'mensual',
                'estado' => 'activa',
                'auto_renovar' => true,
                'proximo_pago' => now()->addMonth(),
            ]);
        }

        // Crear barrios para la empresa demo
        $barrios = [
            ['nombre' => 'Centro', 'ciudad_id' => $asuncion->id],
            ['nombre' => 'Villa Morra', 'ciudad_id' => $asuncion->id],
            ['nombre' => 'Recoleta', 'ciudad_id' => $asuncion->id],
            ['nombre' => 'San Cristóbal', 'ciudad_id' => $sanLorenzo->id],
        ];

        foreach ($barrios as $barrioData) {
            Barrio::create([
                'empresa_id' => $empresa->id,
                'ciudad_id' => $barrioData['ciudad_id'],
                'nombre' => $barrioData['nombre'],
                'activo' => true,
            ]);
        }

        // Crear tarifas para la empresa demo
        $tarifas = [
            [
                'codigo' => 'RES001',
                'nombre' => 'Residencial',
                'descripcion' => 'Tarifa para uso residencial',
                'monto_mensual' => 50000, // 50.000 Gs
                'genera_mora' => true,
                'monto_mora' => 5000, // 5.000 Gs
                'tipo_mora' => 'fijo',
                'dias_vencimiento' => 30,
                'dias_gracia' => 5,
                'costo_reconexion' => 25000,
            ],
            [
                'codigo' => 'COM001',
                'nombre' => 'Comercial',
                'descripcion' => 'Tarifa para uso comercial',
                'monto_mensual' => 80000, // 80.000 Gs
                'genera_mora' => true,
                'monto_mora' => 8000, // 8.000 Gs
                'tipo_mora' => 'fijo',
                'dias_vencimiento' => 30,
                'dias_gracia' => 3,
                'costo_reconexion' => 35000,
            ],
        ];

        foreach ($tarifas as $tarifaData) {
            Tarifa::create(array_merge($tarifaData, ['empresa_id' => $empresa->id]));
        }

        // Crear métodos de pago
        $metodosPago = [
            ['codigo' => 'EFE', 'nombre' => 'Efectivo', 'requiere_referencia' => false],
            ['codigo' => 'TRA', 'nombre' => 'Transferencia Bancaria', 'requiere_referencia' => true],
            ['codigo' => 'QR', 'nombre' => 'Pago QR', 'requiere_referencia' => true],
            ['codigo' => 'CHE', 'nombre' => 'Cheque', 'requiere_referencia' => true],
        ];

        foreach ($metodosPago as $metodoData) {
            MetodoPago::create(array_merge($metodoData, ['empresa_id' => $empresa->id]));
        }
    }
}
