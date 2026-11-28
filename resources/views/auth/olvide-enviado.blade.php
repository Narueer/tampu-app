@extends('layouts.app')

@section('titulo', 'Revisa tu correo')

@section('contenido')
    <main class="contenedor auth">
        @include('partials.auth-imagen', [
            'imagen' => 'san-blas.jpg',
            'titulo' => 'Ya va en <em>camino</em>',
            'texto' => 'Abre el correo de TAMPU y presiona “Crear nueva contraseña”. Puedes cerrar esta pestaña.',
            'globos' => [
                ['bi-envelope-check-fill', 'Correo enviado', 'llega en menos de un minuto'],
                ['bi-clock-history', 'Vence en 60 minutos', 'después pides otro'],
            ],
        ])
        <div class="formulario acceso">
            <span class="acceso-icono acceso-icono-ok"><i class="bi bi-envelope-check"></i></span>
            <h2>Revisa tu correo</h2>
            <p class="texto-suave">Enviamos un enlace para cambiar tu contraseña a</p>
            <p class="acceso-correo">{{ $oculto }}</p>

            @if (session('enlace'))
                <p class="alerta alerta-aviso">
                    Modo local: no hay servidor de correo.<br>
                    <a href="{{ session('enlace') }}">Abrir el enlace para cambiar la contraseña</a>
                </p>
            @endif

            <ol class="acceso-pasos">
                <li><i class="bi bi-1-circle"></i> <span>Abre tu correo y busca <b>“Cambia tu contraseña de TAMPU”</b>.</span></li>
                <li><i class="bi bi-2-circle"></i> <span>Presiona <b>Crear nueva contraseña</b>.</span></li>
                <li><i class="bi bi-3-circle"></i> <span>Escribe la nueva y listo, entras de nuevo.</span></li>
            </ol>

            @php
                $dominio = strtolower(\Illuminate\Support\Str::after($correo, '@'));
                $bandeja = match (true) {
                    $dominio === 'gmail.com' || str_ends_with($dominio, '.edu.pe') => ['https://mail.google.com', 'bi-google', 'Abrir Gmail'],
                    in_array($dominio, ['hotmail.com', 'outlook.com', 'live.com', 'outlook.es', 'hotmail.es']) => ['https://outlook.live.com/mail', 'bi-microsoft', 'Abrir Outlook'],
                    str_starts_with($dominio, 'yahoo.') => ['https://mail.yahoo.com', 'bi-envelope', 'Abrir Yahoo Mail'],
                    default => null,
                };
            @endphp
            @if ($bandeja)
                <a href="{{ $bandeja[0] }}" target="_blank" rel="noopener" class="btn acceso-enviar"><i class="bi {{ $bandeja[1] }}"></i> {{ $bandeja[2] }}</a>
            @endif

            <div class="acceso-ayuda">
                <b>¿No te llegó?</b> Revisa <b>Spam</b> y <b>Promociones</b>. Si no está,
                <form action="{{ route('clave.olvide') }}" method="POST" class="acceso-reenviar">
                    @csrf
                    <input type="hidden" name="email" value="{{ $correo }}">
                    <button type="submit" class="btn-enlace" data-espera="60">envíalo de nuevo</button>
                </form>
                o <a href="{{ route('clave.olvide') }}">usa otro correo</a>.
            </div>

            <p class="acceso-pie"><a href="{{ route('login') }}"><i class="bi bi-arrow-left"></i> Volver a iniciar sesión</a></p>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/acceso.js') }}?v={{ filemtime(public_path('js/acceso.js')) }}"></script>
@endpush
