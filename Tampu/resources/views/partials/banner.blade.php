{{-- cabecera con foto de fondo para las paginas publicas --}}
<section class="banner" style="--fondo-img: url('{{ asset($imagen ?? 'img/cusco/catedral.jpg') }}')">
    <div class="contenedor">
        <nav class="migas" aria-label="Ubicación">
            <a href="{{ route('inicio') }}"><i class="bi bi-house"></i> Inicio</a>
            <i class="bi bi-chevron-right"></i>
            <span>{{ $titulo }}</span>
        </nav>
        <h1>{{ $titulo }}</h1>
        @isset($texto)<p>{{ $texto }}</p>@endisset
    </div>
</section>
