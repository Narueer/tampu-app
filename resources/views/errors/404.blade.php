@extends('layouts.app')

@section('titulo', 'Error 404')

@section('contenido')
    <main class="contenedor pagina-error">
        <img src="{{ asset('img/3d/cuarto.webp') }}" alt="" class="ilustracion-vacia">
        <h1>404</h1>
        <p>Este cuarto ya no está, o la dirección cambió. Te ayudamos a encontrar otro.</p>
        <a href="{{ route('inicio') }}" class="btn">Volver al inicio</a>
    </main>
@endsection
