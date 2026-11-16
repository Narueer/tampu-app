@extends('layouts.app')

@section('titulo', 'Recuperar contraseña')

@section('contenido')
    <main class="contenedor">
        <form class="formulario" action="{{ route('clave.olvide') }}" method="POST">
            @csrf
            <h2>Recuperar contraseña</h2>
            <p class="texto-suave">Escribe el correo con el que te registraste y te enviaremos un enlace para crear una nueva contraseña.</p>

            @include('partials.errores')

            @if (session('enlace'))
                <p class="alerta alerta-aviso">
                    Modo local: no hay servidor de correo, así que el enlace también queda en storage/logs/laravel.log.<br>
                    <a href="{{ session('enlace') }}">Abrir el enlace para cambiar la contraseña</a>
                </p>
            @endif

            <label for="correo">Correo</label>
            <input type="email" id="correo" name="email" value="{{ old('email') }}" required>

            <button type="submit" class="btn">Enviar enlace</button>
            <p><a href="{{ route('login') }}">Volver a iniciar sesión</a></p>
        </form>
    </main>
@endsection
