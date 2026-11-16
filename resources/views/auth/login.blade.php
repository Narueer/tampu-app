@extends('layouts.app')

@section('titulo', 'Iniciar sesión')

@section('contenido')
    <main class="contenedor">
        <form class="formulario" id="formLogin" action="{{ route('login') }}" method="POST" novalidate>
            @csrf
            <h2>Iniciar sesión</h2>

            @include('partials.errores')

            <label for="correo">Correo</label>
            <input type="email" id="correo" name="email" value="{{ old('email') }}" placeholder="correo@ejemplo.com" required>

            <label for="clave">Contraseña</label>
            <input type="password" id="clave" name="password" required>

            <div class="fila-login">
                <label class="checkbox"><input type="checkbox" name="recordar"> Recordarme</label>
                <a href="{{ route('clave.olvide') }}">¿Olvidaste tu contraseña?</a>
            </div>

            <p class="error" id="mensajeLogin"></p>
            <button type="submit" class="btn">Ingresar</button>

            <p>¿No tienes cuenta? <a href="{{ route('registro') }}">Regístrate aquí</a></p>
        </form>
    </main>
@endsection
