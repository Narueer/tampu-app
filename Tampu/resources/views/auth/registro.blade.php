@extends('layouts.app')

@section('titulo', 'Crear cuenta')

@section('contenido')
    <main class="contenedor">
        <form class="formulario" id="formRegistro" action="{{ route('registro') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            <h2>Crear cuenta</h2>

            @include('partials.errores')

            <label for="tipo">Soy</label>
            <select id="tipo" name="rol">
                <option value="estudiante" @selected(old('rol') === 'estudiante')>Estudiante</option>
                <option value="arrendador" @selected(old('rol') === 'arrendador')>Arrendador (alquilo cuartos)</option>
                <option value="empleador" @selected(old('rol') === 'empleador')>Empleador (ofrezco trabajo)</option>
            </select>

            <label for="nombre">Nombres y apellidos</label>
            <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required>

            <label for="dni">DNI</label>
            <input type="text" id="dni" name="dni" value="{{ old('dni') }}" maxlength="8" inputmode="numeric" required>

            <label for="celular">Celular</label>
            <input type="text" id="celular" name="celular" value="{{ old('celular') }}" maxlength="9" inputmode="numeric">

            <div id="campoProcedencia">
                <label for="procedencia">¿De dónde vienes?</label>
                <input type="text" id="procedencia" name="procedencia" value="{{ old('procedencia') }}" placeholder="Ej: Sicuani">
            </div>

            <div id="campoDocumento" class="oculto">
                <label for="documento">Foto de tu DNI o RUC (jpg, png o pdf)</label>
                <input type="file" id="documento" name="documento" accept=".jpg,.jpeg,.png,.pdf">
                <p class="texto-suave">El administrador lo revisa antes de que puedas publicar. Nadie más lo ve.</p>
            </div>

            <label for="email">Correo</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>

            <label for="clave">Contraseña</label>
            <input type="password" id="clave" name="password" minlength="8" required>
            <small class="ayuda">Mínimo 8 caracteres, con letras y números.</small>

            <label for="clave2">Repetir contraseña</label>
            <input type="password" id="clave2" name="password_confirmation" required>

            <p class="error" id="mensajeRegistro"></p>
            <button type="submit" class="btn">Crear cuenta</button>

            <p>¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
        </form>
    </main>
@endsection
