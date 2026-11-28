<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\EventoSeguridad;
use App\Models\Usuario;
use App\Notifications\Aviso;
use App\Support\Respaldo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Tareas automáticas. Render gratis no tiene "cron", así que cron-job.org visita
 * /tareas/CLAVE cada hora (la clave está en la variable TAREAS_CLAVE de Render).
 * Cada tarea revisa si ya se hizo, así que no importa si se llama muchas veces.
 */
class TareasController extends Controller
{
    public function ejecutar(string $clave)
    {
        $correcta = (string) config('tampu.tareas_clave');
        abort_unless($correcta !== '' && hash_equals($correcta, $clave), 404);

        $ahora = now('America/Lima');
        $hecho = [];
        foreach (['recordatorios', 'resumenSemanal', 'respaldo'] as $tarea) {
            try {
                $hecho[$tarea] = $this->$tarea($ahora);
            } catch (\Throwable $e) {
                Log::error("Tarea $tarea falló: " . $e->getMessage());
                $hecho[$tarea] = 'error';
            }
        }

        return response()->json(['ok' => true, 'hora' => $ahora->format('Y-m-d H:i'), 'tareas' => $hecho]);
    }

    // desde las 7 am: aviso de lo de hoy y mañana (exámenes, entregas y pagos), una vez por día
    private function recordatorios(Carbon $ahora): string
    {
        if ($ahora->hour < 7) {
            return 'espera a las 7 am';
        }
        $hoy = $ahora->copy()->startOfDay();
        $manana = $hoy->copy()->addDay();
        $enviados = 0;

        $porUsuario = Actividad::whereIn('tipo', ['examen', 'entrega', 'pago'])
            ->where('fecha', '<=', $manana->toDateString())
            ->with('usuario')->get()->groupBy('usuario_id');

        foreach ($porUsuario as $actividades) {
            $usuario = $actividades->first()->usuario;
            if (! $usuario || $usuario->suspendido || ! Cache::add('recordatorio:' . $usuario->id . ':' . $hoy->toDateString(), 1, now()->addDays(2))) {
                continue;
            }
            $lineas = [];
            foreach ($actividades as $a) {
                foreach ($a->fechasEntre($hoy, $manana->copy()->endOfDay()) as $dia) {
                    if ($a->repetir === 'no' && $a->hecha) {
                        continue;
                    }
                    $cuando = $dia->isSameDay($hoy) ? 'Hoy' : 'Mañana';
                    $lineas[] = "$cuando: " . (Actividad::TIPOS[$a->tipo][0] ?? '') . ' — ' . $a->titulo
                        . ($a->hora_inicio ? ' a las ' . substr($a->hora_inicio, 0, 5) : '')
                        . ($a->monto ? ' (S/ ' . number_format((float) $a->monto, 2) . ')' : '');
                }
            }
            if (! $lineas) {
                Cache::forget('recordatorio:' . $usuario->id . ':' . $hoy->toDateString());
                continue;
            }
            sort($lineas);
            $examenes = count(array_filter($lineas, fn ($l) => str_contains($l, 'Examen')));
            Aviso::enviar($usuario,
                count($lineas) === 1 ? $lineas[0] : 'Tienes ' . count($lineas) . ' pendientes para hoy y mañana',
                route('calendario'), 'calendar-check',
                $examenes ? '¡Se viene examen! Tus pendientes de hoy y mañana' : 'Tus pendientes de hoy y mañana',
                array_merge($lineas, ['Recuerda: Mi planificador te muestra también tus turnos libres para estudiar.']),
                'Abrir mi planificador');
            $enviados++;
        }

        return "$enviados avisos";
    }

    // domingo desde las 6 pm: cómo viene la semana
    private function resumenSemanal(Carbon $ahora): string
    {
        if (! $ahora->isSunday() || $ahora->hour < 18) {
            return 'solo domingos desde las 6 pm';
        }
        $desde = $ahora->copy()->addDay()->startOfDay();
        $hasta = $desde->copy()->addDays(6)->endOfDay();
        $enviados = 0;

        $estudiantes = Usuario::where('rol', 'estudiante')->where('suspendido', false)
            ->whereHas('actividades')->with('actividades')->get();
        foreach ($estudiantes as $u) {
            if (! Cache::add('resumen:' . $u->id . ':' . $desde->format('o-W'), 1, now()->addDays(8))) {
                continue;
            }
            $items = [];
            foreach ($u->actividades as $a) {
                foreach ($a->fechasEntre($desde, $hasta) as $dia) {
                    $items[] = [$dia->toDateString() . ($a->hora_inicio ?? ''), $a, $dia];
                }
            }
            if (! $items) {
                continue;
            }
            usort($items, fn ($x, $y) => $x[0] <=> $y[0]);
            $cuenta = collect($items)->countBy(fn ($i) => $i[1]->tipo);
            $pagos = collect($items)->filter(fn ($i) => $i[1]->tipo === 'pago')->sum(fn ($i) => (float) $i[1]->monto);

            $lineas = ['Así viene tu semana: ' . collect([
                $cuenta->get('examen') ? $cuenta['examen'] . ' examen' . ($cuenta['examen'] > 1 ? 'es' : '') : null,
                $cuenta->get('entrega') ? $cuenta['entrega'] . ' entrega' . ($cuenta['entrega'] > 1 ? 's' : '') : null,
                $pagos ? 'S/ ' . number_format($pagos, 2) . ' en pagos' : null,
                $cuenta->get('estudio') ? $cuenta['estudio'] . ' bloque' . ($cuenta['estudio'] > 1 ? 's' : '') . ' de estudio' : null,
            ])->filter()->implode(', ') . '.'];
            foreach (array_slice($items, 0, 12) as [, $a, $dia]) {
                $lineas[] = ucfirst($dia->translatedFormat('l j')) . ': ' . $a->titulo . ($a->hora_inicio ? ' (' . substr($a->hora_inicio, 0, 5) . ')' : '');
            }
            Aviso::enviar($u, 'Tu semana: ' . $lineas[0], route('calendario'), 'calendar-check', 'Tu plan de la semana', $lineas, 'Ver mi semana');
            $enviados++;
        }

        return "$enviados resúmenes";
    }

    // una copia de seguridad por día (desde las 3 am)
    private function respaldo(Carbon $ahora): string
    {
        if ($ahora->hour < 3) {
            return 'espera a las 3 am';
        }
        if (! Cache::add('respaldo:' . $ahora->toDateString(), 1, now()->addDays(2))) {
            return 'ya hecho hoy';
        }
        $ruta = Respaldo::crear('automática');
        EventoSeguridad::create(['tipo' => 'respaldo', 'detalle' => basename($ruta), 'ip' => request()->ip()]);

        return basename($ruta);
    }
}
