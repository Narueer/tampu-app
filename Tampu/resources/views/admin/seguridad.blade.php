@extends('layouts.app')

@section('titulo', 'Seguridad')

@section('contenido')
    <main class="contenedor">
        @include('partials.subnav')

        <h1 class="titulo-pagina">Bitácora de seguridad</h1>
        <p class="texto-suave">Aquí queda registrado quién entra, quién falla la contraseña, los bloqueos y los accesos a páginas sin permiso. Cuando alguien falla 5 veces seguidas te llega un aviso.</p>

        <div class="numeros">
            <div><i class="bi bi-box-arrow-in-right"></i><b>{{ $resumen['login'] }}</b><span>Inicios de sesión (24 h)</span></div>
            <div><i class="bi bi-x-octagon"></i><b>{{ $resumen['login_fallido'] }}</b><span>Contraseñas incorrectas (24 h)</span></div>
            <div><i class="bi bi-lock"></i><b>{{ $resumen['bloqueo'] }}</b><span>Bloqueos (24 h)</span></div>
            <div><i class="bi bi-sign-stop"></i><b>{{ $resumen['acceso_denegado'] }}</b><span>Accesos denegados (24 h)</span></div>
        </div>

        <form class="barra-filtros" method="GET">
            <select name="tipo" aria-label="Tipo de evento">
                <option value="">Todos los eventos</option>
                <option value="alertas" @selected($tipo === 'alertas')>Solo alertas</option>
                @foreach (\App\Models\EventoSeguridad::TIPOS as $valor => $t)
                    <option value="{{ $valor }}" @selected($tipo === $valor)>{{ $t[0] }}</option>
                @endforeach
            </select>
            <input type="search" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por correo" aria-label="Buscar por correo">
            <button class="btn">Filtrar</button>
        </form>

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
                            <td title="{{ $e->navegador }}">{{ $e->ip }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>

            @if ($eventos->hasPages())
                <div class="paginas">
                    @if ($eventos->previousPageUrl())<a class="btn btn-borde" href="{{ $eventos->previousPageUrl() }}">Anteriores</a>@endif
                    <span>Página {{ $eventos->currentPage() }} de {{ $eventos->lastPage() }}</span>
                    @if ($eventos->nextPageUrl())<a class="btn btn-borde" href="{{ $eventos->nextPageUrl() }}">Siguientes</a>@endif
                </div>
            @endif
        @endif
    </main>
@endsection
