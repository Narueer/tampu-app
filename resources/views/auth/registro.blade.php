@extends('layouts.app')

@section('titulo', 'Crear cuenta')

@section('contenido')
    <main class="contenedor auth">
        @include('partials.auth-imagen', ['titulo' => 'Empieza tu camino en Cusco', 'texto' => 'Crea tu cuenta gratis. Si eres estudiante, marca tu horario y te mostramos solo los trabajos que encajan.'])
        <form class="formulario" id="formRegistro" action="{{ route('registro') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            <h2>Crear cuenta</h2>

            @include('partials.errores')

            @if (! $externo)
                @include('partials.botones-externos', ['texto' => 'Registrarme con'])
            @endif

            <label for="tipo">Soy</label>
            <select id="tipo" name="rol">
                <option value="estudiante" @selected(old('rol') === 'estudiante')>Estudiante</option>
                <option value="arrendador" @selected(old('rol') === 'arrendador')>Arrendador (alquilo cuartos)</option>
                <option value="empleador" @selected(old('rol') === 'empleador')>Empleador (ofrezco trabajo)</option>
            </select>

            <label for="nombre">Nombres y apellidos</label>
            <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $externo['nombre'] ?? '') }}" required>

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
            @if ($externo)
                <input type="email" id="email" value="{{ $externo['email'] }}" disabled>
                <small class="ayuda"><i class="bi bi-patch-check"></i> Comprobado con {{ $externo['via'] }}. Entrarás con ese botón, sin contraseña.
                    @if (\App\Support\Acceso::esInstitucional($externo['email'])) Como es tu correo de la universidad, tu cuenta de estudiante quedará verificada. @endif
                </small>
            @else
                <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" data-sugerir-correo required>

                <label for="clave">Contraseña</label>
                <input type="password" id="clave" name="password" minlength="8" autocomplete="new-password" required data-fuerza>
                <div class="fuerza" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
                <ul class="requisitos" aria-live="polite">
                    <li data-regla="largo"><i class="bi bi-circle"></i> Mínimo 8 caracteres</li>
                    <li data-regla="letra"><i class="bi bi-circle"></i> Al menos una letra</li>
                    <li data-regla="numero"><i class="bi bi-circle"></i> Al menos un número</li>
                    <li data-regla="igual"><i class="bi bi-circle"></i> Las dos coinciden</li>
                </ul>

                <label for="clave2">Repetir contraseña</label>
                <input type="password" id="clave2" name="password_confirmation" autocomplete="new-password" required data-repetir>
            @endif

            <p class="error" id="mensajeRegistro"></p>
            <button type="submit" class="btn">Crear cuenta</button>

            <p>¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
        </form>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/acceso.js') }}?v={{ filemtime(public_path('js/acceso.js')) }}"></script>
@endpush
