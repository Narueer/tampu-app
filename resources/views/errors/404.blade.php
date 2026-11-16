@extends('layouts.app')

@section('titulo', 'Error 404')

@section('contenido')
    <main class="contenedor pagina-error">
        <h1>404</h1>
        <p>La página que buscas no existe o ya no está disponible.</p>
        <a href="{{ route('inicio') }}" class="btn">Volver al inicio</a>
    </main>
@endsection
