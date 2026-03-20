<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return redirect('/login');
});

// Dashboard - temporalmente sin middleware tenant para pruebas
Route::get('/dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Ruta de prueba para verificar que el login funciona
Route::get('/test-login', function() {
    $user = auth()->user();
    if ($user) {
        return response()->json([
            'logged_in' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tipo_usuario' => $user->tipo_usuario,
                'empresa_id' => $user->empresa_id,
                'empresa_nombre' => $user->empresa?->nombre ?? 'Sin empresa'
            ]
        ]);
    }
    
    return response()->json(['logged_in' => false]);
})->middleware(['auth']);

// Profile routes
Route::middleware(['auth'])->group(function () {
    Route::view('profile', 'profile')->name('profile.edit');
});

// Rutas principales del sistema (temporalmente sin middleware tenant para desarrollo)
Route::middleware(['auth'])->group(function () {
    
    // Clientes
    Route::get('/clientes', \App\Livewire\Clientes\ClienteIndex::class)
        ->name('clientes.index');
    
    Route::get('/clientes/create', \App\Livewire\Clientes\ClienteForm::class)
        ->name('clientes.create');
        
    Route::get('/clientes/{clienteId}/edit', \App\Livewire\Clientes\ClienteForm::class)
        ->name('clientes.edit');
    
    // Facturas
    Route::get('/facturas', \App\Livewire\Facturas\FacturaIndex::class)
        ->name('facturas.index');
        
    Route::get('/facturas/create', \App\Livewire\Facturas\FacturaForm::class)
        ->name('facturas.create');
        
    Route::get('/facturas/{facturaId}/edit', \App\Livewire\Facturas\FacturaForm::class)
        ->name('facturas.edit');
    
    // Facturas - Vistas y PDFs
    Route::get('/facturas/{factura}/ver', [\App\Http\Controllers\FacturaController::class, 'ver'])
        ->name('facturas.ver');
        
    Route::get('/facturas/{factura}/pdf', [\App\Http\Controllers\FacturaController::class, 'generarPdf'])
        ->name('facturas.pdf');
        
    Route::get('/facturas/{factura}/imprimir', [\App\Http\Controllers\FacturaController::class, 'imprimir'])
        ->name('facturas.imprimir');

    Route::get('/facturas/boletas/masivas', [\App\Http\Controllers\FacturaController::class, 'boletasMasivas'])
        ->name('facturas.boletas.masivas');

    Route::get('/facturas/{factura}/boleta/imprimir', [\App\Http\Controllers\FacturaController::class, 'boletaImprimir'])
        ->name('facturas.boleta.imprimir');

    Route::get('/facturas/{factura}/boleta/pdf', [\App\Http\Controllers\FacturaController::class, 'boletaPdf'])
        ->name('facturas.boleta.pdf');
    
    // Pagos
    Route::get('/pagos', \App\Livewire\Pagos\PagoIndex::class)
        ->name('pagos.index');
    
    Route::get('/pagos/create', \App\Livewire\Pagos\PagoForm::class)
        ->name('pagos.create');
    
    // Recibos
    Route::get('/recibos/{recibo}/pdf', [\App\Http\Controllers\ReciboController::class, 'generarPdf'])
        ->name('recibos.pdf');
        
    Route::get('/recibos/{recibo}/imprimir', [\App\Http\Controllers\ReciboController::class, 'imprimir'])
        ->name('recibos.imprimir');
    
    // Cobradores
    Route::get('/cobradores', \App\Livewire\Cobradores\CobradorIndex::class)
        ->name('cobradores.index');
    
    Route::get('/cobradores/crear', \App\Livewire\Cobradores\CobradorForm::class)
        ->name('cobradores.crear');
        
    Route::get('/cobradores/{cobradorId}/editar', \App\Livewire\Cobradores\CobradorForm::class)
        ->name('cobradores.editar');
    
    // Ciudades
    Route::get('/ciudades', \App\Livewire\Ciudades\CiudadIndex::class)
        ->name('ciudades.index');

    Route::get('/ciudades/crear', \App\Livewire\Ciudades\CiudadForm::class)
        ->name('ciudades.crear');

    Route::get('/ciudades/{ciudadId}/editar', \App\Livewire\Ciudades\CiudadForm::class)
        ->name('ciudades.editar');

    // Barrios
    Route::get('/barrios', \App\Livewire\Barrios\BarrioIndex::class)
        ->name('barrios.index');
    
    Route::get('/barrios/crear', \App\Livewire\Barrios\BarrioForm::class)
        ->name('barrios.crear');
        
    Route::get('/barrios/{barrioId}/editar', \App\Livewire\Barrios\BarrioForm::class)
        ->name('barrios.editar');
    
    // Tarifas
    Route::get('/tarifas', \App\Livewire\Tarifas\TarifaIndex::class)
        ->name('tarifas.index');
    
    Route::get('/tarifas/crear', \App\Livewire\Tarifas\TarifaForm::class)
        ->name('tarifas.crear');
        
    Route::get('/tarifas/{tarifaId}/editar', \App\Livewire\Tarifas\TarifaForm::class)
        ->name('tarifas.editar');
    
    // Zonas
    Route::get('/zonas', \App\Livewire\Zonas\ZonaIndex::class)
        ->name('zonas.index');
    
    Route::get('/zonas/crear', \App\Livewire\Zonas\ZonaForm::class)
        ->name('zonas.crear');
        
    Route::get('/zonas/{zonaId}/editar', \App\Livewire\Zonas\ZonaForm::class)
        ->name('zonas.editar');
    
    // Cortes y Reconexiones
    Route::get('/cortes', function() {
        return view('coming-soon', ['module' => 'Cortes y Reconexiones']);
    })->name('cortes.index');
    
    // Cobranza
    Route::get('/cobranza', function() {
        return view('coming-soon', ['module' => 'Gestión de Cobranza']);
    })->name('cobranza.index');
    
    // Reportes
    Route::get('/reportes', function() {
        return view('coming-soon', ['module' => 'Reportes']);
    })->name('reportes.index');
    
    // Configuración de Recibos
    Route::get('/configuracion-recibos', \App\Livewire\Configuracion\ConfiguracionRecibos::class)
        ->name('configuracion.recibos');

    // Configuración de Facturación (día de generación automática)
    Route::get('/configuracion-facturacion', \App\Livewire\Configuracion\ConfiguracionFacturacion::class)
        ->name('configuracion.facturacion');

    // Configuración de Moras y Avisos
    Route::get('/configuracion-moras', \App\Livewire\Configuracion\ConfiguracionMoras::class)
        ->name('configuracion.moras');
    
    // Configuración (solo para admin)
    Route::middleware(['admin'])->group(function () {
        Route::get('/configuracion', function() {
            return view('coming-soon', ['module' => 'Configuración']);
        })->name('configuracion.index');
        
        Route::get('/usuarios', \App\Livewire\Usuarios\UsuarioIndex::class)->name('usuarios.index');
        Route::get('/usuarios/crear', \App\Livewire\Usuarios\UsuarioForm::class)->name('usuarios.crear');
        Route::get('/usuarios/{usuarioId}/editar', \App\Livewire\Usuarios\UsuarioForm::class)->name('usuarios.editar');
        
        Route::get('/empresa/configuracion', \App\Livewire\Empresa\EmpresaConfiguracion::class)->name('empresa.configuracion');
    });
    
    // Super Admin (solo para super admin)
    Route::middleware(['super.admin'])->group(function () {
        Route::get('/super/empresas', function() {
            return view('coming-soon', ['module' => 'Gestión de Empresas']);
        })->name('super.empresas');
    });
});

require __DIR__.'/auth.php';
