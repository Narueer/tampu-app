@extends('layouts.app')

@section('titulo', $trabajo->exists ? 'Editar oferta' : 'Nueva oferta')

@section('contenido')
    <main class="contenedor">
        <form class="formulario formulario-ancho" method="POST"
            action="{{ $trabajo->exists ? route('empleador.trabajos.update', $trabajo) : route('empleador.trabajos.store') }}">
            @csrf
            @if ($trabajo->exists)
                @method('PUT')
            @endif

            <h2>{{ $trabajo->exists ? 'Editar oferta' : 'Nueva oferta de trabajo' }}</h2>
            @include('partials.errores')

            <label for="titulo">Puesto</label>
            <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $trabajo->titulo) }}" maxlength="120" required placeholder="Ej: Mozo/a fin de semana">

            <div class="fila">
                <div>
                    <label for="empresa">Empresa o negocio</label>
                    <input type="text" id="empresa" name="empresa" value="{{ old('empresa', $trabajo->empresa) }}" maxlength="100" required>
                </div>
                <div>
                    <label for="distrito">Distrito</label>
                    <select id="distrito" name="distrito">
                        @foreach ($distritos as $d)
                            <option @selected(old('distrito', $trabajo->distrito) === $d)>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="3" maxlength="1000" required>{{ old('descripcion', $trabajo->descripcion) }}</textarea>

            <div class="fila">
                <div>
                    <label for="modalidad">Modalidad</label>
                    <select id="modalidad" name="modalidad">
                        @foreach (['medio tiempo' => 'Medio tiempo', 'por horas' => 'Por horas', 'practicas' => 'Prácticas'] as $valor => $texto)
                            <option value="{{ $valor }}" @selected(old('modalidad', $trabajo->modalidad) === $valor)>{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <option value="activa" @selected(old('estado', $trabajo->estado) !== 'cerrada')>Activa</option>
                        <option value="cerrada" @selected(old('estado', $trabajo->estado) === 'cerrada')>Cerrada</option>
                    </select>
                </div>
            </div>

            <div class="fila">
                <div>
                    <label for="pago">Pago (S/)</label>
                    <input type="number" id="pago" name="pago" value="{{ old('pago', $trabajo->pago ? (int) $trabajo->pago : '') }}" min="1" required>
                </div>
                <div>
                    <label for="pago_por">Por</label>
                    <select id="pago_por" name="pago_por">
                        @foreach (['hora' => 'Hora', 'dia' => 'Día', 'mes' => 'Mes'] as $valor => $texto)
                            <option value="{{ $valor }}" @selected(old('pago_por', $trabajo->pago_por) === $valor)>{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <label>Turnos de trabajo</label>
            @include('partials.horario', ['marcados' => $marcados])

            <button type="submit" class="btn">Guardar</button>
            <p><a href="{{ route('empleador.trabajos.index') }}">Cancelar</a></p>
        </form>
    </main>
@endsection
