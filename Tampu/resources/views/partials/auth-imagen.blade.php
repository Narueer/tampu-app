{{-- panel con foto al lado del login y del registro --}}
<div class="auth-imagen" style="--fondo-img: url('{{ asset('img/cusco/cuesta-san-blas.jpg') }}')">
    <div class="auth-imagen-texto">
        <img src="{{ asset('img/logo-160.png') }}" alt="" class="auth-logo">
        <h2>{{ $titulo }}</h2>
        <p>{{ $texto }}</p>
    </div>
    <div class="globo-flotante auth-globo-1 flotar">
        <i class="bi bi-shield-lock-fill"></i>
        <div><b>Cuenta protegida</b><small>Verificación en dos pasos</small></div>
    </div>
    <div class="globo-flotante auth-globo-2 flotar-lento">
        <i class="bi bi-house-heart-fill"></i>
        <div><b>Visita antes de pagar</b><small>sin adelantos a ciegas</small></div>
    </div>
</div>
