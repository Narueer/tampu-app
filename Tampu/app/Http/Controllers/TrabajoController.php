<?php

namespace App\Http\Controllers;

use App\Models\Trabajo;
use Illuminate\Http\Request;

class TrabajoController extends Controller
{
    public function index(Request $request)
    {
        $consulta = Trabajo::publicadas()->with('horarios');

        if ($request->filled('modalidad')) {
            $consulta->where('modalidad', $request->modalidad);
        }
        if ($request->filled('distrito')) {
            $consulta->where('distrito', $request->distrito);
        }
        if ($request->filled('q')) {
            $consulta->where(fn ($q) => $q->whereLike('titulo', '%' . $request->q . '%')->orWhereLike('empresa', '%' . $request->q . '%'));
        }

        // filtro por horario
        $clases = collect();
        $usuario = $request->user();
        if ($usuario && $usuario->rol === 'estudiante') {
            $clases = $usuario->horarios;
        }

        $compatible = $request->boolean('compatible') && $clases->isNotEmpty();
        if ($compatible) {
            // quita los que chocan con sus clases
            $consulta->whereDoesntHave('horarios', function ($q) use ($clases) {
                $q->where(function ($w) use ($clases) {
                    foreach ($clases as $c) {
                        $w->orWhere(fn ($x) => $x->where('dia', $c->dia)->where('turno', $c->turno));
                    }
                });
            });
        }

        $trabajos = $consulta->latest()->latest('id')->get();

        $postulados = [];
        if ($usuario && $usuario->rol === 'estudiante') {
            $postulados = $usuario->postulaciones()->pluck('estado', 'trabajo_id')->all();
        }

        // puntos para el mapa de ofertas
        $puntos = $trabajos->whereNotNull('lat')->map(fn ($t) => [
            'lat' => (float) $t->lat, 'lng' => (float) $t->lng, 'titulo' => $t->titulo, 'empresa' => $t->empresa,
            'pago' => $t->pagoTexto(), 'tipo' => 'trabajo', 'url' => route('trabajos.show', $t),
        ])->values();

        $distritos = HabitacionController::DISTRITOS;

        return view('trabajos.index', compact('trabajos', 'compatible', 'clases', 'postulados', 'puntos', 'distritos'));
    }

    public function show(Request $request, Trabajo $trabajo)
    {
        abort_unless($trabajo->empleador->esVerificado(), 404);
        $trabajo->load('horarios', 'empleador');

        $usuario = $request->user();
        $clases = collect();
        $postulacion = null;
        if ($usuario && $usuario->rol === 'estudiante') {
            $clases = $usuario->horarios;
            $postulacion = $usuario->postulaciones()->where('trabajo_id', $trabajo->id)->first();
        }

        // turnos del trabajo que chocan con las clases del estudiante
        $deClase = $clases->map(fn ($c) => $c->dia . '-' . $c->turno)->all();
        $deTrabajo = $trabajo->horarios->map(fn ($h) => $h->dia . '-' . $h->turno)->all();
        $choques = array_values(array_intersect($deTrabajo, $deClase));

        $similares = Trabajo::publicadas()->with('horarios')->where('id', '!=', $trabajo->id)
            ->orderByRaw('case when modalidad = ? then 0 else 1 end', [$trabajo->modalidad])
            ->orderByRaw('case when distrito = ? then 0 else 1 end', [$trabajo->distrito])
            ->take(6)->get();

        $distancias = $trabajo->lat ? \App\Support\Lugares::distancias((float) $trabajo->lat, (float) $trabajo->lng) : [];

        return view('trabajos.show', compact('trabajo', 'postulacion', 'clases', 'deClase', 'deTrabajo', 'choques', 'similares', 'distancias'));
    }
}
