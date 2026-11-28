@extends('layouts.app')

@section('titulo', 'Constancias emitidas')

@section('contenido')
    <main class="contenedor">
        @include('partials.subnav')

        <div class="cabecera-panel">
            <h1 class="titulo-pagina">Constancias emitidas</h1>
        </div>
        <p class="texto-suave">Cada constancia tiene un código único y un QR. Si anulas una, quien escanee su QR verá que ya no es válida. Solo tú, como administrador, puedes anularlas o eliminarlas.</p>

        <form class="barra-filtros filtros-admin" method="GET">
            <input type="search" name="buscar" value="{{ request('buscar') }}" placeholder="Código o nombre del estudiante" aria-label="Buscar">
            <select name="estado" aria-label="Estado">
                <option value="">Todas</option>
                <option value="vigentes" @selected(request('estado') === 'vigentes')>Vigentes</option>
                <option value="anuladas" @selected(request('estado') === 'anuladas')>Anuladas</option>
            </select>
            @include('partials.rango-fechas')
            <button class="btn">Filtrar</button>
            @if (request()->query())<a href="{{ route('admin.documentos') }}" class="limpiar-filtros">Limpiar</a>@endif
        </form>
        <p class="conteo-resultados">{{ $documentos->total() }} {{ $documentos->total() === 1 ? 'constancia' : 'constancias' }}</p>

        @if ($documentos->isEmpty())
            <p class="vacio">Todavía no se emitió ninguna constancia con ese filtro.</p>
        @else
            <div class="tabla-scroll">
                <table class="tabla">
                    <tr>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Estudiante</th>
                        <th>Sobre</th>
                        <th>Emitida</th>
                        <th title="Descargas / veces que escanearon el QR">Uso</th>
                        <th>Estado</th>
                    </tr>
                    @foreach ($documentos as $d)
                        <tr class="{{ $d->revocado_at ? 'fila-alerta' : '' }}">
                            <td><a href="{{ $d->url() }}" target="_blank" class="mono">{{ $d->codigo }}</a></td>
                            <td>{{ $d->tipo === 'visita' ? 'Visita' : 'Postulación' }}</td>
                            <td>{{ $d->datos['estudiante'] ?? ($d->usuario->nombre ?? '—') }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($d->datos['que'] ?? '—', 40) }}</td>
                            <td>{{ $d->created_at->timezone('America/Lima')->format('d/m/Y H:i') }}</td>
                            <td><i class="bi bi-download"></i> {{ $d->descargas }} · <i class="bi bi-qr-code-scan"></i> {{ $d->verificaciones }}</td>
                            <td class="acciones">
                                @if ($d->revocado_at)
                                    <span class="estado est-mal" title="{{ $d->motivo_revocacion }}">Anulada</span>
                                    <form action="{{ route('admin.documentos.anular', $d) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-borde btn-chico">Reactivar</button>
                                    </form>
                                @else
                                    <details class="eliminar-cuenta">
                                        <summary class="btn btn-borde btn-chico btn-alerta">Anular</summary>
                                        <form action="{{ route('admin.documentos.anular', $d) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <label>Motivo <input name="motivo" maxlength="200" placeholder="Ej: la visita se canceló"></label>
                                            <button class="btn btn-chico btn-peligro-lleno">Anular constancia</button>
                                        </form>
                                    </details>
                                @endif
                                <form action="{{ route('admin.documentos.eliminar', $d) }}" method="POST" onsubmit="return confirm('¿Eliminar la constancia {{ $d->codigo }}? Su QR dejará de funcionar.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-borde btn-chico btn-peligro" title="Eliminar"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
            @include('partials.paginacion', ['p' => $documentos])
        @endif
    </main>
@endsection
