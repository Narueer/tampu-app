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

        Aviso::enviar($habitacion->arrendador,
            $request->user()->nombre . ' quiere visitar "' . $habitacion->titulo . '"',
            route('arrendador.solicitudes'), 'house-door',
            'Nueva solicitud de visita', [
                $request->user()->nombre . ' quiere visitar tu cuarto «' . $habitacion->titulo . '».',
                'Fecha propuesta: ' . \Illuminate\Support\Carbon::parse($request->fecha_visita)->translatedFormat('l d \\d\\e F') . '.',
                'Acepta o rechaza la visita desde tu panel. Cuando aceptas, el estudiante ve tu celular para coordinar.',
            ], 'Ver la solicitud');

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

        Aviso::enviar($trabajo->empleador,
            $usuario->nombre . ' postuló a "' . $trabajo->titulo . '"',
            route('empleador.postulaciones'), 'briefcase',
            'Nueva postulación', [
                $usuario->nombre . ' postuló a tu oferta «' . $trabajo->titulo . '».',
                'Su horario de clases es compatible con los turnos que indicaste. Revisa su perfil y respóndele desde tu panel.',
            ], 'Ver la postulación');

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

    // "¿El cuarto era como en las fotos?": sí suma al sello de confianza; no crea un reporte para el administrador
    public function confirmarVisita(Request $request, \App\Models\Solicitud $solicitud)
    {
        abort_unless($solicitud->usuario_id === $request->user()->id, 403);
        abort_unless($solicitud->puedeConfirmar(), 403, 'Solo puedes confirmar una visita aceptada que ya pasó, y una sola vez.');
        $datos = $request->validate([
            'coincide' => 'required|in:si,no',
            'detalle' => 'nullable|string|max:500',
        ]);

        $solicitud->update(['confirmacion' => $datos['coincide'] === 'si' ? 'coincide' : 'no_coincide', 'confirmado_at' => now()]);

        if ($datos['coincide'] === 'no') {
            \App\Models\Reporte::create([
                'usuario_id' => $request->user()->id,
                'habitacion_id' => $solicitud->habitacion_id,
                'motivo' => 'Fotos o datos falsos',
                'detalle' => 'Después de la visita del ' . $solicitud->fecha_visita->format('d/m/Y') . ' el estudiante indicó que el cuarto no coincide con la publicación. '
                    . ($datos['detalle'] ?? ''),
                'estado' => 'pendiente',
            ]);

            return back()->with('ok', 'Gracias por avisar. El administrador revisará la publicación.');
        }

        return back()->with('ok', '¡Gracias! Tu confirmación ayuda a otros estudiantes a confiar en este cuarto.');
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
        $habitaciones = $request->user()->favoritos()->publicadas()->withAvg('resenas', 'estrellas')->withCount('fotos')->withCount(['solicitudes as confirmadas_count' => fn ($q) => $q->where('confirmacion', 'coincide')])->get();

        return view('estudiante.favoritos', compact('habitaciones'));
    }
}
