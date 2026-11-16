<div class="tabla-scroll">
    <table class="horario">
        <tr>
            <th></th>
            @foreach (\App\Models\Horario::DIAS as $corto)
                <th>{{ $corto }}</th>
            @endforeach
        </tr>
        @foreach (\App\Models\Horario::TURNOS as $turno => $nombreTurno)
            <tr>
                <th>{{ ucfirst($nombreTurno) }}</th>
                @foreach (\App\Models\Horario::DIAS as $dia => $corto)
                    @php($valor = $dia . '-' . $turno)
                    <td>
                        <input type="checkbox" name="turnos[]" value="{{ $valor }}"
                            aria-label="{{ $corto }} {{ $nombreTurno }}"
                            @checked(in_array($valor, old('turnos', $marcados)))>
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>
</div>
