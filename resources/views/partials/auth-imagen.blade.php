{{-- panel con foto al lado del login, registro y pantallas de cuenta --}}
@php
    $globos = $globos ?? [
        ['bi-shield-lock-fill', 'Cuenta protegida', 'Verificación en dos pasos'],
        ['bi-house-heart-fill', 'Visita antes de pagar', 'sin adelantos a ciegas'],
    ];
@endphp
<div class="auth-imagen" style="--fondo-img: url('{{ asset('img/cusco/' . ($imagen ?? 'cuesta-san-blas.jpg')) }}')">
    <div class="auth-imagen-texto">
        <img src="{{ asset('img/logo-160.png') }}" alt="" class="auth-logo">
        <h2>{!! $titulo !!}</h2>
        <p>{{ $texto }}</p>
    </div>
    @foreach ($globos as $i => [$icono, $grande, $chico])
        <div class="globo-flotante auth-globo-{{ $i + 1 }} {{ $i ? 'flotar-lento' : 'flotar' }}">
            <i class="bi {{ $icono }}"></i>
            <div><b>{{ $grande }}</b><small>{{ $chico }}</small></div>
        </div>
    @endforeach
</div>
