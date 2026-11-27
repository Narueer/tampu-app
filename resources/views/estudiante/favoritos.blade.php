@extends('layouts.app')

@section('titulo', 'Mis favoritos')

@section('contenido')
    <main class="contenedor">
        @include('partials.subnav')

        <h1 class="titulo-pagina">Mis favoritos</h1>

        <div class="tarjetas">
            @forelse ($habitaciones as $h)
                @include('partials.tarjeta-habitacion', ['favoritos' => [$h->id]])
            @empty
                <p class="vacio"><img src="{{ asset('img/3d/cuarto.webp') }}?v={{ @filemtime(public_path('img/3d/cuarto.webp')) }}" alt="" class="ilustracion-vacia">No guardaste habitaciones todavía. Toca el corazón en una habitación para guardarla.</p>
            @endforelse
        </div>
    </main>
@endsection
