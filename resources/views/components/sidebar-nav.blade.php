<nav class="mt-4 px-2 pb-4 space-y-0.5">

    {{-- PRINCIPAL --}}
    @if(auth()->user()->tienePermiso('dashboard'))
    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
        <i class="fas fa-chart-pie w-5 h-5 mr-3"></i> Dashboard
    </x-nav-link>
    @endif

    @if(auth()->user()->tienePermiso('clientes'))
    <x-nav-link href="{{ route('clientes.index') }}" :active="request()->routeIs('clientes.*')">
        <i class="fas fa-users w-5 h-5 mr-3"></i> Clientes
    </x-nav-link>
    @endif

    @if(auth()->user()->tienePermiso('facturas'))
    <x-nav-link href="{{ route('facturas.index') }}" :active="request()->routeIs('facturas.*')">
        <i class="fas fa-file-invoice w-5 h-5 mr-3"></i> Facturación
    </x-nav-link>
    @endif

    @if(auth()->user()->tienePermiso('pagos'))
    <x-nav-link href="{{ route('pagos.index') }}" :active="request()->routeIs('pagos.*')">
        <i class="fas fa-credit-card w-5 h-5 mr-3"></i> Pagos
    </x-nav-link>
    @endif

    @if(auth()->user()->tienePermiso('cobradores'))
    <x-nav-link href="{{ route('cobradores.index') }}" :active="request()->routeIs('cobradores.*')">
        <i class="fas fa-user-tie w-5 h-5 mr-3"></i> Cobradores
    </x-nav-link>
    @endif

    @if(auth()->user()->tienePermiso('cobranza'))
    <x-nav-link href="{{ route('cobranza.index') }}" :active="request()->routeIs('cobranza.*')">
        <i class="fas fa-money-check-alt w-5 h-5 mr-3"></i> Cobranza
    </x-nav-link>
    @endif

    @if(auth()->user()->tienePermiso('reportes'))
    <x-nav-link href="{{ route('reportes.index') }}" :active="request()->routeIs('reportes.*')">
        <i class="fas fa-chart-bar w-5 h-5 mr-3"></i> Reportes
    </x-nav-link>
    @endif

    {{-- CONFIGURACIÓN --}}
    @if(auth()->user()->tienePermiso('configuracion'))
    <div class="mt-5 mb-1 px-3 py-1 text-xs font-semibold text-gray-400 uppercase tracking-widest">
        Configuración
    </div>

    @if(auth()->user()->tienePermiso('zonas'))
    <x-nav-link href="{{ route('zonas.index') }}" :active="request()->routeIs('zonas.*')">
        <i class="fas fa-map w-5 h-5 mr-3"></i> Zonas
    </x-nav-link>
    @endif

    @if(auth()->user()->tienePermiso('ciudades'))
    <x-nav-link href="{{ route('ciudades.index') }}" :active="request()->routeIs('ciudades.*')">
        <i class="fas fa-city w-5 h-5 mr-3"></i> Ciudades
    </x-nav-link>
    @endif

    @if(auth()->user()->tienePermiso('barrios'))
    <x-nav-link href="{{ route('barrios.index') }}" :active="request()->routeIs('barrios.*')">
        <i class="fas fa-map-marker-alt w-5 h-5 mr-3"></i> Barrios
    </x-nav-link>
    @endif

    @if(auth()->user()->tienePermiso('tarifas'))
    <x-nav-link href="{{ route('tarifas.index') }}" :active="request()->routeIs('tarifas.*')">
        <i class="fas fa-dollar-sign w-5 h-5 mr-3"></i> Tarifas
    </x-nav-link>
    @endif

    <x-nav-link href="{{ route('configuracion.recibos') }}" :active="request()->routeIs('configuracion.recibos')">
        <i class="fas fa-receipt w-5 h-5 mr-3"></i> Config. Recibos
    </x-nav-link>

    <x-nav-link href="{{ route('configuracion.facturacion') }}" :active="request()->routeIs('configuracion.facturacion')">
        <i class="fas fa-calendar-alt w-5 h-5 mr-3"></i> Config. Facturación
    </x-nav-link>

    <x-nav-link href="{{ route('configuracion.moras') }}" :active="request()->routeIs('configuracion.moras')">
        <i class="fas fa-percentage w-5 h-5 mr-3"></i> Config. Moras
    </x-nav-link>
    @endif

    {{-- ADMINISTRACIÓN --}}
    @if(auth()->user()->tienePermiso('usuarios'))
    <div class="mt-5 mb-1 px-3 py-1 text-xs font-semibold text-gray-400 uppercase tracking-widest">
        Administración
    </div>

    <x-nav-link href="{{ route('usuarios.index') }}" :active="request()->routeIs('usuarios.*')">
        <i class="fas fa-user-cog w-5 h-5 mr-3"></i> Usuarios
    </x-nav-link>

    <x-nav-link href="{{ route('empresa.configuracion') }}" :active="request()->routeIs('empresa.*')">
        <i class="fas fa-building w-5 h-5 mr-3"></i> Mi Empresa
    </x-nav-link>
    @endif

    {{-- SUPER ADMIN --}}
    @if(auth()->user()->isSuperAdmin())
    <div class="mt-5 mb-1 px-3 py-1 text-xs font-semibold text-gray-400 uppercase tracking-widest">
        Super Admin
    </div>

    <x-nav-link href="{{ route('super.empresas') }}" :active="request()->routeIs('super.*')">
        <i class="fas fa-globe w-5 h-5 mr-3"></i> Gestión Empresas
    </x-nav-link>
    @endif

</nav>
