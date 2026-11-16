@if (auth()->user()->rol !== 'admin' && auth()->id() !== $dueno)
    <details class="desplegable reportar">
        <summary class="enlace"><i class="bi bi-flag"></i> Reportar</summary>
        <form action="{{ route('reportes.store') }}" method="POST">
            @csrf
            <input type="hidden" name="{{ $campo }}" value="{{ $id }}">
            <label>Motivo</label>
            <select name="motivo" required>
                @foreach (\App\Models\Reporte::MOTIVOS as $m)
                    <option>{{ $m }}</option>
                @endforeach
            </select>
            <textarea name="detalle" rows="2" maxlength="500" placeholder="Cuéntanos qué pasó (opcional)"></textarea>
            <button type="submit" class="btn btn-peligro btn-chico">Enviar reporte</button>
        </form>
    </details>
@endif
