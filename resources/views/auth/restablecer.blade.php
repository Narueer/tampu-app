@extends('layouts.app')

@section('titulo', 'Nueva contraseña')

@section('contenido')
    <main class="contenedor">
        <form class="formulario" action="{{ route('clave.guardar') }}" method="POST">
            @csrf
            <h2>Crea tu nueva contraseña</h2>

            @include('partials.errores')

            <input type="hidden" name="token" value="{{ $token }}">
            <label for="correo">Correo</label>
            <input type="email" id="correo" name="email" value="{{ old('email', $email) }}" required>

            <label for="clave">Nueva contraseña</label>
            <input type="password" id="clave" name="password" minlength="6" required>

            <label for="clave2">Repite la contraseña</label>
            <input type="password" id="clave2" name="password_confirmation" minlength="6" required>

            <button type="submit" class="btn">Guardar contraseña</button>
        </form>
    </main>
@endsection
