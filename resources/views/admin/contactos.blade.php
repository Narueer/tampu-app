@extends('layouts.app')

@section('titulo', 'Mensajes de contacto')

@section('contenido')
    <main class="contenedor">
        @include('partials.subnav')

        <h1 class="titulo-pagina">Mensajes de contacto</h1>

        @if ($contactos->isEmpty())
            <p class="vacio">Todavía no llegaron mensajes.</p>
        @else
            <div class="tabla-scroll">
                <table class="tabla">
                    <tr>
                        <th>De</th>
                        <th>Asunto</th>
                        <th>Mensaje</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                    </tr>
                    @foreach ($contactos as $c)
                        <tr class="{{ $c->leido ? '' : 'fila-nueva' }}">
                            <td><b>{{ $c->nombre }}</b><br><a href="mailto:{{ $c->email }}?subject=Re: {{ $c->asunto }}">{{ $c->email }}</a></td>
                            <td>{{ $c->asunto }}</td>
                            <td>{{ $c->mensaje }}</td>
                            <td>{{ $c->created_at->format('d/m/Y H:i') }}</td>
                            <td class="acciones">
                                <form action="{{ route('admin.contactos.leer', $c) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-borde btn-chico">{{ $c->leido ? 'Marcar no leído' : 'Marcar leído' }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif
    </main>
@endsection
