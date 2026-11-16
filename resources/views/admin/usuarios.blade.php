@extends('layouts.app')

@section('titulo', 'Usuarios')

@section('contenido')
    <main class="contenedor">
        @include('partials.subnav')

        <div class="cabecera-panel">
            <h1 class="titulo-pagina">Usuarios</h1>
        </div>

        <form class="barra-filtros" method="GET">
            <select name="rol" aria-label="Rol">
                <option value="">Todos los roles</option>
                @foreach (['estudiante', 'arrendador', 'empleador'] as $r)
                    <option value="{{ $r }}" @selected(request('rol') === $r)>{{ ucfirst($r) }}</option>
                @endforeach
            </select>
            <button class="btn">Filtrar</button>
        </form>

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
                        <td><b>{{ $u->nombre }}</b><br><span class="texto-suave">{{ $u->email }}</span></td>
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
                                    <button class="btn btn-borde btn-chico">Suspender</button>
                                @endif
                            </form>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </main>
@endsection
