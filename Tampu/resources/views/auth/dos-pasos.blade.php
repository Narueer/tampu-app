@extends('layouts.app')

@section('titulo', 'Verificación en dos pasos')

@section('contenido')
    <main class="contenedor">
        <form class="formulario" action="{{ route('dos-pasos') }}" method="POST">
            @csrf
            <h2><i class="bi bi-shield-lock"></i> Verificación en dos pasos</h2>
            <p class="texto-suave">Abre la app autenticadora de tu celular (Google Authenticator o Microsoft Authenticator) y escribe el código de TAMPU.</p>

            @include('partials.errores')

            <label for="codigo">Código de 6 números</label>
            <input type="text" id="codigo" name="codigo" inputmode="numeric" autocomplete="one-time-code" maxlength="9" class="campo-codigo" autofocus required>

            <button type="submit" class="btn">Verificar</button>
            <small class="ayuda">¿Perdiste el celular? Escribe uno de tus códigos de respaldo (por ejemplo abcd-1234).</small>
            <p><a href="{{ route('login') }}">Volver a iniciar sesión</a></p>
        </form>
    </main>
@endsection
