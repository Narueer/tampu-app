<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Solicitud;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class CalendarioController extends Controller
{
    const DIA_NUMERO = [
        'domingo' => 0, 'lunes' => 1, 'martes' => 2, 'miercoles' => 3,
        'jueves' => 4, 'viernes' => 5, 'sabado' => 6,
    ];

    const HORAS = [
        'manana' => ['08:00', '12:00'],
        'tarde' => ['14:00', '18:00'],
        'noche' => ['19:00', '22:00'],
    ];

    public function index(Request $request)
    {
        abort_if($request->user()->rol === 'admin', 404);

        $yo = $request->user();
        $cuarto = $yo->rol === 'estudiante' ? (float) ($yo->presupuesto['cuarto'] ?? 0) : 0;
        $tieneAlquiler = $cuarto > 0 && $yo->actividades()->where('tipo', 'pago')->where('repetir', 'mensual')->exists();

        return view('calendario', [
            'tipos' => Actividad::TIPOS,
            'cuarto' => $cuarto > 0 && ! $tieneAlquiler ? $cuarto : null,
        ]);
    }

    // eventos en el formato de FullCalendar
    public function eventos(Request $request)
    {
        $yo = $request->user();
        $eventos = [];

        if ($yo->rol === 'estudiante') {
            foreach ($yo->horarios as $h) {
                $eventos[] = $this->semanal('Clases', $h->dia, $h->turno, '#34483e') + ['classNames' => ['ev-clases']];
            }

            $aceptadas = $yo->postulaciones()->where('estado', 'aceptada')->with('trabajo.horarios')->get();
            foreach ($aceptadas as $p) {
                foreach ($p->trabajo->horarios as $h) {
                    $eventos[] = $this->semanal('Trabajo: ' . $p->trabajo->titulo, $h->dia, $h->turno, '#a66d53') + ['classNames' => ['ev-trabajo']];
                }
            }

            foreach ($yo->solicitudes()->with('habitacion')->whereIn('estado', ['pendiente', 'aceptada'])->get() as $s) {
                $eventos[] = $this->visita($s, 'Visita: ' . $s->habitacion->titulo, route('habitaciones.show', $s->habitacion));
            }

            // lo que anotó en su planificador, con las repeticiones dentro del rango que se está viendo
            $desde = Carbon::parse($request->query('start', now()->startOfMonth()->subWeek()))->startOfDay();
            $hasta = Carbon::parse($request->query('end', now()->endOfMonth()->addWeek()))->endOfDay();
            foreach ($yo->actividades()->where('fecha', '<=', $hasta)->get() as $a) {
                foreach ($a->fechasEntre($desde, $hasta) as $dia) {
                    $eventos[] = $this->actividad($a, $dia);
                }
            }

            // turnos sin clases ni trabajo: buenos para estudiar
            $ocupados = $yo->horarios->map(fn ($h) => $h->dia . '-' . $h->turno)->all();
            foreach ($aceptadas as $p) {
                foreach ($p->trabajo->horarios as $h) {
                    $ocupados[] = $h->dia . '-' . $h->turno;
                }
            }
            if ($yo->horarios->isNotEmpty()) {
                foreach (['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'] as $dia) {
                    foreach (self::HORAS as $turno => $horas) {
                        if (! in_array("$dia-$turno", $ocupados)) {
                            $eventos[] = $this->semanal('Libre para estudiar', $dia, $turno, '#879484') + ['display' => 'background', 'classNames' => ['ev-libre'], 'groupId' => 'libres'];
                        }
                    }
                }
            }
        }

        if ($yo->rol === 'arrendador') {
            $solicitudes = Solicitud::with(['habitacion', 'estudiante'])
                ->whereIn('estado', ['pendiente', 'aceptada'])
                ->whereHas('habitacion', fn ($q) => $q->where('usuario_id', $yo->id))->get();

            foreach ($solicitudes as $s) {
                $eventos[] = $this->visita($s, $s->estudiante->nombre . ' - ' . $s->habitacion->titulo, route('arrendador.solicitudes'));
            }
        }

        if ($yo->rol === 'empleador') {
            $trabajos = $yo->trabajos()->where('estado', 'activa')
                ->with(['horarios', 'postulaciones' => fn ($q) => $q->where('estado', 'aceptada')->with('estudiante')])->get();

            foreach ($trabajos as $t) {
                $nombres = $t->postulaciones->map(fn ($p) => explode(' ', $p->estudiante->nombre)[0])->implode(', ');
                $titulo = $t->titulo . ($nombres ? ' (' . $nombres . ')' : '');
                foreach ($t->horarios as $h) {
                    $eventos[] = $this->semanal($titulo, $h->dia, $h->turno, '#965c57');
                }
            }
        }

        return response()->json($eventos);
    }

    private function semanal(string $titulo, string $dia, string $turno, string $color): array
    {
        return [
            'title' => $titulo,
            'daysOfWeek' => [self::DIA_NUMERO[$dia]],
            'startTime' => self::HORAS[$turno][0],
            'endTime' => self::HORAS[$turno][1],
            'color' => $color,
        ];
    }

    private function actividad(Actividad $a, Carbon $dia): array
    {
        $fecha = $dia->toDateString();
        $evento = [
            'id' => 'a' . $a->id . '-' . $fecha,
            'title' => $a->titulo . ($a->monto ? ' · S/ ' . number_format((float) $a->monto, 0) : ''),
            'color' => $a->color(),
            'editable' => $a->repetir === 'no',
            'classNames' => array_values(array_filter(['ev-actividad', 'ev-' . $a->tipo, $a->hecha && $a->repetir === 'no' ? 'ev-hecha' : null])),
            'extendedProps' => [
                'actividad' => [
                    'id' => $a->id, 'titulo' => $a->titulo, 'tipo' => $a->tipo, 'fecha' => $a->fecha->toDateString(),
                    'hora_inicio' => $a->hora_inicio ? substr($a->hora_inicio, 0, 5) : '', 'hora_fin' => $a->hora_fin ? substr($a->hora_fin, 0, 5) : '',
                    'repetir' => $a->repetir, 'hasta' => $a->hasta?->toDateString() ?? '', 'monto' => $a->monto, 'nota' => $a->nota ?? '', 'hecha' => $a->hecha,
                ],
                'icono' => $a->icono(),
            ],
        ];
        if ($a->hora_inicio) {
            $evento['start'] = $fecha . 'T' . substr($a->hora_inicio, 0, 5);
            $evento['end'] = $fecha . 'T' . substr($a->hora_fin ?: Carbon::parse($a->hora_inicio)->addHour()->format('H:i'), 0, 5);
        } else {
            $evento['start'] = $fecha;
            $evento['allDay'] = true;
        }

        return $evento;
    }

    private function visita(Solicitud $s, string $titulo, string $url): array
    {
        return [
            'title' => $titulo . ($s->estado === 'pendiente' ? ' (pendiente)' : ''),
            'start' => $s->fecha_visita->toDateString(),
            'allDay' => true,
            'color' => $s->estado === 'aceptada' ? '#465548' : '#b58a4a',
            'classNames' => ['ev-visita'],
            'url' => $url,
        ];
    }
}
