{{-- botones de Google y Microsoft; solo salen si estan configurados --}}
@php $externos = collect(config('tampu.oauth'))->filter(fn ($p) => $p['id']); @endphp
@if ($externos->isNotEmpty())
    <div class="externos">
        <span class="separador">o</span>
        @foreach ($externos as $clave => $p)
            <a href="{{ route('externo.ir', $clave) }}" class="btn-externo">
                <i class="bi bi-{{ $clave }}"></i> {{ $texto }} {{ $p['nombre'] }}
            </a>
        @endforeach
        <small class="ayuda">Si usas tu correo {{ '@' . config('tampu.dominio_institucional') }}, tu cuenta de estudiante queda verificada.</small>
    </div>
@endif
