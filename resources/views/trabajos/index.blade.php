@extends('layouts.app')

@section('titulo', 'Trabajos')

@section('contenido')
    <main class="contenedor">
        <h1 class="titulo-pagina">Trabajos para estudiantes</h1>
        <p>Ofertas de medio tiempo, por horas y prácticas que puedes combinar con tus clases.</p>

        <form class="barra-filtros" action="{{ route('trabajos.index') }}" method="GET">
            <select name="modalidad" aria-label="Modalidad">
                <option value="">Todas las modalidades</option>
                <option value="medio tiempo" @selected(request('modalidad') === 'medio tiempo')>Medio tiempo</option>
                <option value="por horas" @selected(request('modalidad') === 'por horas')>Por horas</option>
                <option value="practicas" @selected(request('modalidad') === 'practicas')>Prácticas</option>
            </select>

            @if (auth()->check() && auth()->user()->rol === 'estudiante')
                @if ($clases->isNotEmpty())
                    <label class="checkbox"><input type="checkbox" name="compatible" value="1" @checked($compatible)> Solo compatibles con mi horario</label>
                @else
                    <span class="texto-suave">Registra tu <a href="{{ route('estudiante.panel') }}">horario de clases</a> para ver los compatibles.</span>
                @endif
            @else
                <span class="texto-suave"><a href="{{ route('login') }}">Inicia sesión</a> como estudiante para filtrar por tu horario.</span>
            @endif

            <button type="submit" class="btn">Filtrar</button>
        </form>

        <p id="total">{{ $trabajos->count() }} oferta(s){{ $compatible ? ' que no chocan con tus clases' : '' }}</p>

        @forelse ($trabajos as $t)
            @include('partials.trabajo')
        @empty
            <p class="vacio">No hay ofertas con esos filtros.</p>
        @endforelse
    </main>
@endsection
