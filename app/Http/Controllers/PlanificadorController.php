<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

// "Mi planificador": lo que el estudiante anota en su calendario (exámenes, entregas, pagos, estudio)
class PlanificadorController extends Controller
{
    const MAXIMO = 300;

    public function guardar(Request $request)
    {
        $yo = $request->user();
        if ($yo->actividades()->count() >= self::MAXIMO) {
            return response()->json(['message' => 'Llegaste al máximo de ' . self::MAXIMO . ' actividades. Borra algunas que ya pasaron.'], 422);
        }
        $actividad = $yo->actividades()->create($this->validar($request));

        return response()->json(['ok' => true, 'id' => $actividad->id, 'mensaje' => 'Agregado a tu planificador.']);
    }

    public function actualizar(Request $request, Actividad $actividad)
    {
        $this->esMia($request, $actividad);
        $actividad->update($this->validar($request, $actividad));

        return response()->json(['ok' => true, 'mensaje' => 'Cambios guardados.']);
    }

    // arrastrar en el calendario: solo cambia el día y la hora
    public function mover(Request $request, Actividad $actividad)
    {
        $this->esMia($request, $actividad);
        $datos = $request->validate([
            'fecha' => ['required', 'date'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'date_format:H:i'],
        ]);
        abort_if($actividad->repetir !== 'no', 422, 'Las actividades que se repiten se cambian desde el formulario.');
        $actividad->update($datos);

        return response()->json(['ok' => true]);
    }

    public function marcar(Request $request, Actividad $actividad)
    {
        $this->esMia($request, $actividad);
        $actividad->update(['hecha' => ! $actividad->hecha]);

        return response()->json(['ok' => true, 'hecha' => $actividad->hecha]);
    }

    public function borrar(Request $request, Actividad $actividad)
    {
        $this->esMia($request, $actividad);
        $actividad->delete();

        return response()->json(['ok' => true, 'mensaje' => 'Eliminado.']);
    }

    // "Armar mi semana": pone bloques de estudio en los turnos libres de lo que queda de la semana.
    // Si hay un examen o una entrega, los bloques de antes llevan su nombre.
    public function armarSemana(Request $request)
    {
        $yo = $request->user();
        $cuantos = (int) $request->validate(['bloques' => ['required', 'integer', 'in:3,5,7']])['bloques'];
        $hoy = now('America/Lima');
        $inicio = $hoy->isSunday() ? $hoy->copy()->addDay()->startOfDay() : $hoy->copy()->startOfDay();
        $fin = $inicio->copy()->next(Carbon::SATURDAY)->endOfDay();
        if ($inicio->isSaturday()) {
            $fin = $inicio->copy()->endOfDay();
        }

        $nombres = [1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sabado'];
        $horas = ['tarde' => ['15:00', '17:00'], 'noche' => ['19:00', '21:00'], 'manana' => ['09:00', '11:00']];
        $ocupados = $yo->horarios->map(fn ($h) => $h->dia . '-' . $h->turno)->all();
        foreach ($yo->postulaciones()->where('estado', 'aceptada')->with('trabajo.horarios')->get() as $p) {
            foreach ($p->trabajo->horarios as $h) {
                $ocupados[] = $h->dia . '-' . $h->turno;
            }
        }
        $yaTiene = [];
        $metas = [];
        foreach ($yo->actividades as $a) {
            foreach ($a->fechasEntre($inicio, $fin->copy()->addDays(7)) as $dia) {
                if ($a->hora_inicio) {
                    $yaTiene[] = $dia->toDateString() . '-' . substr($a->hora_inicio, 0, 2);
                }
                if (in_array($a->tipo, ['examen', 'entrega'])) {
                    $metas[] = [$dia->copy(), $a->titulo];
                }
            }
        }
        usort($metas, fn ($x, $y) => $x[0] <=> $y[0]);

        // turnos libres de lo que queda de la semana (los de hoy, solo si todavía no pasaron)
        $libres = [];
        for ($dia = $inicio->copy(); $dia->lte($fin); $dia->addDay()) {
            if (! isset($nombres[$dia->dayOfWeekIso])) {
                continue;
            }
            foreach ($horas as $turno => [$desde, $hasta]) {
                $clave = $nombres[$dia->dayOfWeekIso] . '-' . $turno;
                $empieza = $dia->copy()->setTimeFromTimeString($desde);
                if (in_array($clave, $ocupados) || in_array($dia->toDateString() . '-' . substr($desde, 0, 2), $yaTiene) || $empieza->lt($hoy)) {
                    continue;
                }
                $libres[] = [$dia->copy(), $desde, $hasta, $turno];
            }
        }
        // primero tarde y noche, repartidos en días distintos
        usort($libres, fn ($x, $y) => [array_search($x[3], array_keys($horas)), $x[0]] <=> [array_search($y[3], array_keys($horas)), $y[0]]);
        $elegidos = [];
        $dias = [];
        foreach ($libres as $l) {
            if (count($elegidos) >= $cuantos) {
                break;
            }
            if (! in_array($l[0]->toDateString(), $dias) || count($dias) >= count(array_unique(array_map(fn ($x) => $x[0]->toDateString(), $libres)))) {
                $elegidos[] = $l;
                $dias[] = $l[0]->toDateString();
            }
        }
        foreach ($libres as $l) {   // si faltan, se completa con lo que quede
            if (count($elegidos) >= $cuantos) {
                break;
            }
            if (! in_array($l, $elegidos, true)) {
                $elegidos[] = $l;
            }
        }

        foreach ($elegidos as [$dia, $desde, $hasta]) {
            $meta = collect($metas)->first(fn ($m) => $m[0]->gt($dia));
            $yo->actividades()->create([
                'titulo' => $meta ? 'Estudiar para: ' . \Illuminate\Support\Str::limit($meta[1], 60, '') : 'Bloque de estudio',
                'tipo' => 'estudio', 'fecha' => $dia->toDateString(), 'hora_inicio' => $desde, 'hora_fin' => $hasta, 'repetir' => 'no',
                'nota' => 'Lo armó TAMPU en un turno libre. Cámbialo o muévelo si quieres.',
            ]);
        }

        return response()->json([
            'ok' => true,
            'creados' => count($elegidos),
            'mensaje' => count($elegidos) ? 'Listo: agregamos ' . count($elegidos) . ' bloques de estudio en tus turnos libres.' : 'No encontramos turnos libres esta semana. ¡Estás a full!',
        ]);
    }

    // lo que viene en los próximos 14 días, para la columna del planificador
    public function proximas(Request $request)
    {
        $hoy = now('America/Lima')->startOfDay();
        $fin = $hoy->copy()->addDays(13)->endOfDay();
        $lista = [];
        foreach ($request->user()->actividades()->where(fn ($q) => $q->where('fecha', '<=', $fin)->where(fn ($q) => $q->where('repetir', '!=', 'no')->orWhere('fecha', '>=', $hoy)))->get() as $a) {
            foreach ($a->fechasEntre($hoy, $fin) as $dia) {
                $lista[] = [
                    'id' => $a->id,
                    'titulo' => $a->titulo,
                    'tipo' => $a->tipo,
                    'icono' => $a->icono(),
                    'color' => $a->color(),
                    'fecha' => $dia->toDateString(),
                    'cuando' => $this->cuando($dia, $hoy),
                    'hora' => $a->hora_inicio ? substr($a->hora_inicio, 0, 5) : null,
                    'monto' => $a->monto ? 'S/ ' . number_format((float) $a->monto, 2) : null,
                    'hecha' => $a->repetir === 'no' && $a->hecha,
                    'repite' => $a->repetir !== 'no',
                    'urgente' => $dia->diffInDays($hoy, true) <= 1 && in_array($a->tipo, ['examen', 'entrega', 'pago']),
                ];
            }
        }
        usort($lista, fn ($x, $y) => [$x['fecha'], $x['hora'] ?? '00:00'] <=> [$y['fecha'], $y['hora'] ?? '00:00']);

        $semana = collect($lista)->filter(fn ($x) => $x['fecha'] <= $hoy->copy()->addDays(6)->toDateString());

        return response()->json([
            'lista' => array_slice($lista, 0, 25),
            'resumen' => [
                'examenes' => $semana->where('tipo', 'examen')->count(),
                'entregas' => $semana->where('tipo', 'entrega')->count(),
                'pagos' => 'S/ ' . number_format($semana->where('tipo', 'pago')->sum(fn ($x) => (float) str_replace(['S/ ', ','], '', $x['monto'] ?? '0')), 2),
            ],
        ]);
    }

    // archivo .ics para pasar el planificador a Google Calendar o al calendario del celular
    public function exportar(Request $request)
    {
        $yo = $request->user();
        $lineas = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//TAMPU//Planificador//ES', 'CALSCALE:GREGORIAN', 'X-WR-CALNAME:TAMPU - ' . $this->ics(explode(' ', $yo->nombre)[0]), 'X-WR-TIMEZONE:America/Lima'];
        foreach ($yo->actividades as $a) {
            $nombre = Actividad::TIPOS[$a->tipo][0] ?? 'Actividad';
            $lineas[] = 'BEGIN:VEVENT';
            $lineas[] = 'UID:tampu-' . $a->id . '@' . parse_url(config('app.url'), PHP_URL_HOST);
            $lineas[] = 'DTSTAMP:' . now('UTC')->format('Ymd\THis\Z');
            if ($a->hora_inicio) {
                $lineas[] = 'DTSTART;TZID=America/Lima:' . $a->fecha->format('Ymd') . 'T' . str_replace(':', '', substr($a->hora_inicio, 0, 5)) . '00';
                $lineas[] = 'DTEND;TZID=America/Lima:' . $a->fecha->format('Ymd') . 'T' . str_replace(':', '', substr($a->hora_fin ?: Carbon::parse($a->hora_inicio)->addHour()->format('H:i'), 0, 5)) . '00';
            } else {
                $lineas[] = 'DTSTART;VALUE=DATE:' . $a->fecha->format('Ymd');
                $lineas[] = 'DTEND;VALUE=DATE:' . $a->fecha->copy()->addDay()->format('Ymd');
            }
            if ($a->repetir !== 'no') {
                $lineas[] = 'RRULE:FREQ=' . ($a->repetir === 'semanal' ? 'WEEKLY' : 'MONTHLY') . ($a->hasta ? ';UNTIL=' . $a->hasta->format('Ymd') : '');
            }
            $lineas[] = 'SUMMARY:' . $this->ics($nombre . ': ' . $a->titulo . ($a->monto ? ' (S/ ' . number_format((float) $a->monto, 2) . ')' : ''));
            if ($a->nota) {
                $lineas[] = 'DESCRIPTION:' . $this->ics($a->nota);
            }
            // aviso un día antes para exámenes, entregas y pagos
            if (in_array($a->tipo, ['examen', 'entrega', 'pago'])) {
                array_push($lineas, 'BEGIN:VALARM', 'TRIGGER:-P1D', 'ACTION:DISPLAY', 'DESCRIPTION:' . $this->ics('Mañana: ' . $a->titulo), 'END:VALARM');
            }
            $lineas[] = 'END:VEVENT';
        }
        $lineas[] = 'END:VCALENDAR';

        return response(implode("\r\n", $lineas) . "\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="mi-planificador-tampu.ics"',
        ]);
    }

    private function validar(Request $request, ?Actividad $actual = null): array
    {
        $datos = $request->validate([
            'titulo' => ['required', 'string', 'max:80'],
            'tipo' => ['required', Rule::in(array_keys(Actividad::TIPOS))],
            'fecha' => ['required', 'date', 'after_or_equal:2020-01-01', 'before:2100-01-01'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'date_format:H:i', 'after:hora_inicio'],
            'repetir' => ['required', 'in:no,semanal,mensual'],
            'hasta' => ['nullable', 'date', 'after_or_equal:fecha'],
            'monto' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'nota' => ['nullable', 'string', 'max:300'],
        ], [
            'titulo.required' => 'Ponle un nombre (por ejemplo: Parcial de Cálculo).',
            'hora_fin.after' => 'La hora de fin debe ser después de la hora de inicio.',
            'hasta.after_or_equal' => 'La repetición debe terminar después de la primera fecha.',
        ]);
        if (! ($datos['hora_inicio'] ?? null)) {
            $datos['hora_fin'] = null;
        }
        if ($datos['tipo'] !== 'pago') {
            $datos['monto'] = null;
        }
        if ($datos['repetir'] === 'no') {
            $datos['hasta'] = null;
        }

        return $datos;
    }

    private function esMia(Request $request, Actividad $actividad): void
    {
        abort_unless($actividad->usuario_id === $request->user()->id, 403);
    }

    private function cuando(Carbon $dia, Carbon $hoy): string
    {
        $dias = (int) $hoy->diffInDays($dia, false);

        return match (true) {
            $dias === 0 => 'Hoy',
            $dias === 1 => 'Mañana',
            $dias < 7 => ucfirst($dia->translatedFormat('l')),
            default => ucfirst($dia->translatedFormat('D j M')),
        };
    }

    // texto seguro para el archivo .ics
    private function ics(string $texto): string
    {
        return str_replace(["\\", ';', ',', "\r\n", "\n"], ['\\\\', '\;', '\,', '\n', '\n'], $texto);
    }
}
