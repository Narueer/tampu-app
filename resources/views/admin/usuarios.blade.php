@extends('layouts.app')

@section('titulo', 'Usuarios')

@section('contenido')
    <main class="contenedor">
        @include('partials.subnav')

        <div class="cabecera-panel">
            <h1 class="titulo-pagina">Usuarios</h1>
        </div>

        <form class="barra-filtros filtros-admin" method="GET">
            <input type="search" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre, correo, DNI, celular o ciudad" aria-label="Buscar usuario" list="sugerenciasUsuarios" autocomplete="off">
            <datalist id="sugerenciasUsuarios">
                @foreach ($sugerencias as $s)
                    <option value="{{ $s->nombre }}">{{ $s->email }}</option>
                @endforeach
            </datalist>
            <select name="rol" aria-label="Rol">
                <option value="">Todos los roles</option>
                @foreach (['estudiante', 'arrendador', 'empleador'] as $r)
                    <option value="{{ $r }}" @selected(request('rol') === $r)>{{ ucfirst($r) }}</option>
                @endforeach
            </select>
            <select name="verificacion" aria-label="Verificación">
                <option value="">Cualquier verificación</option>
                @foreach (['verificado' => 'Verificados', 'pendiente' => 'Por verificar', 'rechazado' => 'Rechazados', 'no_aplica' => 'Estudiantes (no aplica)'] as $v => $t)
                    <option value="{{ $v }}" @selected(request('verificacion') === $v)>{{ $t }}</option>
                @endforeach
            </select>
            <select name="cuenta" aria-label="Cuenta">
                <option value="">Activas y suspendidas</option>
                <option value="activa" @selected(request('cuenta') === 'activa')>Solo activas</option>
                <option value="suspendida" @selected(request('cuenta') === 'suspendida')>Solo suspendidas</option>
            </select>
            <select name="orden" aria-label="Orden">
                <option value="">Más nuevos primero</option>
                <option value="antiguos" @selected(request('orden') === 'antiguos')>Más antiguos primero</option>
                <option value="nombre" @selected(request('orden') === 'nombre')>Por nombre (A-Z)</option>
            </select>
            @include('partials.rango-fechas')
            <button class="btn">Filtrar</button>
            @if (request()->query())<a href="{{ route('admin.usuarios') }}" class="limpiar-filtros">Limpiar</a>@endif
        </form>
        <p class="conteo-resultados">{{ $usuarios->count() }} {{ $usuarios->count() === 1 ? 'usuario' : 'usuarios' }}@if (request('desde') || request('hasta')) registrados en ese rango @endif</p>

        <div class="tabla-scroll">
            <table class="tabla">
                <tr>
                    <th>Nombre</th>
                    <th>Rol</th>
                    <th>Procedencia</th>
                    <th>Publicaciones</th>
                    <th>Verificación</th>
                    <th>Cuenta</th>
                </tr>
                @foreach ($usuarios as $u)
                    <tr>
                        <td>
                            <b>{{ $u->nombre }}</b> @include('partials.insignia-uni', ['usuario' => $u])<br>
                            <span class="texto-suave">{{ $u->email }}</span>
                            @if (! $u->correoConfirmado())<span class="texto-suave" title="Correo sin confirmar"><i class="bi bi-envelope-exclamation"></i></span>@endif
                            @if ($u->dos_pasos_activo)<span class="texto-suave" title="Verificación en dos pasos activa"><i class="bi bi-phone"></i></span>@endif
                        </td>
                        <td>{{ ucfirst($u->rol) }}</td>
                        <td>{{ $u->procedencia ?: '—' }}</td>
                        <td>{{ $u->habitaciones_count + $u->trabajos_count }}</td>
                        <td>@include('partials.estado', ['estado' => $u->verificacion])</td>
                        <td class="acciones">
                            <form action="{{ route('admin.suspender', $u) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                @if ($u->suspendido)
                                    <span class="estado est-mal">Suspendida</span>
                                    <button class="btn btn-borde btn-chico">Reactivar</button>
                                @else
                                    <button class="btn btn-borde btn-chico btn-alerta">Suspender</button>
                                @endif
                            </form>
                            @unless ($u->rol === 'admin' || $u->id === auth()->id())
                            <details class="eliminar-cuenta">
                                <summary class="btn btn-borde btn-chico btn-peligro"><i class="bi bi-trash"></i> Eliminar</summary>
                                <form action="{{ route('admin.usuarios.eliminar', $u) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <p>Se borra la cuenta con sus publicaciones, mensajes y solicitudes. Antes se guarda una copia de seguridad.</p>
                                    <label>Escribe <b>ELIMINAR</b> para confirmar
                                        <input name="confirmar" autocomplete="off" required pattern="ELIMINAR" aria-label="Escribe ELIMINAR">
                                    </label>
                                    <button class="btn btn-chico btn-peligro-lleno">Eliminar para siempre</button>
                                </form>
                            </details>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </main>
@endsection
