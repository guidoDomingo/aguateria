<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar que el usuario esté autenticado
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Super admin puede acceder a todo
        if ($user->tipo_usuario == 'super_admin') {
            return $next($request);
        }

        // Verificar que el usuario tenga empresa asignada
        if (!$user->empresa_id) {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['error' => 'Usuario sin empresa asignada']);
        }

        // Verificar que la empresa esté activa
        $empresa = $user->empresa;
        if (!$empresa || !in_array($empresa->estado, ['activa', 'trial'])) {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['error' => 'Empresa inactiva o suspendida']);
        }

        // Verificar suscripción para empresas no en trial
        if ($empresa->estado !== 'trial') {
            $suscripcion = $empresa->suscripcion;
            if (!$suscripcion || !in_array($suscripcion->estado, ['activa'])) {
                return redirect()->route('suscripcion.vencida');
            }
        }

        // Establecer el tenant actual en la sesión
        session(['tenant_id' => $empresa->id]);
        session(['tenant_nombre' => $empresa->nombre]);
        
        return $next($request);
    }
}
