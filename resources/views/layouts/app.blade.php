<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Inicio') - TAMPU</title>
    <link rel="icon" href="{{ asset('img/favicon.png') }}">
    <!-- para instalarla como app en el celular -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#17352f">
    <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="TAMPU">
    <!-- fuentes Fraunces y Plus Jakarta Sans, Bootstrap 5 y Bootstrap Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,400;1,9..144,500&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}?v={{ filemtime(public_path('css/estilos.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/tema.css') }}?v={{ filemtime(public_path('css/tema.css')) }}">
    <script>document.documentElement.classList.add('con-js');</script>
    @stack('estilos')
</head>
<body class="{{ request()->routeIs('mensajes.ver') ? 'en-chat' : '' }}">

    <!-- menu -->
    <header id="cabecera">
        <div class="contenedor">
            <a href="{{ route('inicio') }}" class="logo">
                <img src="{{ asset('img/logo-160.png') }}" alt="Logo de Tampu">
                <span>Tampu<small>Cusco · estudiantes</small></span>
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
                    <a href="{{ route('notificaciones') }}" id="iconoAvisos" class="icono-menu {{ request()->routeIs('notificaciones') ? 'activo' : '' }}" title="Notificaciones">
                        <i class="bi bi-bell"></i><span class="solo-celular"> Notificaciones</span>
                        @if ($sinLeer)<span class="globo">{{ $sinLeer }}</span>@endif
                    </a>
                    <a href="{{ route('mensajes') }}" id="iconoChats" class="icono-menu {{ request()->routeIs('mensajes*') ? 'activo' : '' }}" title="Mensajes">
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
                    <a href="{{ route('login') }}" class="menu-cta {{ request()->routeIs('login', 'registro') ? 'activo' : '' }}">Iniciar sesión</a>
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
    <div class="banda-andina" aria-hidden="true"></div>
    <footer class="pie">
        <div class="contenedor">
            <p class="pie-lema">Tu casa de paso <em>en el camino</em><br>hacia la universidad.</p>
            <div class="row g-4 pie-columnas">
                <div class="col-lg-4 col-md-6">
                    <a href="{{ route('inicio') }}" class="logo logo-pie">
                        <img src="{{ asset('img/logo-160.png') }}" alt="Logo de Tampu"> Tampu
                    </a>
                    <p class="pie-texto">Habitaciones seguras y trabajos que encajan con tus clases, para estudiantes que llegan a Cusco.</p>
                    <button type="button" id="btnInstalar" class="btn-instalar oculto"><i class="bi bi-phone"></i> Instalar la app</button>
                </div>
                <div class="col-lg-2 col-md-6 col-6">
                    <h4>Explora</h4>
                    <a href="{{ route('habitaciones.index') }}">Habitaciones</a>
                    <a href="{{ route('trabajos.index') }}">Trabajos</a>
                    <a href="{{ route('nosotros') }}">Nosotros</a>
                    <a href="{{ route('contacto') }}">Contacto</a>
                </div>
                <div class="col-lg-3 col-md-6 col-6">
                    <h4>Tu cuenta</h4>
                    @auth
                        <a href="{{ route('panel') }}">Mi panel</a>
                        <a href="{{ route('perfil') }}">Mi perfil</a>
                        <a href="{{ route('mensajes') }}">Mensajes</a>
                    @else
                        <a href="{{ route('registro') }}">Soy estudiante</a>
                        <a href="{{ route('registro') }}">Alquilo un cuarto</a>
                        <a href="{{ route('registro') }}">Ofrezco trabajo</a>
                        <a href="{{ route('login') }}">Iniciar sesión</a>
                    @endauth
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4>Contacto</h4>
                    <p><i class="bi bi-envelope"></i> <a href="mailto:{{ config('tampu.correo') }}">{{ config('tampu.correo') }}</a></p>
                    <p><i class="bi bi-geo-alt"></i> Cusco, Perú</p>
                    <p class="pie-aviso"><i class="bi bi-shield-check"></i> Nunca pagues un adelanto sin visitar el cuarto.</p>
                </div>
            </div>
            <div class="pie-final">
                <span>© 2026 TAMPU · Proyecto del curso Programación Web · Universidad Continental</span>
                <a href="#" class="subir" aria-label="Volver arriba"><i class="bi bi-arrow-up"></i></a>
            </div>
        </div>
    </footer>

    @include('partials.asistente')

    @auth
        <a href="{{ route('notificaciones') }}" id="avisoNuevo" class="aviso-nuevo oculto" data-estado="{{ route('estado') }}">
            <i class="bi bi-bell"></i> <span></span>
        </a>
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/script.js') }}?v={{ filemtime(public_path('js/script.js')) }}"></script>
    @stack('scripts')
</body>
</html>
