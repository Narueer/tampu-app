@extends('layouts.app')

@section('titulo', 'Error 403')

@section('contenido')
    <main class="contenedor pagina-error">
        <h1>403</h1>
        <p>{{ $exception->getMessage() ?: 'No tienes permiso para entrar a esta página.' }}</p>
        <a href="{{ route('inicio') }}" class="btn">Volver al inicio</a>
    </main>
@endsection
