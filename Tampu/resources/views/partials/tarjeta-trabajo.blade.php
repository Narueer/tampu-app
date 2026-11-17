{{-- tarjeta corta de una oferta (portada y "ofertas parecidas") --}}
<a href="{{ route('trabajos.show', $t) }}" class="tarjeta-trabajo">
    <span class="logo-empresa">{{ $t->inicialesEmpresa() }}</span>
    <span class="etiqueta">{{ ucfirst($t->modalidad) }}</span>
    <h3>{{ $t->titulo }}</h3>
    <p class="texto-suave">{{ $t->empresa }} · <i class="bi bi-geo-alt"></i> {{ $t->distrito }}</p>
    <p class="tarjeta-trabajo-horario"><i class="bi bi-clock"></i> {{ $t->horarioTexto() }}</p>
    <p class="precio">S/ {{ number_format($t->pago, 0) }} <small>por {{ $t->pago_por === 'dia' ? 'día' : $t->pago_por }}</small></p>
</a>
