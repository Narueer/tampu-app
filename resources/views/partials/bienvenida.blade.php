{{-- saludo arriba de cada panel --}}
@php($yo = auth()->user())
{{-- ilustración 3D según el rol (hechas en Blender, en public/img/3d) --}}
@php($ilustracion = ['estudiante' => 'cuarto', 'arrendador' => 'casa', 'empleador' => 'horario', 'admin' => 'casa'][$yo->rol] ?? null)
<div class="bienvenida {{ $ilustracion ? 'con-ilustracion' : '' }}">
    @if ($ilustracion)
        <img src="{{ asset("img/3d/$ilustracion.webp") }}" alt="" class="bienvenida-ilustracion" loading="lazy">
    @endif
    <div class="bienvenida-quien">
        @if ($yo->foto)
            <img src="{{ $yo->fotoUrl() }}" alt="" class="bienvenida-avatar">
        @else
            <span class="bienvenida-avatar">{{ $yo->iniciales() }}</span>
        @endif
        <div>
            <span class="bienvenida-fecha"><i class="bi bi-calendar3"></i> {{ ucfirst(now()->locale('es')->isoFormat('dddd D [de] MMMM')) }}</span>
            <h1>{{ $titulo ?? 'Hola, ' . explode(' ', $yo->nombre)[0] }}</h1>
            <p>{{ $subtitulo ?? '' }}</p>
        </div>
    </div>
    @if (! empty($acciones))
        <div class="bienvenida-acciones">
            @foreach ($acciones as [$url, $icono, $texto])
                <a href="{{ $url }}" class="btn btn-claro"><i class="bi bi-{{ $icono }}"></i> {{ $texto }}</a>
            @endforeach
        </div>
    @endif
</div>
