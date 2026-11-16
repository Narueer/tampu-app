<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
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

        return view('calendario');
    }

    // eventos en el formato de FullCalendar
    public function eventos(Request $request)
    {
        $yo = $request->user();
        $eventos = [];

        if ($yo->rol === 'estudiante') {
            foreach ($yo->horarios as $h) {
                $eventos[] = $this->semanal('Clases', $h->dia, $h->turno, '#1f3d36');
            }

            $aceptadas = $yo->postulaciones()->where('estado', 'aceptada')->with('trabajo.horarios')->get();
            foreach ($aceptadas as $p) {
                foreach ($p->trabajo->horarios as $h) {
                    $eventos[] = $this->semanal('Trabajo: ' . $p->trabajo->titulo, $h->dia, $h->turno, '#b4532a');
                }
            }

            foreach ($yo->solicitudes()->with('habitacion')->whereIn('estado', ['pendiente', 'aceptada'])->get() as $s) {
                $eventos[] = $this->visita($s, 'Visita: ' . $s->habitacion->titulo, route('habitaciones.show', $s->habitacion));
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
                    $eventos[] = $this->semanal($titulo, $h->dia, $h->turno, '#b4532a');
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

    private function visita(Solicitud $s, string $titulo, string $url): array
    {
        return [
            'title' => $titulo . ($s->estado === 'pendiente' ? ' (pendiente)' : ''),
            'start' => $s->fecha_visita->toDateString(),
            'allDay' => true,
            'color' => $s->estado === 'aceptada' ? '#2e7d5b' : '#c99a2e',
            'url' => $url,
        ];
    }
}
