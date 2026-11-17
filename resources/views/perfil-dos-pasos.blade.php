@extends('layouts.app')

@section('titulo', 'Activar verificación en dos pasos')

@section('contenido')
    <main class="contenedor">
        @include('partials.subnav')

        <h1 class="titulo-pagina">Activar verificación en dos pasos</h1>
        <p class="texto-suave">Así, aunque alguien sepa tu contraseña, no puede entrar sin tu celular.</p>

        <div class="dos-pasos">
            <div class="caja">
                <h2>1. Instala una app autenticadora</h2>
                <p>Google Authenticator o Microsoft Authenticator, gratis en Play Store o App Store.</p>
                <h2>2. Escanea este código</h2>
                <p>En la app toca <b>+</b> y luego <b>Escanear código QR</b>.</p>
                <div id="qr" class="qr" data-enlace="{{ $enlace }}"></div>
                <p class="texto-suave">¿No puedes escanear? Escribe esta clave en la app:</p>
                <code class="clave-manual">{{ trim(chunk_split($secreto, 4, ' ')) }}</code>
            </div>

            <form class="caja" action="{{ route('dos-pasos.confirmar') }}" method="POST">
                @csrf
                <h2>3. Escribe el código que aparece</h2>
                @include('partials.errores')
                <label for="codigo">Código de 6 números</label>
                <input type="text" id="codigo" name="codigo" inputmode="numeric" autocomplete="one-time-code" maxlength="7" class="campo-codigo" required>
                <button type="submit" class="btn">Activar</button>
                <a href="{{ route('perfil') }}" class="btn btn-borde">Cancelar</a>
            </form>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // el QR se dibuja en el navegador; el secreto no sale a ningun otro sitio
        const cajaQr = document.getElementById("qr");
        new QRCode(cajaQr, { text: cajaQr.dataset.enlace, width: 200, height: 200, correctLevel: QRCode.CorrectLevel.M });
    </script>
@endpush
