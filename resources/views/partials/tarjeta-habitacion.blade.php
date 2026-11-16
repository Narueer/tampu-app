<div class="tarjeta">
    <div class="tarjeta-foto">
        <img src="{{ $h->fotoUrl() }}" alt="{{ $h->titulo }}">
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
        <span class="etiqueta">{{ ucfirst($h->tipo) }}</span>
        <h3>{{ $h->titulo }}</h3>
        <p><i class="bi bi-geo-alt"></i> {{ $h->distrito }}@if ($h->cerca_de) - cerca de {{ $h->cerca_de }}@endif</p>
        @if ($h->resenas_avg_estrellas)
            <p class="estrellas">@include('partials.estrellas', ['n' => $h->resenas_avg_estrellas]) <small>{{ number_format($h->resenas_avg_estrellas, 1) }}</small></p>
        @endif
        <p class="precio">S/ {{ number_format($h->precio, 0) }} <small>al mes</small></p>
        <a href="{{ route('habitaciones.show', $h) }}" class="btn btn-borde">Ver detalle</a>
    </div>
</div>
