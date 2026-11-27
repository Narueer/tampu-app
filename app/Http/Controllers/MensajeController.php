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
        $rapidos = $actual ? $this->rapidos($yo, $actual, $mensajes) : [];

        return view('mensajes', compact('conversaciones', 'actual', 'mensajes', 'rapidos'));
    }

    // mensajes predeterminados: se envían con un toque y dependen de quién escribe y de qué trata el chat
    private function rapidos(Usuario $yo, Conversacion $c, $mensajes): array
    {
        $otro = $c->otro($yo);
        $nombre = explode(' ', $otro->nombre)[0];
        $vacio = $mensajes->where('usuario_id', $yo->id)->isEmpty();   // todavía no le escribí nada

        if ($c->habitacion) {
            $titulo = $c->habitacion->titulo;
            if ($yo->rol === 'estudiante') {
                return array_values(array_filter([
                    $vacio ? "Hola, $nombre. Me interesa \"$titulo\". ¿Sigue disponible?" : null,
                    '¿Puedo visitarlo esta semana?',
                    '¿Qué día y hora le queda bien para la visita?',
                    '¿El precio incluye agua, luz e internet?',
                    '¿Cuánto es la garantía?',
                    '¿Hay horario para llegar de noche?',
                    '¿Se puede cocinar?',
                    '¿Aceptan mascotas?',
                    'Gracias, lo voy a pensar y le aviso.',
                ]));
            }

            return array_values(array_filter([
                $vacio ? "Hola, $nombre. Gracias por tu interés en \"$titulo\"." : null,
                'Sí, sigue disponible 🙂',
                '¿Qué día y hora te queda bien para la visita?',
                'Te espero en la dirección de la publicación.',
                'Incluye agua y luz. El internet se paga aparte.',
                'La garantía es de un mes y se devuelve al dejar el cuarto.',
                'Se paga el primer mes y la garantía al mudarte.',
                'Lo siento, el cuarto ya fue alquilado.',
            ]));
        }

        if ($c->trabajo) {
            $titulo = $c->trabajo->titulo;
            if ($yo->rol === 'estudiante') {
                return array_values(array_filter([
                    $vacio ? "Hola, $nombre. Me interesa el puesto de \"$titulo\"." : null,
                    '¿La oferta sigue vigente?',
                    '¿Cuál sería el horario exacto?',
                    'Tengo clases en las mañanas, ¿puedo trabajar en las tardes?',
                    '¿Puedo trabajar solo fines de semana?',
                    '¿Cuándo sería la entrevista?',
                    '¿Qué documentos debo llevar?',
                    '¿Cada cuánto es el pago?',
                    'Gracias por la oportunidad 🙌',
                ]));
            }

            return array_values(array_filter([
                $vacio ? "Hola, $nombre. Gracias por postular a \"$titulo\"." : null,
                '¿Qué días y horas tienes libres?',
                '¿Puedes venir a una entrevista esta semana?',
                'Trae tu DNI y tu CV, por favor.',
                '¿Tienes experiencia en algo parecido?',
                'Puedes empezar el lunes. ¡Bienvenido/a!',
                'El pago es semanal.',
                'Gracias por postular. Por ahora ya cubrimos el puesto.',
            ]));
        }

        // chat con el asesor (administrador)
        if ($yo->rol === 'admin') {
            return array_values(array_filter([
                $vacio ? "Hola, $nombre. ¿En qué te puedo ayudar?" : null,
                '¿Me envías una captura del problema?',
                'Ya revisamos tu caso, está solucionado ✅',
                'Tu cuenta ya está verificada ✅',
                'Estamos revisando la publicación reportada.',
                'Nunca pagues adelantos sin visitar el cuarto.',
                'Puedes reportar una publicación desde el botón "Reportar".',
                'Gracias por escribirnos 🙌',
            ]));
        }

        $porRol = [
            'estudiante' => ['Creo que una publicación es una estafa', '¿Cómo pido una visita?', '¿Cómo registro mi horario de clases?', 'Un arrendador no me responde'],
            'arrendador' => ['¿Cuánto tarda la verificación de mi cuenta?', '¿Cómo publico una habitación?', 'Quiero editar o pausar una publicación', 'Un estudiante no llegó a la visita'],
            'empleador' => ['¿Cuánto tarda la verificación de mi cuenta?', '¿Cómo publico una oferta de trabajo?', 'Quiero cerrar una oferta', '¿Cómo veo a los postulantes?'],
        ];

        return array_merge(
            ['Hola, necesito ayuda 🙋'],
            $porRol[$yo->rol] ?? [],
            ['Tengo un problema con mi cuenta', 'Quiero reportar a un usuario', 'Gracias, ya se solucionó 🙌'],
        );
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
