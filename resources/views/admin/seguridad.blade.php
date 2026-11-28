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

        {{-- copias de seguridad --}}
        <section class="caja respaldos">
            <div class="respaldos-cabecera">
                <div>
                    <h2><i class="bi bi-cloud-arrow-up"></i> Copias de seguridad</h2>
                    <p class="texto-suave">Se crea una copia de toda la base de datos cada día (y antes de eliminar una cuenta). Se guardan las últimas {{ \App\Support\Respaldo::GUARDAR }} en la carpeta privada.</p>
                </div>
                <form action="{{ route('admin.respaldos.crear') }}" method="POST">
                    @csrf
                    <button class="btn" data-cargando="Creando…"><i class="bi bi-plus-lg"></i> Crear copia ahora</button>
                </form>
            </div>
            @if ($respaldos === null)
                <p class="alerta alerta-aviso">No se pudo leer la carpeta de copias. Revisa que <b>ARCHIVOS</b> y las claves de Supabase estén bien en Render.</p>
            @elseif (! $respaldos)
                <p class="texto-suave">Todavía no hay copias. Crea la primera con el botón.</p>
            @else
                <ul class="respaldos-lista">
                    @foreach (array_slice($respaldos, 0, 6) as $r)
                        <li>
                            <i class="bi bi-file-earmark-zip"></i>
                            <span>{{ \Illuminate\Support\Str::of($r['nombre'])->after('tampu-')->before('.json')->replaceMatches('/^(\d{4})-(\d{2})-(\d{2})-(\d{2})(\d{2})(\d{2})$/', '$3/$2/$1 $4:$5') }}</span>
                            <small>{{ number_format($r['peso'] / 1024, 0) }} KB</small>
                            <a href="{{ route('admin.respaldos.descargar', \Illuminate\Support\Str::before($r['nombre'], '.json')) }}" title="Descargar"><i class="bi bi-download"></i></a>
                            <details class="eliminar-cuenta">
                                <summary title="Volver a esta copia"><i class="bi bi-arrow-counterclockwise"></i> Volver</summary>
                                <form action="{{ route('admin.respaldos.restaurar', \Illuminate\Support\Str::before($r['nombre'], '.json')) }}" method="POST">
                                    @csrf
                                    <p><b>Volver los datos a esta fecha.</b> Lo que se hizo después se pierde (antes se guarda una copia de lo actual, por si acaso).</p>
                                    <label>Escribe <b>RESTAURAR</b>
                                        <input name="confirmar" autocomplete="off" required pattern="RESTAURAR">
                                    </label>
                                    <button class="btn btn-chico btn-peligro-lleno">Restaurar</button>
                                </form>
                            </details>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

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
