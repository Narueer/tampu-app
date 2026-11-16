@extends('layouts.app')

@section('titulo', 'Contacto')

@section('contenido')
    <main class="contenedor">
        <h1 class="titulo-pagina">Contacto</h1>

        <div class="contacto">
            <form class="caja" action="{{ route('contacto') }}" method="POST">
                @csrf
                <h2>Escríbenos</h2>
                <p class="texto-suave">¿Tienes una duda, un problema con tu cuenta o quieres avisarnos de una estafa? Déjanos tu mensaje y te respondemos a tu correo.</p>

                @include('partials.errores')

                <div class="fila">
                    <div>
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" value="{{ old('nombre', auth()->user()->nombre ?? '') }}" maxlength="100" required>
                    </div>
                    <div>
                        <label for="email">Correo</label>
                        <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" maxlength="100" required>
                    </div>
                </div>

                <label for="asunto">Asunto</label>
                <select id="asunto" name="asunto" required>
                    @foreach (\App\Models\Contacto::ASUNTOS as $a)
                        <option @selected(old('asunto') === $a)>{{ $a }}</option>
                    @endforeach
                </select>

                <label for="mensaje">Mensaje</label>
                <textarea id="mensaje" name="mensaje" rows="5" minlength="10" maxlength="1000" required>{{ old('mensaje') }}</textarea>

                <button type="submit" class="btn">Enviar mensaje</button>
            </form>

            <aside class="caja datos-contacto">
                <h2>Otros medios</h2>
                <p><i class="bi bi-envelope"></i> <a href="mailto:{{ config('tampu.correo') }}">{{ config('tampu.correo') }}</a></p>
                @if (config('tampu.whatsapp'))
                    <p><i class="bi bi-whatsapp"></i> <a href="https://wa.me/51{{ config('tampu.whatsapp') }}" target="_blank" rel="noopener">{{ config('tampu.whatsapp') }}</a></p>
                @endif
                <p><i class="bi bi-geo-alt"></i> Cusco, Perú</p>
                <p class="aviso">Si alguien te pide un adelanto sin dejarte ver el cuarto, no pagues. Usa el botón "Reportar" de la publicación o escríbenos.</p>
                <p class="texto-suave">También puedes resolver dudas rápidas con el asistente (botón de abajo a la derecha).</p>
            </aside>
        </div>
    </main>
@endsection
