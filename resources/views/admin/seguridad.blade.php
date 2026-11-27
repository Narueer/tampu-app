@extends('layouts.app')

@section('titulo', 'Seguridad')

@section('contenido')
    <main class="contenedor">
        @include('partials.subnav')

        <h1 class="titulo-pagina">Bitácora de seguridad</h1>
        <p class="texto-suave">Aquí queda registrado quién entra, quién falla la contraseña, los bloqueos y los accesos a páginas sin permiso. Cuando alguien falla 5 veces seguidas te llega un aviso.</p>

        @include('partials.kpis', ['kpis' => [
            ['box-arrow-in-right', $resumen['login'], 'Inicios de sesión (24 h)', 'verde', route('admin.seguridad', ['tipo' => 'login'])],
            ['x-octagon', $resumen['login_fallido'], 'Contraseñas incorrectas (24 h)', 'amarillo', route('admin.seguridad', ['tipo' => 'login_fallido'])],
            ['lock', $resumen['bloqueo'], 'Bloqueos (24 h)', 'rosa', route('admin.seguridad', ['tipo' => 'bloqueo'])],
            ['sign-stop', $resumen['acceso_denegado'], 'Accesos denegados (24 h)', 'terracota', route('admin.seguridad', ['tipo' => 'acceso_denegado'])],
        ]])

        <form class="barra-filtros filtros-admin" method="GET">
            <select name="tipo" aria-label="Tipo de evento">
                <option value="">Todos los eventos</option>
                <option value="alertas" @selected($tipo === 'alertas')>Solo alertas</option>
                @foreach (\App\Models\EventoSeguridad::TIPOS as $valor => $t)
                    <option value="{{ $valor }}" @selected($tipo === $valor)>{{ $t[0] }}</option>
                @endforeach
            </select>
            <input type="search" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por correo" aria-label="Buscar por correo" list="sugerenciasCorreos" autocomplete="off">
            <datalist id="sugerenciasCorreos">
                @foreach ($correos as $c)<option value="{{ $c }}">@endforeach
            </datalist>
            <input type="search" name="ip" value="{{ request('ip') }}" placeholder="IP" aria-label="Buscar por IP" class="campo-ip">
            @include('partials.rango-fechas')
            <button class="btn">Filtrar</button>
            @if (request()->query())<a href="{{ route('admin.seguridad') }}" class="limpiar-filtros">Limpiar</a>@endif
        </form>
        <p class="conteo-resultados">{{ $eventos->total() }} {{ $eventos->total() === 1 ? 'evento' : 'eventos' }}</p>

        @if ($eventos->isEmpty())
            <p class="vacio">No hay eventos con ese filtro.</p>
        @else
            <div class="tabla-scroll">
                <table class="tabla">
                    <tr>
                        <th>Fecha</th>
                        <th>Evento</th>
                        <th>Cuenta</th>
                        <th>Detalle</th>
                        <th>IP</th>
                    </tr>
                    @foreach ($eventos as $e)
                        <tr class="{{ $e->esAlerta() ? 'fila-alerta' : '' }}">
                            <td>{{ $e->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>
                                @if ($e->esAlerta())<i class="bi bi-exclamation-triangle"></i>@endif
                                {{ $e->nombreTipo() }}
                            </td>
                            <td>
                                @if ($e->usuario)
                                    <b>{{ $e->usuario->nombre }}</b><br><span class="texto-suave">{{ $e->usuario->rol }}</span>
                                @else
                                    {{ $e->email ?? '—' }}
                                @endif
                            </td>
                            <td>{{ $e->detalle }}</td>
                            <td title="{{ $e->navegador }}"><a href="{{ request()->fullUrlWithQuery(['ip' => $e->ip, 'page' => null]) }}" class="enlace-filtro" title="Ver todo lo de esta IP">{{ $e->ip }}</a></td>
                        </tr>
                    @endforeach
                </table>
            </div>

            @include('partials.paginacion', ['p' => $eventos])
        @endif
    </main>
@endsection
