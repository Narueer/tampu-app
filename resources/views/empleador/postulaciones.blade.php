@extends('layouts.app')

@section('titulo', 'Postulaciones')

@section('contenido')
    <main class="contenedor">
        @include('partials.subnav')

        <h1 class="titulo-pagina">Postulaciones recibidas</h1>

        @if ($postulaciones->isEmpty())
            <p class="vacio">Aún no recibiste postulaciones.</p>
        @else
            <div class="tabla-scroll">
                <table class="tabla">
                    <tr>
                        <th>Estudiante</th>
                        <th>Oferta</th>
                        <th>Mensaje</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Responder</th>
                    </tr>
                    @foreach ($postulaciones as $p)
                        <tr>
                            <td>
                                <b>{{ $p->estudiante->nombre }}</b><br>
                                <span class="texto-suave">De {{ $p->estudiante->procedencia ?: '—' }}</span>
                                @if ($p->estado === 'aceptada')
                                    <br><i class="bi bi-telephone"></i> {{ $p->estudiante->celular }}
                                @endif
                            </td>
                            <td>{{ $p->trabajo->titulo }}</td>
                            <td>{{ $p->mensaje ?: '—' }}</td>
                            <td>{{ $p->created_at->format('d/m/Y') }}</td>
                            <td>@include('partials.estado', ['estado' => $p->estado])</td>
                            <td class="acciones">
                                @if ($p->estado === 'pendiente')
                                    <form action="{{ route('empleador.postulaciones.responder', $p) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button name="estado" value="aceptada" class="btn btn-chico">Aceptar</button>
                                        <button name="estado" value="rechazada" class="btn btn-borde btn-chico">Rechazar</button>
                                    </form>
                                @elseif ($p->estado === 'aceptada')
                                    <a href="{{ route('constancia.postulacion', $p) }}" class="btn btn-borde btn-chico"><i class="bi bi-file-earmark-pdf"></i> Constancia</a>
                                @endif
                                <form action="{{ route('mensajes.iniciar') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="trabajo_id" value="{{ $p->trabajo_id }}">
                                    <input type="hidden" name="estudiante_id" value="{{ $p->usuario_id }}">
                                    <button type="submit" class="btn btn-borde btn-chico" title="Enviar mensaje"><i class="bi bi-chat-dots"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif
    </main>
@endsection
