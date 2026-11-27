<div class="tarjeta">
    <div class="tarjeta-foto">
        <img src="{{ $h->fotoUrl() }}" alt="{{ $h->titulo }}" loading="lazy">
        <span class="precio-foto">S/ {{ number_format($h->precio, 0) }}<small>/mes</small></span>
        @if (($h->fotos_count ?? 0) > 0)
            <span class="cuenta-fotos"><i class="bi bi-images"></i> {{ $h->fotos_count + 1 }}</span>
        @endif
        @if (auth()->check() && auth()->user()->rol === 'estudiante')
            <form action="{{ route('estudiante.favorito', $h) }}" method="POST" class="form-favorito">
                @csrf
                @php($fav = in_array($h->id, $favoritos ?? []))
                <button type="submit" class="btn-corazon {{ $fav ? 'marcado' : '' }}" aria-label="Guardar en favoritos">
                    <i class="bi {{ $fav ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                </button>
            </form>
        @endif
    </div>
    <div class="info">
        <span class="etiqueta verificado">✔ Verificado</span>
        <span class="etiqueta etiqueta-tipo tipo-{{ $h->tipo }}"><i class="bi bi-{{ $h->icono() }}"></i> {{ $h->tipoTexto() }}</span>
        @if ($h->created_at && $h->created_at->gt(now()->subDays(2)))<span class="etiqueta etiqueta-nuevo">Nuevo</span>@endif
        <h3>{{ $h->titulo }}</h3>
        <p><i class="bi bi-geo-alt"></i> {{ $h->distrito }}@if ($h->cerca_de) - cerca de {{ $h->cerca_de }}@endif</p>
        @if ($h->metros || $h->bano)
            <p class="datos-tarjeta">
                @if ($h->metros)<span><i class="bi bi-arrows-angle-expand"></i> {{ $h->metros }} m²</span>@endif
                @if ($h->bano)<span><i class="bi bi-droplet"></i> Baño {{ $h->bano }}</span>@endif
                @if ($h->piso)<span><i class="bi bi-building"></i> {{ $h->piso }}° piso</span>@endif
            </p>
        @endif
        @if ($h->resenas_avg_estrellas)
            <p class="estrellas">@include('partials.estrellas', ['n' => $h->resenas_avg_estrellas]) <small>{{ number_format($h->resenas_avg_estrellas, 1) }}</small></p>
        @endif
        @if ($h->created_at)<p class="publicado"><i class="bi bi-clock-history"></i> Publicado {{ $h->created_at->locale('es')->diffForHumans() }}</p>@endif
        <a href="{{ route('habitaciones.show', $h) }}" class="btn btn-borde">Ver detalle</a>
    </div>
</div>
