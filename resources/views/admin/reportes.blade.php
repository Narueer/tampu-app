@extends('layouts.app')

@section('titulo', 'Reportes')

@section('contenido')
    <main class="contenedor">
        @include('partials.subnav')

        <h1 class="titulo-pagina">Reportes de publicaciones</h1>

        <form class="barra-filtros filtros-admin" method="GET">
            <select name="estado" aria-label="Estado">
                @foreach (['pendiente' => 'Pendientes', 'sancionado' => 'Sancionados', 'descartado' => 'Descartados', 'todos' => 'Todos'] as $valor => $texto)
                    <option value="{{ $valor }}" @selected($estado === $valor)>{{ $texto }}</option>
                @endforeach
            </select>
            <select name="motivo" aria-label="Motivo">
                <option value="">Cualquier motivo</option>
                @foreach (\App\Models\Reporte::MOTIVOS as $m)
                    <option value="{{ $m }}" @selected(request('motivo') === $m)>{{ $m }}</option>
                @endforeach
            </select>
            @include('partials.rango-fechas')
            <button class="btn">Filtrar</button>
            @if (request()->query())<a href="{{ route('admin.reportes') }}" class="limpiar-filtros">Limpiar</a>@endif
        </form>
        <p class="conteo-resultados">{{ $reportes->count() }} {{ $reportes->count() === 1 ? 'reporte' : 'reportes' }}</p>

        @if ($reportes->isEmpty())
            <p class="vacio">No hay reportes {{ $estado === 'todos' ? '' : $estado . 's' }}{{ request('desde') || request('hasta') || request('motivo') ? ' con ese filtro' : '' }}.</p>
        @else
            <div class="tabla-scroll">
                <table class="tabla">
                    <tr>
                        <th>Publicación</th>
                        <th>Dueño</th>
                        <th>Motivo</th>
                        <th>Reportado por</th>
                        <th>Estado</th>
                        <th>Decisión</th>
                    </tr>
                    @foreach ($reportes as $r)
                        @php($dueno = $r->denunciado())
                        <tr>
                            <td>
                                @if ($r->habitacion)
                                    <i class="bi bi-house"></i> <a href="{{ route('habitaciones.show', $r->habitacion) }}" target="_blank">{{ $r->publicacion() }}</a>
                                @else
                                    <i class="bi bi-briefcase"></i> {{ $r->publicacion() }}
                                @endif
                            </td>
                            <td>{{ $dueno->nombre ?? '—' }}@if ($dueno && $dueno->suspendido)<br><span class="estado est-mal">Suspendido</span>@endif</td>
                            <td><b>{{ $r->motivo }}</b>@if ($r->detalle)<br><span class="texto-suave">{{ $r->detalle }}</span>@endif</td>
                            <td>{{ $r->autor->nombre }}<br><span class="texto-suave">{{ $r->created_at->format('d/m/Y') }}</span></td>
                            <td>@include('partials.estado', ['estado' => $r->estado])</td>
                            <td class="acciones">
                                @if ($r->estado === 'pendiente')
                                    <form action="{{ route('admin.reportes.resolver', $r) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button name="accion" value="sancionado" class="btn btn-peligro btn-chico">Suspender cuenta</button>
                                        <button name="accion" value="descartado" class="btn btn-borde btn-chico">Descartar</button>
                                    </form>
                                @else
                                    <span class="texto-suave">Resuelto</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif
    </main>
@endsection
