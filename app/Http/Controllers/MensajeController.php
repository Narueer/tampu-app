<?php

namespace App\Http\Controllers;

use App\Models\Conversacion;
use App\Models\Habitacion;
use App\Models\Trabajo;
use App\Models\Usuario;
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
        $this->leerAvisos($request->user(), $conversacion);

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

    // chat con el administrador, que hace de asesor
    public function asesor(Request $request)
    {
        $yo = $request->user();
        $admin = Usuario::where('rol', 'admin')->where('suspendido', false)->orderBy('id')->first();

        if ($yo->rol === 'admin' || ! $admin) {
            return redirect()->route('mensajes');
        }

        $conversacion = Conversacion::de($yo)
            ->where(fn ($q) => $q->where('usuario_a_id', $admin->id)->orWhere('usuario_b_id', $admin->id))
            ->whereNull('habitacion_id')->whereNull('trabajo_id')
            ->first();

        if (! $conversacion) {
            $conversacion = Conversacion::create(['usuario_a_id' => $yo->id, 'usuario_b_id' => $admin->id]);
            $conversacion->mensajes()->create([
                'usuario_id' => $admin->id,
                'texto' => 'Hola, ' . explode(' ', $yo->nombre)[0] . '. Soy el asesor de TAMPU. Cuéntame en qué te puedo ayudar.',
            ]);
        }

        $admin->notify(new Aviso($yo->nombre . ' pidió hablar con un asesor', route('mensajes.ver', $conversacion), 'headset'));

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
        $this->leerAvisos($yo, $conversacion);

        return response()->json($mensajes->map(fn ($m) => $this->formato($m, $yo->id)));
    }

    // si ya esta viendo el chat, sus avisos de ese chat quedan leidos
    private function leerAvisos($usuario, Conversacion $conversacion): void
    {
        $url = route('mensajes.ver', $conversacion);
        $usuario->unreadNotifications->filter(fn ($n) => ($n->data['url'] ?? '') === $url)->each->markAsRead();
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
