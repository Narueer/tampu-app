@extends('layouts.app')

@section('titulo', 'Iniciar sesión')

@section('contenido')
    <main class="contenedor auth">
        @include('partials.auth-imagen', ['titulo' => 'Qué bueno verte otra vez', 'texto' => 'Revisa tus solicitudes, tus postulaciones y los mensajes que te llegaron.'])
        <form class="formulario" id="formLogin" action="{{ route('login') }}" method="POST" novalidate>
            @csrf
            <h2>Iniciar sesión</h2>

            @include('partials.errores')

            <label for="correo">Correo</label>
            <input type="email" id="correo" name="email" value="{{ old('email') }}" placeholder="correo@ejemplo.com" required data-sugerir-correo autocomplete="email">

            <label for="clave">Contraseña</label>
            <input type="password" id="clave" name="password" autocomplete="current-password" required>

            <div class="fila-login">
                <label class="checkbox"><input type="checkbox" name="recordar"> Recordarme</label>
                <a href="{{ route('clave.olvide') }}">¿Olvidaste tu contraseña?</a>
            </div>

            <p class="error" id="mensajeLogin"></p>
            <button type="submit" class="btn">Ingresar</button>

            @include('partials.botones-externos', ['texto' => 'Entrar con'])

            <p>¿No tienes cuenta? <a href="{{ route('registro') }}">Regístrate aquí</a></p>
        </form>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/acceso.js') }}?v={{ filemtime(public_path('js/acceso.js')) }}"></script>
@endpush
