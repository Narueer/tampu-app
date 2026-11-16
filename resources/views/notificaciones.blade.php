@extends('layouts.app')

@section('titulo', 'Notificaciones')

@section('contenido')
    <main class="contenedor">
        <div class="cabecera-panel">
            <h1 class="titulo-pagina">Notificaciones</h1>
            @if (auth()->user()->unreadNotifications()->exists())
                <form action="{{ route('notificaciones.leer') }}" method="POST">
                    @csrf
                    <button class="btn btn-borde btn-chico"><i class="bi bi-check2-all"></i> Marcar todas como leídas</button>
                </form>
            @endif
        </div>

        <div class="caja lista-avisos">
            @forelse ($notificaciones as $n)
                <a href="{{ route('notificaciones.abrir', $n->id) }}" class="aviso-item {{ $n->read_at ? '' : 'nuevo' }}">
                    <i class="bi bi-{{ $n->data['icono'] ?? 'bell' }}"></i>
                    <span>{{ $n->data['texto'] }}</span>
                    <small>{{ $n->created_at->diffForHumans() }}</small>
                </a>
            @empty
                <p class="vacio">No tienes notificaciones.</p>
            @endforelse
        </div>
    </main>
@endsection
