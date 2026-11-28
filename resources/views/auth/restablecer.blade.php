@extends('layouts.app')

@section('titulo', 'Nueva contraseña')

@section('contenido')
    <main class="contenedor auth">
        @include('partials.auth-imagen', [
            'imagen' => 'catedral.jpg',
            'titulo' => 'Un nuevo <em>comienzo</em>',
            'texto' => 'Elige una contraseña que no uses en otras páginas. Así, aunque otra web tenga un problema, tu cuenta de TAMPU sigue segura.',
            'globos' => [
                ['bi-shield-lock-fill', 'Guardada cifrada', 'nadie puede verla, ni nosotros'],
                ['bi-phone', 'Activa los dos pasos', 'en Mi perfil, para más seguridad'],
            ],
        ])

        @if (! $valido && ! $errors->any())
            <div class="formulario acceso">
                <span class="acceso-icono acceso-icono-error"><i class="bi bi-hourglass-bottom"></i></span>
                <h2>Este enlace ya no sirve</h2>
                <p class="texto-suave">Los enlaces vencen a los <b>60 minutos</b> y funcionan <b>una sola vez</b>. Si ya cambiaste tu contraseña con él, simplemente inicia sesión.</p>
                <a href="{{ route('clave.olvide') }}" class="btn acceso-enviar">Pedir un enlace nuevo <i class="bi bi-arrow-right"></i></a>
                <p class="acceso-pie"><a href="{{ route('login') }}">Iniciar sesión</a></p>
            </div>
        @else
            <form class="formulario acceso" action="{{ route('clave.guardar') }}" method="POST" data-clave-nueva>
                @csrf
                <span class="acceso-icono"><i class="bi bi-shield-lock"></i></span>
                <h2>{{ $nombre ? $nombre . ', crea' : 'Crea' }} tu nueva contraseña</h2>
                <p class="texto-suave">Para la cuenta <b>{{ $email }}</b></p>

                @include('partials.errores')

                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ old('email', $email) }}">

                <label for="clave">Nueva contraseña</label>
                <div class="campo-clave">
                    <input type="password" id="clave" name="password" minlength="8" autocomplete="new-password" autofocus required data-fuerza>
                </div>
                <div class="fuerza" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
                <ul class="requisitos" aria-live="polite">
                    <li data-regla="largo"><i class="bi bi-circle"></i> Mínimo 8 caracteres</li>
                    <li data-regla="letra"><i class="bi bi-circle"></i> Al menos una letra</li>
                    <li data-regla="numero"><i class="bi bi-circle"></i> Al menos un número</li>
                    <li data-regla="igual"><i class="bi bi-circle"></i> Las dos contraseñas coinciden</li>
                </ul>

                <label for="clave2">Repite la contraseña</label>
                <div class="campo-clave">
                    <input type="password" id="clave2" name="password_confirmation" minlength="8" autocomplete="new-password" required data-repetir>
                </div>

                <button type="submit" class="btn acceso-enviar" data-cargando="Guardando…">Guardar contraseña <i class="bi bi-check2"></i></button>
            </form>
        @endif
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/acceso.js') }}?v={{ filemtime(public_path('js/acceso.js')) }}"></script>
@endpush
