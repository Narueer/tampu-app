@extends('layouts.app')

@section('titulo', 'Mensajes de contacto')

@section('contenido')
    <main class="contenedor">
        @include('partials.subnav')

        <h1 class="titulo-pagina">Mensajes de contacto</h1>

        <form class="barra-filtros filtros-admin" method="GET">
            <input type="search" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre, correo o texto del mensaje" aria-label="Buscar mensaje">
            <select name="asunto" aria-label="Asunto">
                <option value="">Cualquier asunto</option>
                @foreach (\App\Models\Contacto::ASUNTOS as $a)
                    <option value="{{ $a }}" @selected(request('asunto') === $a)>{{ $a }}</option>
                @endforeach
            </select>
            <select name="estado" aria-label="Estado">
                <option value="">Todos</option>
                <option value="nuevos" @selected(request('estado') === 'nuevos')>Sin leer</option>
                <option value="leidos" @selected(request('estado') === 'leidos')>Leídos</option>
            </select>
            @include('partials.rango-fechas')
            <button class="btn">Filtrar</button>
            @if (request()->query())<a href="{{ route('admin.contactos') }}" class="limpiar-filtros">Limpiar</a>@endif
        </form>
        <p class="conteo-resultados">{{ $contactos->count() }} {{ $contactos->count() === 1 ? 'mensaje' : 'mensajes' }}</p>

        @if ($contactos->isEmpty())
            <p class="vacio">{{ request()->query() ? 'No hay mensajes con ese filtro.' : 'Todavía no llegaron mensajes.' }}</p>
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
