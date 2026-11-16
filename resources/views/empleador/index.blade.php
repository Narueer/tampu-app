@extends('layouts.app')

@section('titulo', 'Mis ofertas')

@section('contenido')
    <main class="contenedor">
        @include('partials.subnav')

        <div class="cabecera-panel">
            <h1 class="titulo-pagina">Mis ofertas de trabajo</h1>
            @if (auth()->user()->esVerificado())
                <a href="{{ route('empleador.trabajos.create') }}" class="btn"><i class="bi bi-plus-lg"></i> Nueva oferta</a>
            @endif
        </div>

        @include('partials.aviso-verificacion')

        @if ($trabajos->isEmpty())
            <p class="vacio">Todavía no publicaste ofertas.</p>
        @else
            <div class="tabla-scroll">
                <table class="tabla">
                    <tr>
                        <th>Oferta</th>
                        <th>Horario</th>
                        <th>Pago</th>
                        <th>Estado</th>
                        <th>Postulantes</th>
                        <th>Acciones</th>
                    </tr>
                    @foreach ($trabajos as $t)
                        <tr>
                            <td><b>{{ $t->titulo }}</b><br><span class="texto-suave">{{ $t->empresa }} · {{ ucfirst($t->modalidad) }}</span></td>
                            <td>{{ $t->horarioTexto() }}</td>
                            <td>S/ {{ number_format($t->pago, 0) }} por {{ $t->pago_por === 'dia' ? 'día' : $t->pago_por }}</td>
                            <td>@include('partials.estado', ['estado' => $t->estado])</td>
                            <td>{{ $t->postulaciones_count }}</td>
                            <td class="acciones">
                                <a href="{{ route('empleador.trabajos.edit', $t) }}" class="btn btn-borde btn-chico">Editar</a>
                                <form action="{{ route('empleador.trabajos.destroy', $t) }}" method="POST" class="form-eliminar">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-peligro btn-chico">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif
    </main>
@endsection
