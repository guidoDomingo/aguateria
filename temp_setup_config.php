<?php

use App\Models\ConfiguracionRecibo;
use App\Models\Empresa;

require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $empresa = Empresa::first();
    
    if ($empresa) {
        $config = ConfiguracionRecibo::where('empresa_id', $empresa->id)->first();
        
        if (!$config) {
            ConfiguracionRecibo::create(['empresa_id' => $empresa->id]);
            echo "Configuración predeterminada creada para empresa: {$empresa->nombre}\n";
        } else {
            echo "Ya existe configuración para empresa: {$empresa->nombre}\n";
        }
    } else {
        echo "No se encontró ninguna empresa\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}