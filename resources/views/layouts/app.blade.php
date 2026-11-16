<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Inicio') - TAMPU</title>
    <link rel="icon" href="{{ asset('img/favicon.png') }}">
    <!-- fuente Poppins y Bootstrap Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
    @stack('estilos')
</head>
<body class="{{ request()->routeIs('mensajes.ver') ? 'en-chat' : '' }}">

    <!-- menu -->
    <header>
        <div class="contenedor">
            <a href="{{ route('inicio') }}" class="logo">
                <img src="{{ asset('img/logo-160.png') }}" alt="Logo de Tampu">
                TAMPU
            </a>
            <button id="btnMenu" aria-label="Abrir menú">☰</button>
            <nav id="menu">
                <a href="{{ route('inicio') }}" class="{{ request()->routeIs('inicio') ? 'activo' : '' }}">Inicio</a>
                <a href="{{ route('habitaciones.index') }}" class="{{ request()->routeIs('habitaciones.*') ? 'activo' : '' }}">Habitaciones</a>
                <a href="{{ route('trabajos.index') }}" class="{{ request()->routeIs('trabajos.*') ? 'activo' : '' }}">Trabajos</a>
                <a href="{{ route('nosotros') }}" class="{{ request()->routeIs('nosotros') ? 'activo' : '' }}">Nosotros</a>
                <a href="{{ route('contacto') }}" class="{{ request()->routeIs('contacto') ? 'activo' : '' }}">Contacto</a>
                @auth
                    @php($sinLeer = auth()->user()->unreadNotifications()->count())
                    @php($chats = auth()->user()->mensajesSinLeer())
                    <a href="{{ route('notificaciones') }}" class="icono-menu {{ request()->routeIs('notificaciones') ? 'activo' : '' }}" title="Notificaciones">
                        <i class="bi bi-bell"></i><span class="solo-celular"> Notificaciones</span>
                        @if ($sinLeer)<span class="globo">{{ $sinLeer }}</span>@endif
                    </a>
                    <a href="{{ route('mensajes') }}" class="icono-menu {{ request()->routeIs('mensajes*') ? 'activo' : '' }}" title="Mensajes">
                        <i class="bi bi-chat-dots"></i><span class="solo-celular"> Mensajes</span>
                        @if ($chats)<span class="globo">{{ $chats }}</span>@endif
                    </a>
                    <a href="{{ route('panel') }}" class="{{ request()->is('estudiante*', 'arrendador*', 'empleador*', 'admin*', 'perfil', 'calendario') ? 'activo' : '' }}">
                        @if (auth()->user()->foto)
                            <img src="{{ auth()->user()->fotoUrl() }}" alt="" class="avatar-menu">
                        @else
                            <i class="bi bi-person-circle"></i>
                        @endif
                        Mi panel
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="form-salir">
                        @csrf
                        <button type="submit" class="btn btn-borde btn-chico">Salir</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="{{ request()->routeIs('login', 'registro') ? 'activo' : '' }}">Iniciar sesión</a>
                @endauth
            </nav>
        </div>
    </header>

    @if (session('ok') || session('error'))
        <div class="contenedor">
            @if (session('ok'))
                <p class="alerta alerta-ok"><i class="bi bi-check-circle"></i> {{ session('ok') }}</p>
            @endif
            @if (session('error'))
                <p class="alerta alerta-error"><i class="bi bi-exclamation-circle"></i> {{ session('error') }}</p>
            @endif
        </div>
    @endif

    @yield('contenido')

    <!-- footer -->
    <footer>
        <p>
            <a href="{{ route('inicio') }}">Inicio</a>
            <a href="{{ route('habitaciones.index') }}">Habitaciones</a>
            <a href="{{ route('trabajos.index') }}">Trabajos</a>
            <a href="{{ route('nosotros') }}">Nosotros</a>
            <a href="{{ route('contacto') }}">Contacto</a>
        </p>
        <p><i class="bi bi-envelope"></i> <a href="mailto:{{ config('tampu.correo') }}">{{ config('tampu.correo') }}</a></p>
        <p>© 2026 TAMPU - Proyecto del curso Programación Web - Universidad Continental</p>
    </footer>

    @include('partials.asistente')

    <script src="{{ asset('js/script.js') }}"></script>
    @stack('scripts')
</body>
</html>
