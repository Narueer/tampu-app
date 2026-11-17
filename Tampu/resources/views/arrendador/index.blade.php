@extends('layouts.app')

@section('titulo', 'Mis habitaciones')

@section('contenido')
    <main class="contenedor">
        @include('partials.subnav')

        @include('partials.bienvenida', [
            'subtitulo' => 'Administra tus habitaciones y responde las visitas que te piden.',
            'acciones' => auth()->user()->esVerificado()
                ? [[route('arrendador.habitaciones.create'), 'plus-lg', 'Nueva habitación'], [route('arrendador.solicitudes'), 'envelope', 'Ver solicitudes']]
                : [],
        ])

        @php($promedio = $habitaciones->whereNotNull('resenas_avg_estrellas')->avg('resenas_avg_estrellas'))
        @include('partials.kpis', ['kpis' => [
            ['house', $habitaciones->count(), 'Habitaciones', 'terracota'],
            ['door-open', $habitaciones->where('estado', 'disponible')->count(), 'Disponibles', 'verde'],
            ['envelope', (int) $habitaciones->sum('solicitudes_count'), 'Solicitudes recibidas', 'azul', route('arrendador.solicitudes')],
            ['star', $promedio ? number_format($promedio, 1) : '—', 'Promedio de reseñas', 'amarillo'],
        ]])

        <h2 class="titulo-bloque">Mis habitaciones</h2>

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
