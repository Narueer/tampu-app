@extends('layouts.app')

@section('titulo', $habitacion->exists ? 'Editar habitación' : 'Nueva habitación')

@push('estilos')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('contenido')
    <main class="contenedor">
        <form class="formulario formulario-ancho" method="POST" enctype="multipart/form-data"
            action="{{ $habitacion->exists ? route('arrendador.habitaciones.update', $habitacion) : route('arrendador.habitaciones.store') }}">
            @csrf
            @if ($habitacion->exists)
                @method('PUT')
            @endif

            <h2>{{ $habitacion->exists ? 'Editar habitación' : 'Nueva habitación' }}</h2>
            @include('partials.errores')

            <label for="titulo">Título</label>
            <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $habitacion->titulo) }}" maxlength="120" required placeholder="Ej: Cuarto con baño propio cerca de la UNSAAC">

            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="4" maxlength="1000" required>{{ old('descripcion', $habitacion->descripcion) }}</textarea>

            <div class="fila">
                <div>
                    <label for="distrito">Distrito</label>
                    <select id="distrito" name="distrito" required>
                        @foreach ($distritos as $d)
                            <option @selected(old('distrito', $habitacion->distrito) === $d)>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo" required>
                        @foreach (['individual' => 'Individual', 'compartida' => 'Compartida', 'minidepartamento' => 'Minidepartamento'] as $valor => $texto)
                            <option value="{{ $valor }}" @selected(old('tipo', $habitacion->tipo) === $valor)>{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <label for="direccion">Dirección</label>
            <input type="text" id="direccion" name="direccion" value="{{ old('direccion', $habitacion->direccion) }}" maxlength="150" required>

            <div class="fila">
                <div>
                    <label for="cerca_de">Cerca de (universidad o instituto)</label>
                    <input type="text" id="cerca_de" name="cerca_de" value="{{ old('cerca_de', $habitacion->cerca_de) }}" maxlength="80" placeholder="Ej: UNSAAC">
                </div>
                <div>
                    <label for="precio">Precio al mes (S/)</label>
                    <input type="number" id="precio" name="precio" value="{{ old('precio', $habitacion->precio ? (int) $habitacion->precio : '') }}" min="50" max="5000" required>
                </div>
            </div>

            <div class="fila">
                <div>
                    <label for="metros">Tamaño (m²)</label>
                    <input type="number" id="metros" name="metros" value="{{ old('metros', $habitacion->metros) }}" min="4" max="200" placeholder="Ej: 14">
                </div>
                <div>
                    <label for="piso">Piso</label>
                    <input type="number" id="piso" name="piso" value="{{ old('piso', $habitacion->piso) }}" min="1" max="20" placeholder="Ej: 2">
                </div>
                <div>
                    <label for="bano">Baño</label>
                    <select id="bano" name="bano">
                        <option value="">Sin indicar</option>
                        <option value="propio" @selected(old('bano', $habitacion->bano) === 'propio')>Propio</option>
                        <option value="compartido" @selected(old('bano', $habitacion->bano) === 'compartido')>Compartido</option>
                    </select>
                </div>
                <div>
                    <label for="garantia">Garantía (S/)</label>
                    <input type="number" id="garantia" name="garantia" value="{{ old('garantia', $habitacion->garantia ? (int) $habitacion->garantia : '') }}" min="0" max="5000" placeholder="Ej: 300">
                </div>
            </div>

            <label>Ubicación en el mapa <span class="texto-suave">(haz clic donde está la habitación)</span></label>
            <div id="mapaElegir" class="mapa mapa-chico"></div>
            <input type="hidden" id="lat" name="lat" value="{{ old('lat', $habitacion->lat) }}">
            <input type="hidden" id="lng" name="lng" value="{{ old('lng', $habitacion->lng) }}">

            <label>Servicios</label>
            @php($actuales = old('servicios', $habitacion->listaServicios()))
            <div class="casillas">
                @foreach (['Agua y luz', 'Wifi', 'Baño propio', 'Amoblado', 'Cocina', 'Lavandería', 'Agua caliente', 'Escritorio', 'Cochera', 'Cable'] as $s)
                    <label class="checkbox"><input type="checkbox" name="servicios[]" value="{{ $s }}" @checked(in_array($s, $actuales))> {{ $s }}</label>
                @endforeach
            </div>

            <div class="fila">
                <div>
                    <label for="foto">Foto {{ $habitacion->exists ? '(déjalo vacío para mantener la actual)' : '' }}</label>
                    <input type="file" id="foto" name="foto" accept="image/*" {{ $habitacion->exists ? '' : 'required' }}>
                </div>
                <div>
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <option value="disponible" @selected(old('estado', $habitacion->estado) !== 'ocupada')>Disponible</option>
                        <option value="ocupada" @selected(old('estado', $habitacion->estado) === 'ocupada')>Ocupada</option>
                    </select>
                </div>
            </div>
            @if ($habitacion->foto)
                <img src="{{ $habitacion->fotoUrl() }}" alt="Foto actual" class="foto-actual" id="vistaPrevia">
            @else
                <img src="" alt="Vista previa" class="foto-actual oculto" id="vistaPrevia">
            @endif

            <!-- galeria -->
            <label for="galeria">Más fotos <span class="texto-suave">(baño, cocina, escritorio, fachada; hasta {{ \App\Http\Controllers\Arrendador\HabitacionController::MAX_GALERIA }} en total)</span></label>
            <input type="file" id="galeria" name="galeria[]" accept="image/*" multiple>
            @if ($habitacion->exists && $habitacion->fotos->isNotEmpty())
                <div class="galeria-editar">
                    @foreach ($habitacion->fotos as $f)
                        <label class="galeria-editar-foto">
                            <img src="{{ $f->url() }}" alt="{{ $f->descripcion }}">
                            <span><input type="checkbox" name="quitar[]" value="{{ $f->id }}"> Quitar</span>
                        </label>
                    @endforeach
                </div>
            @endif

            <button type="submit" class="btn">Guardar</button>
            <p><a href="{{ route('arrendador.habitaciones.index') }}">Cancelar</a></p>
        </form>
    </main>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/mapa.js') }}"></script>
@endpush
