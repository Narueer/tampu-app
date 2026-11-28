@extends('layouts.app')

@section('titulo', 'Verificación en dos pasos')

@section('contenido')
    <main class="contenedor auth">
        @include('partials.auth-imagen', [
            'imagen' => 'catedral.jpg',
            'titulo' => 'Tu cuenta, <em>doble</em> cerradura',
            'texto' => 'Aunque alguien sepa tu contraseña, sin tu celular no puede entrar.',
            'globos' => [
                ['bi-phone-fill', 'Código en tu celular', 'cambia cada 30 segundos'],
                ['bi-key-fill', 'Códigos de respaldo', 'por si pierdes el celular'],
            ],
        ])
        <form class="formulario acceso" action="{{ route('dos-pasos') }}" method="POST">
            @csrf
            <a href="{{ route('login') }}" class="acceso-volver"><i class="bi bi-arrow-left"></i> Volver</a>
            <span class="acceso-icono"><i class="bi bi-shield-lock"></i></span>
            <h2>Verificación en dos pasos</h2>
            <p class="texto-suave">Abre <b>Google Authenticator</b> o <b>Microsoft Authenticator</b> en tu celular y escribe el código de TAMPU.</p>

            @include('partials.errores')

            <label for="codigo" class="visually-hidden">Código</label>
            <div class="codigo-cajas" data-codigo data-respaldo>
                <input type="text" id="codigo" name="codigo" inputmode="numeric" autocomplete="one-time-code" maxlength="9" class="campo-codigo" autofocus required>
            </div>

            <button type="submit" class="btn acceso-enviar" data-cargando="Verificando…">Verificar <i class="bi bi-check2"></i></button>

            <p class="acceso-ayuda">
                <b>¿Perdiste el celular?</b>
                <button type="button" class="btn-enlace" data-usar-respaldo>Usa un código de respaldo</button>
                (por ejemplo <code>abcd-1234</code>).
            </p>
        </form>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/acceso.js') }}?v={{ filemtime(public_path('js/acceso.js')) }}"></script>
@endpush
