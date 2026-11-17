@extends('layouts.app')

@section('titulo', 'Confirmar correo')

@section('contenido')
    <main class="contenedor">
        <form class="formulario" action="{{ route('correo.confirmar') }}" method="POST">
            @csrf
            <h2><i class="bi bi-envelope-check"></i> Confirma tu correo</h2>
            <p class="texto-suave">Para publicar, postular, pedir visitas o escribir mensajes necesitamos saber que <b>{{ auth()->user()->email }}</b> es tuyo.</p>

            @include('partials.errores')

            @if (session('codigo_local'))
                <p class="alerta alerta-aviso">Modo local: no hay servidor de correo, así que te mostramos el código aquí: <b>{{ session('codigo_local') }}</b></p>
            @endif

            <label for="codigo">Código de 6 números</label>
            <input type="text" id="codigo" name="codigo" inputmode="numeric" autocomplete="one-time-code" maxlength="6" class="campo-codigo" required>

            <button type="submit" class="btn">Confirmar</button>
        </form>

        <form class="formulario formulario-pie" action="{{ route('correo.enviar') }}" method="POST">
            @csrf
            <span class="texto-suave">¿No te llegó o ya venció?</span>
            <button type="submit" class="btn btn-borde">Enviar un código nuevo</button>
        </form>
    </main>
@endsection
