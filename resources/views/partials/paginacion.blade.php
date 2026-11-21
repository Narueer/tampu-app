{{-- paginacion con numeros; recibe $p (un paginador de Laravel) --}}
@if ($p->hasPages())
    <nav class="paginacion" aria-label="Páginas">
        @if ($p->onFirstPage())
            <span class="apagado" aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
        @else
            <a href="{{ $p->previousPageUrl() }}" rel="prev" aria-label="Página anterior"><i class="bi bi-chevron-left"></i></a>
        @endif

        @foreach ($p->getUrlRange(max(1, $p->currentPage() - 2), min($p->lastPage(), $p->currentPage() + 2)) as $numero => $url)
            @if ($numero == $p->currentPage())
                <span class="actual" aria-current="page">{{ $numero }}</span>
            @else
                <a href="{{ $url }}">{{ $numero }}</a>
            @endif
        @endforeach

        @if ($p->hasMorePages())
            <a href="{{ $p->nextPageUrl() }}" rel="next" aria-label="Página siguiente"><i class="bi bi-chevron-right"></i></a>
        @else
            <span class="apagado" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
        @endif
    </nav>
    <p class="paginacion-total">Mostrando {{ $p->firstItem() }}–{{ $p->lastItem() }} de {{ $p->total() }}</p>
@endif
