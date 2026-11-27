@extends('layouts.app')

@section('titulo', 'Mensajes')

@section('contenido')
    <main class="contenedor">
        <h1 class="titulo-pagina">Mensajes</h1>

        <div class="chat {{ $actual ? 'con-chat' : '' }}">
            <aside class="chat-lista">
                @forelse ($conversaciones as $c)
                    @php($otro = $c->otro(auth()->user()))
                    <a href="{{ route('mensajes.ver', $c) }}" class="chat-item {{ $actual && $actual->id === $c->id ? 'activo' : '' }}">
                        @if ($otro->foto)
                            <img src="{{ $otro->fotoUrl() }}" alt="" class="avatar">
                        @else
                            <span class="avatar avatar-letras">{{ $otro->iniciales() }}</span>
                        @endif
                        <span class="chat-texto">
                            <b>{{ $otro->nombre }}</b>
                            <small>{{ $c->tema() }}</small>
                            <small class="texto-suave">{{ $c->ultimo ? \Illuminate\Support\Str::limit($c->ultimo->texto, 35) : 'Sin mensajes aún' }}</small>
                        </span>
                        @if ($c->sin_leer)<span class="globo">{{ $c->sin_leer }}</span>@endif
                    </a>
                @empty
                    <p class="vacio">No tienes conversaciones. Puedes escribir desde una habitación o una oferta de trabajo.</p>
                @endforelse
            </aside>

            <section class="chat-ventana">
                @if ($actual)
                    @php($otro = $actual->otro(auth()->user()))
                    <div class="chat-cabecera">
                        <a href="{{ route('mensajes') }}" class="solo-celular"><i class="bi bi-arrow-left"></i></a>
                        <b>{{ $otro->nombre }}</b>
                        <span class="texto-suave">· {{ $actual->tema() }}</span>
                    </div>
                    <div id="chatMensajes" class="chat-mensajes" data-nuevos="{{ route('mensajes.nuevos', $actual) }}">
                        @foreach ($mensajes as $m)
                            <div class="burbuja {{ $m->usuario_id === auth()->id() ? 'mia' : '' }}" data-id="{{ $m->id }}">
                                {{ $m->texto }}
                                <small>{{ $m->created_at->format('d/m H:i') }}</small>
                            </div>
                        @endforeach
                    </div>
                    @if ($rapidos)
                        <div class="chat-rapidos" id="chatRapidos" aria-label="Mensajes rápidos">
                            @foreach ($rapidos as $r)
                                <button type="button" class="chip-rapido" data-texto="{{ $r }}">{{ $r }}</button>
                            @endforeach
                        </div>
                    @endif
                    <form id="chatForm" class="chat-form" action="{{ route('mensajes.enviar', $actual) }}" method="POST">
                        @csrf
                        <input type="text" name="texto" id="chatTexto" placeholder="Escribe un mensaje..." maxlength="1000" autocomplete="off" required>
                        <button type="submit" class="btn"><i class="bi bi-send"></i></button>
                    </form>
                @else
                    <p class="vacio chat-vacio"><i class="bi bi-chat-dots"></i><br>Elige una conversación.</p>
                @endif
            </section>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/chat.js') }}?v={{ filemtime(public_path('js/chat.js')) }}"></script>
@endpush
