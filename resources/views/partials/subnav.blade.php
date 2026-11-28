@php($rol = auth()->user()->rol)
<nav class="subnav">
    @if ($rol === 'estudiante')
        <a href="{{ route('estudiante.panel') }}" class="{{ request()->routeIs('estudiante.panel') ? 'activo' : '' }}"><i class="bi bi-grid"></i> Mi panel</a>
        <a href="{{ route('estudiante.favoritos') }}" class="{{ request()->routeIs('estudiante.favoritos') ? 'activo' : '' }}"><i class="bi bi-heart"></i> Favoritos</a>
    @elseif ($rol === 'arrendador')
        <a href="{{ route('arrendador.habitaciones.index') }}" class="{{ request()->routeIs('arrendador.habitaciones.*') ? 'activo' : '' }}"><i class="bi bi-house"></i> Mis habitaciones</a>
        <a href="{{ route('arrendador.solicitudes') }}" class="{{ request()->routeIs('arrendador.solicitudes') ? 'activo' : '' }}"><i class="bi bi-envelope"></i> Solicitudes</a>
    @elseif ($rol === 'empleador')
        <a href="{{ route('empleador.trabajos.index') }}" class="{{ request()->routeIs('empleador.trabajos.*') ? 'activo' : '' }}"><i class="bi bi-briefcase"></i> Mis ofertas</a>
        <a href="{{ route('empleador.postulaciones') }}" class="{{ request()->routeIs('empleador.postulaciones') ? 'activo' : '' }}"><i class="bi bi-people"></i> Postulaciones</a>
    @else
        <a href="{{ route('admin.panel') }}" class="{{ request()->routeIs('admin.panel') ? 'activo' : '' }}"><i class="bi bi-speedometer2"></i> Panel</a>
        <a href="{{ route('admin.usuarios') }}" class="{{ request()->routeIs('admin.usuarios') ? 'activo' : '' }}"><i class="bi bi-people"></i> Usuarios</a>
        <a href="{{ route('admin.reportes') }}" class="{{ request()->routeIs('admin.reportes') ? 'activo' : '' }}"><i class="bi bi-flag"></i> Reportes</a>
        <a href="{{ route('admin.contactos') }}" class="{{ request()->routeIs('admin.contactos') ? 'activo' : '' }}"><i class="bi bi-envelope"></i> Contacto</a>
        <a href="{{ route('admin.seguridad') }}" class="{{ request()->routeIs('admin.seguridad') ? 'activo' : '' }}"><i class="bi bi-shield-lock"></i> Seguridad</a>
    @endif
    @if ($rol !== 'admin')
        <a href="{{ route('calendario') }}" class="{{ request()->routeIs('calendario') ? 'activo' : '' }}"><i class="bi bi-calendar3"></i> {{ $rol === 'estudiante' ? 'Planificador' : 'Calendario' }}</a>
    @endif
    <a href="{{ route('perfil') }}" class="{{ request()->routeIs('perfil') ? 'activo' : '' }}"><i class="bi bi-person-gear"></i> Mi perfil</a>
</nav>
