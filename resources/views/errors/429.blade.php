@extends('layouts.app')

@section('titulo', 'Error 429')

@section('contenido')
    <main class="contenedor pagina-error">
        <h1>429</h1>
        <p><b>Vas muy rápido.</b> Hiciste demasiados intentos seguidos. Espera un minuto y vuelve a probar.</p>
        <a href="{{ url()->previous() }}" class="btn">Volver</a>
    </main>
@endsection
