@extends('layouts.app')

@section('titulo', 'Mis habitaciones')

@section('contenido')
    <main class="contenedor">
        @include('partials.subnav')

        <div class="cabecera-panel">
            <h1 class="titulo-pagina">Mis habitaciones</h1>
            <div>
                @if (auth()->user()->esVerificado())
                    <a href="{{ route('arrendador.habitaciones.create') }}" class="btn"><i class="bi bi-plus-lg"></i> Nueva habitación</a>
                @endif
            </div>
        </div>

        @include('partials.aviso-verificacion')

        @if ($habitaciones->isEmpty())
            <p class="vacio">Todavía no publicaste habitaciones.</p>
        @else
            <div class="tabla-scroll">
                <table class="tabla">
                    <tr>
                        <th>Foto</th>
                        <th>Habitación</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Solicitudes</th>
                        <th>Acciones</th>
                    </tr>
                    @foreach ($habitaciones as $h)
                        <tr>
                            <td><img src="{{ $h->fotoUrl() }}" alt="" class="mini"></td>
                            <td><b>{{ $h->titulo }}</b><br><span class="texto-suave">{{ $h->distrito }} · {{ ucfirst($h->tipo) }}</span>
                                @if ($h->resenas_avg_estrellas)
                                    <br><span class="estrellas">@include('partials.estrellas', ['n' => $h->resenas_avg_estrellas])</span>
                                @endif
                            </td>
                            <td>S/ {{ number_format($h->precio, 0) }}</td>
                            <td>@include('partials.estado', ['estado' => $h->estado])</td>
                            <td>{{ $h->solicitudes_count }}</td>
                            <td class="acciones">
                                <a href="{{ route('arrendador.habitaciones.edit', $h) }}" class="btn btn-borde btn-chico">Editar</a>
                                <form action="{{ route('arrendador.habitaciones.destroy', $h) }}" method="POST" class="form-eliminar">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-peligro btn-chico">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif
    </main>
@endsection
