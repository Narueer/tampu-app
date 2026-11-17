@extends('layouts.app')

@section('titulo', 'Solicitudes de visita')

@section('contenido')
    <main class="contenedor">
        @include('partials.subnav')

        <div class="cabecera-panel">
            <h1 class="titulo-pagina">Solicitudes de visita</h1>
        </div>

        @if ($solicitudes->isEmpty())
            <p class="vacio">Aún no tienes solicitudes.</p>
        @else
            <div class="tabla-scroll">
                <table class="tabla">
                    <tr>
                        <th>Estudiante</th>
                        <th>Habitación</th>
                        <th>Fecha</th>
                        <th>Mensaje</th>
                        <th>Estado</th>
                        <th>Responder</th>
                    </tr>
                    @foreach ($solicitudes as $s)
                        <tr>
                            <td><b>{{ $s->estudiante->nombre }}</b> @include('partials.insignia-uni', ['usuario' => $s->estudiante])<br><span class="texto-suave">De {{ $s->estudiante->procedencia ?: '—' }}</span></td>
                            <td>{{ $s->habitacion->titulo }}</td>
                            <td>{{ $s->fecha_visita->format('d/m/Y') }}</td>
                            <td>{{ $s->mensaje }}</td>
                            <td>@include('partials.estado', ['estado' => $s->estado])</td>
                            <td class="acciones">
                                @if ($s->estado === 'pendiente')
                                    <form action="{{ route('arrendador.solicitudes.responder', $s) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button name="estado" value="aceptada" class="btn btn-chico">Aceptar</button>
                                        <button name="estado" value="rechazada" class="btn btn-borde btn-chico">Rechazar</button>
                                    </form>
                                @elseif ($s->estado === 'aceptada')
                                    <a href="{{ route('constancia.visita', $s) }}" class="btn btn-borde btn-chico"><i class="bi bi-file-earmark-pdf"></i> Constancia</a>
                                @else
                                    <span class="texto-suave">Respondida</span>
                                @endif
                                <form action="{{ route('mensajes.iniciar') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="habitacion_id" value="{{ $s->habitacion_id }}">
                                    <input type="hidden" name="estudiante_id" value="{{ $s->usuario_id }}">
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
