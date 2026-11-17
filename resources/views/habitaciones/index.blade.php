@extends('layouts.app')

@section('titulo', request('distrito') ? 'Habitaciones en ' . request('distrito') . ', Cusco' : 'Habitaciones para estudiantes en Cusco')
@section('descripcion', 'Cuartos, habitaciones compartidas y minidepartamentos para estudiantes en ' . (request('distrito') ?: 'Cusco') . '. Arrendadores verificados, precios y mapa con la distancia a tu universidad.')

@push('estilos')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('contenido')
    @include('partials.banner', ['titulo' => 'Habitaciones para estudiantes', 'texto' => 'Cuartos verificados cerca de tu universidad. Filtra por distrito y precio, y mira dónde quedan en el mapa.', 'imagen' => 'img/cusco/plaza.jpg'])

    <main class="contenedor">

        <div class="con-filtros">
            <!-- filtros -->
            <form class="filtros" action="{{ route('habitaciones.index') }}" method="GET">
                <h3>Filtros</h3>
                <label for="distrito">Distrito</label>
                <select id="distrito" name="distrito">
                    <option value="">Todos</option>
                    @foreach ($distritos as $d)
                        <option @selected(request('distrito') === $d)>{{ $d }}</option>
                    @endforeach
                </select>

                <label for="precioMax">Precio máximo (S/)</label>
                <input type="number" id="precioMax" name="precio_max" value="{{ request('precio_max') }}" placeholder="Ej: 400" min="0">

                <label for="tipo">Tipo</label>
                <select id="tipo" name="tipo">
                    <option value="">Todos</option>
                    <option value="individual" @selected(request('tipo') === 'individual')>Individual</option>
                    <option value="compartida" @selected(request('tipo') === 'compartida')>Compartida</option>
                    <option value="minidepartamento" @selected(request('tipo') === 'minidepartamento')>Minidepartamento</option>
                </select>

                <label for="cerca">Cerca de</label>
                <input type="text" id="cerca" name="cerca_de" value="{{ request('cerca_de') }}" placeholder="Ej: UNSAAC" list="listaUniversidades">
                <datalist id="listaUniversidades">
                    @foreach (array_keys(\App\Support\Lugares::UNIVERSIDADES) as $u)
                        <option value="{{ $u }}">
                    @endforeach
                </datalist>

                <label for="orden">Ordenar por precio</label>
                <select id="orden" name="orden">
                    <option value="menor">Menor a mayor</option>
                    <option value="mayor" @selected(request('orden') === 'mayor')>Mayor a menor</option>
                </select>

                <button type="submit" class="btn">Filtrar</button>
                <a href="{{ route('habitaciones.index') }}" class="limpiar">Limpiar filtros</a>
            </form>

            <!-- lista -->
            <div class="resultados">
                <!-- chips para cambiar de distrito rapido -->
                <div class="chips-filtro" aria-label="Distritos">
                    <a href="{{ route('habitaciones.index', request()->except('distrito')) }}" class="{{ request('distrito') ? '' : 'activo' }}">Todos</a>
                    @foreach ($distritos as $d)
                        <a href="{{ route('habitaciones.index', array_merge(request()->except('distrito'), ['distrito' => $d])) }}" class="{{ request('distrito') === $d ? 'activo' : '' }}">{{ $d }}</a>
                    @endforeach
                </div>

                <div class="barra-resultados">
                    <p id="total">{{ $habitaciones->count() }} habitación(es) encontrada(s)</p>
                    <div class="cambiar-vista" role="group" aria-label="Forma de ver">
                        <button type="button" data-vista="cuadricula" aria-label="Ver en cuadrícula" title="Cuadrícula"><i class="bi bi-grid"></i></button>
                        <button type="button" data-vista="lista" aria-label="Ver en lista" title="Lista"><i class="bi bi-list-ul"></i></button>
                    </div>
                </div>
                @if ($puntos->isNotEmpty())
                    <div id="mapa" class="mapa" data-puntos='@json($puntos)'></div>
                @endif
                <div class="tarjetas">
                    @forelse ($habitaciones as $h)
                        @include('partials.tarjeta-habitacion')
                    @empty
                        <p class="vacio">No hay habitaciones con esos filtros. Prueba con otro distrito o un precio mayor.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/mapa.js') }}?v={{ filemtime(public_path('js/mapa.js')) }}"></script>
@endpush
