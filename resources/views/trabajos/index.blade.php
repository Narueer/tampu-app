@extends('layouts.app')

@section('titulo', 'Trabajos')

@push('estilos')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('contenido')
    @include('partials.banner', ['titulo' => 'Trabajos para estudiantes', 'texto' => 'Ofertas de medio tiempo, por horas y prácticas que puedes combinar con tus clases.', 'imagen' => 'img/cusco/san-blas.jpg'])

    <main class="contenedor">

        <form class="barra-filtros" action="{{ route('trabajos.index') }}" method="GET">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar: barista, inglés, cocina..." aria-label="Buscar">
            <select name="distrito" aria-label="Distrito">
                <option value="">Todos los distritos</option>
                @foreach ($distritos as $d)
                    <option @selected(request('distrito') === $d)>{{ $d }}</option>
                @endforeach
            </select>
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

        <!-- chips de modalidad -->
        <div class="chips-filtro" aria-label="Modalidad">
            <a href="{{ route('trabajos.index', request()->except('modalidad')) }}" class="{{ request('modalidad') ? '' : 'activo' }}">Todas</a>
            @foreach (['medio tiempo' => 'Medio tiempo', 'por horas' => 'Por horas', 'practicas' => 'Prácticas'] as $valor => $nombre)
                <a href="{{ route('trabajos.index', array_merge(request()->except('modalidad'), ['modalidad' => $valor])) }}" class="{{ request('modalidad') === $valor ? 'activo' : '' }}">{{ $nombre }}</a>
            @endforeach
        </div>

        <p id="total">{{ $trabajos->count() }} oferta(s){{ $compatible ? ' que no chocan con tus clases' : '' }}</p>

        @if ($puntos->isNotEmpty())
            <div id="mapa" class="mapa mapa-trabajos" data-puntos='@json($puntos)'></div>
        @endif

        @forelse ($trabajos as $t)
            @include('partials.trabajo')
        @empty
            <p class="vacio">No hay ofertas con esos filtros.</p>
        @endforelse
    </main>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/mapa.js') }}?v={{ filemtime(public_path('js/mapa.js')) }}"></script>
@endpush
