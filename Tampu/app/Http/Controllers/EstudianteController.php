<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Horario;
use App\Models\Trabajo;
use App\Notifications\Aviso;
use Illuminate\Http\Request;

class EstudianteController extends Controller
{
    public function panel(Request $request)
    {
        $usuario = $request->user();
        $solicitudes = $usuario->solicitudes()->with('habitacion.arrendador')->latest()->get();
        $postulaciones = $usuario->postulaciones()->with('trabajo.empleador', 'trabajo.horarios')->latest()->get();
        $marcados = $usuario->horarios->map(fn ($h) => $h->dia . '-' . $h->turno)->all();

        return view('estudiante.panel', compact('solicitudes', 'postulaciones', 'marcados'));
    }

    public function guardarHorario(Request $request)
    {
        $request->validate([
            'turnos' => 'array',
            'turnos.*' => ['regex:/^[a-z]+-(manana|tarde|noche)$/'],
        ]);

        $usuario = $request->user();
        $usuario->horarios()->delete();

        foreach ($request->input('turnos', []) as $t) {
            [$dia, $turno] = explode('-', $t);
            if (array_key_exists($dia, Horario::DIAS)) {
                $usuario->horarios()->create(['dia' => $dia, 'turno' => $turno]);
            }
        }

        return back()->with('ok', 'Tu horario de clases se guardó.');
    }

    public function solicitar(Request $request, Habitacion $habitacion)
    {
        $request->validate([
            'fecha_visita' => 'required|date|after_or_equal:today',
            'mensaje' => 'nullable|string|max:300',
        ], [
            'fecha_visita.after_or_equal' => 'La fecha de visita no puede ser un día pasado.',
        ]);

        $existe = $habitacion->solicitudes()
            ->where('usuario_id', $request->user()->id)
            ->where('estado', 'pendiente')->exists();

        if ($existe) {
            return back()->with('error', 'Ya tienes una solicitud pendiente para esta habitación.');
        }

        $habitacion->solicitudes()->create([
            'usuario_id' => $request->user()->id,
            'fecha_visita' => $request->fecha_visita,
            'mensaje' => $request->mensaje,
        ]);

        $habitacion->arrendador->notify(new Aviso(
            $request->user()->nombre . ' quiere visitar "' . $habitacion->titulo . '"',
            route('arrendador.solicitudes'), 'house-door'
        ));

        return redirect()->route('estudiante.panel')->with('ok', 'Solicitud enviada. El arrendador te responderá pronto.');
    }

    public function postular(Request $request, Trabajo $trabajo)
    {
        abort_unless($trabajo->estado === 'activa' && $trabajo->empleador->esVerificado(), 404);
        $request->validate(['mensaje' => 'nullable|string|max:300']);

        $usuario = $request->user();
        if ($trabajo->postulaciones()->where('usuario_id', $usuario->id)->exists()) {
            return back()->with('error', 'Ya postulaste a esta oferta.');
        }

        $trabajo->postulaciones()->create(['usuario_id' => $usuario->id, 'mensaje' => $request->mensaje]);

        $trabajo->empleador->notify(new Aviso(
            $usuario->nombre . ' postuló a "' . $trabajo->titulo . '"',
            route('empleador.postulaciones'), 'briefcase'
        ));

        return back()->with('ok', 'Postulación enviada. Te avisaremos cuando la empresa responda.');
    }

    public function resenar(Request $request, Habitacion $habitacion)
    {
        $usuario = $request->user();

        // solo si ya hizo la visita
        $visito = $habitacion->solicitudes()
            ->where('usuario_id', $usuario->id)
            ->where('estado', 'aceptada')
            ->whereDate('fecha_visita', '<=', now())->exists();

        abort_unless($visito, 403, 'Solo puedes dejar una reseña si ya visitaste la habitación.');

        $datos = $request->validate([
            'estrellas' => 'required|integer|between:1,5',
            'comentario' => 'required|string|min:10|max:500',
        ]);

        $habitacion->resenas()->updateOrCreate(['usuario_id' => $usuario->id], $datos);

        return back()->with('ok', 'Gracias por tu reseña.');
    }

    public function favorito(Request $request, Habitacion $habitacion)
    {
        $cambio = $request->user()->favoritos()->toggle($habitacion->id);
        $agregado = count($cambio['attached']) > 0;

        if ($request->expectsJson()) {
            return response()->json(['favorito' => $agregado]);
        }

        return back()->with('ok', $agregado ? 'Guardada en tus favoritos.' : 'Quitada de tus favoritos.');
    }

    public function favoritos(Request $request)
    {
        $habitaciones = $request->user()->favoritos()->publicadas()->withAvg('resenas', 'estrellas')->withCount('fotos')->get();

        return view('estudiante.favoritos', compact('habitaciones'));
    }
}
