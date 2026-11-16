<div class="trabajo" id="trabajo-{{ $t->id }}">
    <div>
        <span class="etiqueta verificado">✔ Verificado</span> <span class="etiqueta">{{ ucfirst($t->modalidad) }}</span>
        <h3>{{ $t->titulo }}</h3>
        <p>{{ $t->empresa }} - <i class="bi bi-geo-alt"></i> {{ $t->distrito }}</p>
        <p class="texto-suave">{{ $t->descripcion }}</p>
        <p><i class="bi bi-clock"></i> {{ $t->horarioTexto() }}</p>
    </div>
    <div class="trabajo-pago">
        <p class="precio">S/ {{ number_format($t->pago, 0) }} <small>por {{ $t->pago_por === 'dia' ? 'día' : $t->pago_por }}</small></p>

        @if (isset($postulados))
            @auth
                @if (auth()->user()->rol === 'estudiante')
                    @if (isset($postulados[$t->id]))
                        <p>Tu postulación: @include('partials.estado', ['estado' => $postulados[$t->id]])</p>
                    @else
                        <details class="desplegable">
                            <summary class="btn btn-chico">Postular</summary>
                            <form action="{{ route('estudiante.postular', $t) }}" method="POST">
                                @csrf
                                <textarea name="mensaje" rows="2" maxlength="300" placeholder="Cuéntale a la empresa por qué te interesa (opcional)"></textarea>
                                <button type="submit" class="btn btn-chico">Enviar postulación</button>
                            </form>
                        </details>
                    @endif
                    <form action="{{ route('mensajes.iniciar') }}" method="POST">
                        @csrf
                        <input type="hidden" name="trabajo_id" value="{{ $t->id }}">
                        <button type="submit" class="enlace"><i class="bi bi-chat-dots"></i> Enviar mensaje</button>
                    </form>
                @endif
                @include('partials.reportar', ['campo' => 'trabajo_id', 'id' => $t->id, 'dueno' => $t->usuario_id])
            @else
                <a href="{{ route('login') }}" class="btn btn-borde btn-chico">Inicia sesión para postular</a>
            @endauth
        @endif
    </div>
</div>
