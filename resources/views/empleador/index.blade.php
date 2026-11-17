@extends('layouts.app')

@section('titulo', 'Mis ofertas')

@section('contenido')
    <main class="contenedor">
        @include('partials.subnav')

        @include('partials.bienvenida', [
            'subtitulo' => 'Publica ofertas que encajen con el horario de los estudiantes.',
            'acciones' => auth()->user()->esVerificado()
                ? [[route('empleador.trabajos.create'), 'plus-lg', 'Nueva oferta'], [route('empleador.postulaciones'), 'people', 'Ver postulaciones']]
                : [],
        ])

        @include('partials.kpis', ['kpis' => [
            ['briefcase', $trabajos->count(), 'Ofertas publicadas', 'terracota'],
            ['broadcast', $trabajos->where('estado', 'activa')->count(), 'Ofertas activas', 'verde'],
            ['people', (int) $trabajos->sum('postulaciones_count'), 'Postulaciones recibidas', 'azul', route('empleador.postulaciones')],
            ['chat-dots', auth()->user()->mensajesSinLeer(), 'Mensajes sin leer', 'amarillo', route('mensajes')],
        ]])

        <h2 class="titulo-bloque">Mis ofertas de trabajo</h2>

        @include('partials.aviso-verificacion')

        @if ($trabajos->isEmpty())
            <p class="vacio">Todavía no publicaste ofertas.</p>
        @else
            <div class="tabla-scroll">
                <table class="tabla">
                    <tr>
                        <th>Oferta</th>
                        <th>Horario</th>
                        <th>Pago</th>
                        <th>Estado</th>
                        <th>Postulantes</th>
                        <th>Acciones</th>
                    </tr>
                    @foreach ($trabajos as $t)
                        <tr>
                            <td><b>{{ $t->titulo }}</b><br><span class="texto-suave">{{ $t->empresa }} · {{ ucfirst($t->modalidad) }}</span></td>
                            <td>{{ $t->horarioTexto() }}</td>
                            <td>S/ {{ number_format($t->pago, 0) }} por {{ $t->pago_por === 'dia' ? 'día' : $t->pago_por }}</td>
                            <td>@include('partials.estado', ['estado' => $t->estado])</td>
                            <td>{{ $t->postulaciones_count }}</td>
                            <td class="acciones">
                                <a href="{{ route('empleador.trabajos.edit', $t) }}" class="btn btn-borde btn-chico">Editar</a>
                                <form action="{{ route('empleador.trabajos.destroy', $t) }}" method="POST" class="form-eliminar">
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
