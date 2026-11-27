{{-- filtro "desde / hasta" con atajos (hoy, 7 días, 30 días). Va dentro de un <form method="GET"> --}}
@php
    $hoy = now()->toDateString();
    $atajos = ['Hoy' => [$hoy, $hoy], '7 días' => [now()->subDays(6)->toDateString(), $hoy], '30 días' => [now()->subDays(29)->toDateString(), $hoy]];
@endphp
<div class="rango-fechas">
    <label>Desde <input type="date" name="desde" value="{{ request('desde') }}" max="{{ $hoy }}"></label>
    <label>Hasta <input type="date" name="hasta" value="{{ request('hasta') }}" max="{{ $hoy }}"></label>
    <span class="rango-atajos">
        @foreach ($atajos as $texto => [$d, $h])
            <a href="{{ request()->fullUrlWithQuery(['desde' => $d, 'hasta' => $h, 'page' => null]) }}"
               class="{{ request('desde') === $d && request('hasta') === $h ? 'activo' : '' }}">{{ $texto }}</a>
        @endforeach
    </span>
</div>
