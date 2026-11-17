{{-- tarjetas con numeros del panel: [icono, numero, texto, color] y opcional un enlace --}}
<div class="kpis">
    @foreach ($kpis as $k)
        @php([$icono, $numero, $texto, $color] = $k)
        @php($enlace = $k[4] ?? null)
        @if ($enlace)
            <a href="{{ $enlace }}" class="kpi kpi-{{ $color }}">
        @else
            <div class="kpi kpi-{{ $color }}">
        @endif
            <span class="kpi-icono"><i class="bi bi-{{ $icono }}"></i></span>
            <span class="kpi-datos">
                <b>{{ $numero }}</b>
                <span>{{ $texto }}</span>
            </span>
        @if ($enlace)
            </a>
        @else
            </div>
        @endif
    @endforeach
</div>
