@extends('layouts.app')

@section('titulo', 'Error 419')

@section('contenido')
    <main class="contenedor pagina-error">
        <h1>419</h1>
        <p><b>Tu sesión expiró.</b> La página estuvo abierta mucho tiempo y por seguridad se venció. Vuelve atrás, recarga e inténtalo otra vez.</p>
        <a href="{{ url()->previous() }}" class="btn">Volver</a>
    </main>
@endsection
