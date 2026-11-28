@extends('layouts.app')

@section('titulo', 'Confirmar correo')

@section('contenido')
    <main class="contenedor auth">
        @include('partials.auth-imagen', [
            'imagen' => 'plaza.jpg',
            'titulo' => 'Un último <em>paso</em>',
            'texto' => 'Confirmar tu correo nos ayuda a que en TAMPU solo haya personas reales. Así todos publican y alquilan con más confianza.',
            'globos' => [
                ['bi-envelope-check-fill', 'Código de 6 números', 'llega en menos de un minuto'],
                ['bi-people-fill', 'Comunidad verificada', 'estudiantes, arrendadores y empresas'],
            ],
        ])
        <div class="acceso-columna">
            <form class="formulario acceso" action="{{ route('correo.confirmar') }}" method="POST">
                @csrf
                <span class="acceso-icono"><i class="bi bi-envelope-open-heart"></i></span>
                <h2>Confirma tu correo</h2>
                <p class="texto-suave">Escribe el código que enviamos a</p>
                <p class="acceso-correo">{{ \App\Http\Controllers\ClaveController::ocultar(auth()->user()->email) }}</p>

                @include('partials.errores')

                @if (session('codigo_local'))
                    <p class="alerta alerta-aviso">Modo local: no hay servidor de correo, así que te mostramos el código aquí: <b>{{ session('codigo_local') }}</b></p>
                @endif

                <label for="codigo" class="visually-hidden">Código de 6 números</label>
                <div class="codigo-cajas" data-codigo>
                    <input type="text" id="codigo" name="codigo" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" class="campo-codigo" required>
                </div>
                <p class="acceso-nota"><i class="bi bi-clock"></i> El código vence en 15 minutos.</p>

                <button type="submit" class="btn acceso-enviar" data-cargando="Confirmando…">Confirmar <i class="bi bi-check2"></i></button>

                <p class="acceso-ayuda">
                    <b>¿No te llegó?</b> Revisa <b>Spam</b> y <b>Promociones</b>. Busca un correo que empiece con tu código.
                </p>
            </form>

            <form class="formulario formulario-pie acceso-reenviar-caja" action="{{ route('correo.enviar') }}" method="POST">
                @csrf
                <span class="texto-suave">¿Ya venció o no aparece?</span>
                <button type="submit" class="btn btn-borde" data-espera="60">Enviar un código nuevo</button>
            </form>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/acceso.js') }}?v={{ filemtime(public_path('js/acceso.js')) }}"></script>
@endpush
