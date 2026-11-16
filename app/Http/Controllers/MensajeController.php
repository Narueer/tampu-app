<?php

namespace App\Http\Controllers;

use App\Models\Conversacion;
use App\Models\Habitacion;
use App\Models\Trabajo;
use App\Notifications\Aviso;
use Illuminate\Http\Request;

class MensajeController extends Controller
{
    public function index(Request $request)
    {
        return $this->vista($request, null);
    }

    public function ver(Request $request, Conversacion $conversacion)
    {
        abort_unless($conversacion->participa($request->user()), 403);

        $conversacion->mensajes()->where('usuario_id', '!=', $request->user()->id)->update(['leido' => true]);

        return $this->vista($request, $conversacion);
    }

    // abre el chat desde una habitacion, un trabajo, una solicitud o una postulacion
    public function iniciar(Request $request)
    {
        $request->validate([
            'habitacion_id' => 'nullable|required_without:trabajo_id|exists:habitaciones,id',
            'trabajo_id' => 'nullable|exists:trabajos,id',
            'estudiante_id' => 'nullable|exists:usuarios,id',
        ]);

        $yo = $request->user();
        $publicacion = $request->habitacion_id ? Habitacion::find($request->habitacion_id) : Trabajo::find($request->trabajo_id);
        $duenoId = $publicacion->usuario_id;

        if ($yo->rol === 'estudiante') {
            $otroId = $duenoId;
        } else {
            // el dueño solo puede escribir a quien le pidio visita o postulo
            abort_unless($duenoId === $yo->id && $request->estudiante_id, 403);
            $relacion = $request->habitacion_id ? 'solicitudes' : 'postulaciones';
            abort_unless($publicacion->$relacion()->where('usuario_id', $request->estudiante_id)->exists(), 403);
            $otroId = (int) $request->estudiante_id;
        }

        abort_if($otroId === $yo->id, 403);

        $campo = $request->habitacion_id ? 'habitacion_id' : 'trabajo_id';
        $conversacion = Conversacion::de($yo)
            ->where(fn ($q) => $q->where('usuario_a_id', $otroId)->orWhere('usuario_b_id', $otroId))
            ->where($campo, $publicacion->id)
            ->first();

        if (! $conversacion) {
            $conversacion = Conversacion::create([
                'usuario_a_id' => $yo->id,
                'usuario_b_id' => $otroId,
                $campo => $publicacion->id,
            ]);
        }

        return redirect()->route('mensajes.ver', $conversacion);
    }

    public function enviar(Request $request, Conversacion $conversacion)
    {
        $yo = $request->user();
        abort_unless($conversacion->participa($yo), 403);
        $request->validate(['texto' => 'required|string|max:1000']);

        $mensaje = $conversacion->mensajes()->create(['usuario_id' => $yo->id, 'texto' => $request->texto]);
        $conversacion->touch();

        // un solo aviso mientras no lea la conversacion
        $otro = $conversacion->otro($yo);
        $url = route('mensajes.ver', $conversacion);
        $yaAvisado = $otro->unreadNotifications->contains(fn ($n) => ($n->data['url'] ?? '') === $url);
        if (! $yaAvisado) {
            $otro->notify(new Aviso('Nuevo mensaje de ' . $yo->nombre, $url, 'chat-dots'));
        }

        if ($request->expectsJson()) {
            return response()->json($this->formato($mensaje, $yo->id));
        }

        return back();
    }

    // la vista pregunta cada 4 segundos si hay mensajes nuevos
    public function nuevos(Request $request, Conversacion $conversacion)
    {
        $yo = $request->user();
        abort_unless($conversacion->participa($yo), 403);

        $mensajes = $conversacion->mensajes()->where('id', '>', (int) $request->desde)->oldest('id')->get();
        $conversacion->mensajes()->where('usuario_id', '!=', $yo->id)->where('leido', false)->update(['leido' => true]);

        return response()->json($mensajes->map(fn ($m) => $this->formato($m, $yo->id)));
    }

    private function vista(Request $request, ?Conversacion $actual)
    {
        $yo = $request->user();
        $conversaciones = Conversacion::de($yo)
            ->with(['usuarioA', 'usuarioB', 'habitacion', 'trabajo', 'ultimo'])
            ->withCount(['mensajes as sin_leer' => fn ($q) => $q->where('usuario_id', '!=', $yo->id)->where('leido', false)])
            ->latest('updated_at')->get();

        $mensajes = $actual ? $actual->mensajes()->oldest('id')->get() : collect();

        return view('mensajes', compact('conversaciones', 'actual', 'mensajes'));
    }

    private function formato($m, int $yoId): array
    {
        return [
            'id' => $m->id,
            'texto' => $m->texto,
            'mio' => $m->usuario_id === $yoId,
            'hora' => $m->created_at->format('d/m H:i'),
        ];
    }
}
