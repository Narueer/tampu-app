{{--
    Maqueta 3D que se gira con el mouse o el dedo (model-viewer). Mientras carga se ve la imagen (poster).
    Parámetros: modelo (.glb), poster (.webp), alt, puntos = [[posición "x y z", título, texto], ...]
--}}
<figure class="modelo-3d {{ $clase ?? '' }}" style="--poster: url('{{ asset($poster) }}')">
    <model-viewer
        src="{{ asset($modelo) }}"
        poster="{{ asset($poster) }}"
        alt="{{ $alt }}"
        loading="lazy"
        reveal="auto"
        camera-controls
        disable-zoom
        touch-action="pan-y"
        auto-rotate
        auto-rotate-delay="2500"
        rotation-per-second="14deg"
        interaction-prompt="none"
        camera-orbit="{{ $orbita ?? '35deg 62deg auto' }}"
        min-camera-orbit="auto 25deg auto"
        max-camera-orbit="auto 85deg auto"
        shadow-intensity="1.1"
        shadow-softness="0.8"
        exposure="1.05"
        tone-mapping="aces"
        ar
        ar-modes="webxr scene-viewer quick-look">
        @foreach ($puntos ?? [] as $i => [$pos, $titulo, $texto])
            <button class="punto-3d" slot="hotspot-{{ $i }}" data-position="{{ $pos }}" data-visibility-attribute="visible"
                    aria-label="{{ $titulo }}: {{ $texto }}">
                <span class="punto-3d-num">{{ $i + 1 }}</span>
                <span class="punto-3d-globo"><b>{{ $titulo }}</b>{{ $texto }}</span>
            </button>
        @endforeach
        <button slot="ar-button" class="boton-ar"><i class="bi bi-phone"></i> Ver en tu espacio</button>
        <div class="modelo-cargando" slot="progress-bar"><span></span></div>
    </model-viewer>
    {{-- aquí aparece la explicación del número que se toca --}}
    <div class="modelo-info" aria-live="polite" hidden></div>
    <span class="modelo-pista"><i class="bi bi-arrow-repeat"></i> Arrastra para girar · toca los números</span>
</figure>
