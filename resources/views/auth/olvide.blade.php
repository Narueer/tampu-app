@extends('layouts.app')

@section('titulo', 'Recuperar contraseña')

@section('contenido')
    <main class="contenedor auth">
        @include('partials.auth-imagen', [
            'imagen' => 'san-blas.jpg',
            'titulo' => 'Le pasa a <em>todos</em>',
            'texto' => 'Te mandamos un enlace a tu correo y en un minuto vuelves a entrar. Tus cuartos, postulaciones y mensajes siguen ahí.',
            'globos' => [
                ['bi-envelope-paper-heart-fill', 'Enlace por correo', 'vence en 60 minutos'],
                ['bi-shield-check', 'Solo tú lo puedes usar', 'y sirve una sola vez'],
            ],
        ])
        <form class="formulario acceso" action="{{ route('clave.olvide') }}" method="POST" novalidate>
            @csrf
            <a href="{{ route('login') }}" class="acceso-volver"><i class="bi bi-arrow-left"></i> Volver</a>

            <span class="acceso-icono"><i class="bi bi-key"></i></span>
            <h2>¿Olvidaste tu contraseña?</h2>
            <p class="texto-suave">Escribe el correo con el que te registraste y te enviaremos un enlace para crear una nueva.</p>

            @error('email')
                <div class="acceso-aviso acceso-aviso-error" role="alert">
                    <i class="bi bi-exclamation-circle"></i>
                    <div>
                        {{ $message }}
                        @if (session('sin_cuenta'))
                            <a href="{{ route('registro') }}">Crear una cuenta</a>
                        @endif
                    </div>
                </div>
            @enderror

            <label for="correo">Correo</label>
            <div class="campo-con-icono">
                <i class="bi bi-envelope"></i>
                <input type="email" id="correo" name="email" value="{{ old('email') }}" placeholder="tucorreo@gmail.com"
                       autocomplete="email" data-sugerir-correo autofocus required @error('email') aria-invalid="true" @enderror>
            </div>

            <button type="submit" class="btn acceso-enviar" data-cargando="Enviando…">Enviar enlace <i class="bi bi-arrow-right"></i></button>

            <p class="acceso-pie">¿Te acordaste? <a href="{{ route('login') }}">Inicia sesión</a></p>
        </form>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/acceso.js') }}?v={{ filemtime(public_path('js/acceso.js')) }}"></script>
@endpush
