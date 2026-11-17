<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Trabajo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

// asistente por palabras clave, no usa ninguna API externa
class ChatbotController extends Controller
{
    const SUGERENCIAS = ['Cuartos en Wanchaq hasta 400', 'Trabajos compatibles', '¿Cómo evito estafas?', 'Hablar con un asesor'];

    const PREGUNTAS = [
        [['estafa', 'seguro', 'adelanto', 'confiar', 'enganar'],
            'En TAMPU solo publican arrendadores y empresas con DNI o RUC revisado. Nunca pagues adelantos sin visitar el cuarto. Si algo te parece raro, usa el botón "Reportar" de la publicación.'],
        [['verific', 'documento', 'ruc'],
            'Al registrarte como arrendador o empleador subes tu DNI o RUC. El administrador lo revisa y, cuando te verifica, ya puedes publicar. Te llega una notificación.'],
        [['registr', 'crear cuenta', 'cuenta nueva'],
            'Entra a "Iniciar sesión" y luego a "Regístrate aquí". Eliges si eres estudiante, arrendador o empleador.'],
        [['contrasena', 'olvide', 'clave'],
            'En la pantalla de inicio de sesión toca "¿Olvidaste tu contraseña?" y te mandamos un enlace para cambiarla.'],
        [['visita', 'visitar'],
            'Abre la habitación, elige la fecha y toca "Solicitar visita". Cuando el arrendador acepta, ves su celular y puedes descargar la constancia en PDF.'],
        [['postul'],
            'En Trabajos toca "Postular" en la oferta que te guste. La empresa acepta o rechaza y te llega una notificación.'],
        [['resena', 'estrellas', 'calific', 'opinion'],
            'Puedes dejar una reseña con estrellas cuando ya visitaste la habitación (tu visita debe estar aceptada).'],
        [['report', 'denunci', 'sospech'],
            'Dentro de cada habitación o trabajo está el botón "Reportar". El administrador lo revisa y puede suspender la cuenta.'],
        [['favorit', 'guardar'],
            'Toca el corazón en una habitación para guardarla. Las ves en "Favoritos" desde tu panel.'],
        [['calendario', 'agenda'],
            'En tu panel está el Calendario: ahí ves tus clases, tus turnos de trabajo y las visitas que pediste.'],
        [['mensaje', 'chat', 'escribir', 'contactar'],
            'Desde una habitación o un trabajo toca "Enviar mensaje" para hablar con el arrendador o la empresa.'],
        [['mapa', 'ubicacion', 'donde queda'],
            'En Habitaciones hay un mapa con la ubicación de cada cuarto. Toca un punto para ver el precio.'],
        [['horario', 'clases'],
            'En tu panel marcas los turnos en los que tienes clases. Así en Trabajos puedes ver solo las ofertas que no se cruzan.'],
    ];

    public function responder(Request $request)
    {
        $request->validate(['mensaje' => 'required|string|max:200']);
        $texto = $this->normalizar($request->mensaje);

        if ($this->tiene($texto, ['hola', 'buenas', 'buenos dias']) && strlen($texto) < 20) {
            $saludo = '¡Hola! Soy el asistente de TAMPU. Puedo buscarte cuartos por distrito y precio, '
                . 'mostrarte trabajos o resolver dudas.';

            return $this->respuesta($saludo, [], self::SUGERENCIAS);
        }

        if ($this->tiene($texto, ['asesor', 'hablar con alguien', 'hablar con una persona', 'persona real', 'soporte', 'atencion al cliente', 'ayuda humana'])) {
            return $this->pedirAsesor($request);
        }

        if ($this->tiene($texto, ['gracias', 'chau', 'adios'])) {
            return $this->respuesta('¡De nada! Éxitos en tus estudios.');
        }

        // si es una pregunta tipo "como...", primero las dudas frecuentes
        $faq = $this->buscarPregunta($texto);
        if ($faq && $this->tiene($texto, ['como', 'puedo', 'que hago', 'donde', 'para que'])) {
            return $this->respuesta($faq);
        }

        if ($this->tiene($texto, ['cuarto', 'habitacion', 'alquil', 'vivir', 'departamento', 'hospedaje'])) {
            return $this->buscarHabitaciones($texto);
        }

        if ($this->tiene($texto, ['trabajo', 'empleo', 'chamba', 'cachuelo', 'practica'])) {
            return $this->buscarTrabajos($request, $texto);
        }

        if ($faq) {
            return $this->respuesta($faq);
        }

        $noEntendi = 'No te entendí bien. Prueba con algo como "cuartos en Santiago hasta 300" o "¿cómo me verifico?".';

        return $this->respuesta($noEntendi, [], self::SUGERENCIAS);
    }

    private function buscarHabitaciones(string $texto)
    {
        $consulta = Habitacion::publicadas();
        $filtros = [];

        foreach (HabitacionController::DISTRITOS as $d) {
            if (str_contains($texto, $this->normalizar($d))) {
                $consulta->where('distrito', $d);
                $filtros['distrito'] = $d;
                break;
            }
        }

        if (preg_match('/(\d{3,4})/', $texto, $m)) {
            $consulta->where('precio', '<=', (int) $m[1]);
            $filtros['precio_max'] = (int) $m[1];
        }

        $total = $consulta->count();
        $lista = $consulta->orderBy('precio')->take(3)->get();

        if ($lista->isEmpty()) {
            return $this->respuesta('No encontré cuartos con eso. Prueba con otro distrito o un precio un poco mayor.', [
                ['texto' => 'Ver todas las habitaciones', 'url' => route('habitaciones.index')],
            ]);
        }

        $enlaces = $lista->map(fn ($h) => [
            'texto' => $h->titulo . ' - S/ ' . (int) $h->precio,
            'url' => route('habitaciones.show', $h),
        ])->all();

        if ($total > 3) {
            $enlaces[] = ['texto' => 'Ver las ' . $total . ' habitaciones', 'url' => route('habitaciones.index', $filtros)];
        }

        return $this->respuesta('Encontré ' . $total . ' cuarto(s). Estos son los más baratos:', $enlaces);
    }

    private function buscarTrabajos(Request $request, string $texto)
    {
        $usuario = $request->user();
        $compatibles = $usuario && $usuario->rol === 'estudiante' && $usuario->horarios()->exists();

        $consulta = Trabajo::publicadas()->with('horarios');
        if (str_contains($texto, 'practica')) {
            $consulta->where('modalidad', 'practicas');
        }

        $trabajos = $consulta->latest()->get();

        if ($compatibles) {
            $clases = $usuario->horarios->map(fn ($h) => $h->dia . '-' . $h->turno)->all();
            $trabajos = $trabajos->reject(fn ($t) => $t->horarios->contains(fn ($h) => in_array($h->dia . '-' . $h->turno, $clases)));
        }

        if ($trabajos->isEmpty()) {
            return $this->respuesta('Por ahora no hay ofertas que encajen.', [['texto' => 'Ver trabajos', 'url' => route('trabajos.index')]]);
        }

        $enlaces = $trabajos->take(3)->map(fn ($t) => [
            'texto' => $t->titulo . ' (' . $t->horarioTexto() . ')',
            'url' => route('trabajos.index', $compatibles ? ['compatible' => 1] : []),
        ])->values()->all();

        $intro = $compatibles
            ? 'Estas ofertas no chocan con tu horario de clases:'
            : 'Estas son algunas ofertas. Si inicias sesión como estudiante y registras tu horario, te muestro solo las compatibles.';

        return $this->respuesta($intro, $enlaces);
    }

    private function pedirAsesor(Request $request)
    {
        if (! $request->user()) {
            return $this->respuesta('Para hablar con un asesor primero inicia sesión. Si no tienes cuenta, puedes escribirnos desde Contacto.', [
                ['texto' => 'Iniciar sesión', 'url' => route('login')],
                ['texto' => 'Ir a Contacto', 'url' => route('contacto')],
            ]);
        }

        if ($request->user()->rol === 'admin') {
            return $this->respuesta('Tú eres el asesor. Las consultas te llegan en Mensajes.', [['texto' => 'Ver mensajes', 'url' => route('mensajes')]]);
        }

        return $this->respuesta('Te conecto con un asesor de TAMPU. Te responderá por el chat de Mensajes.', [
            ['texto' => 'Abrir chat con un asesor', 'url' => route('asesor')],
        ]);
    }

    private function buscarPregunta(string $texto): ?string
    {
        foreach (self::PREGUNTAS as [$claves, $respuesta]) {
            if ($this->tiene($texto, $claves)) {
                return $respuesta;
            }
        }

        return null;
    }

    private function respuesta(string $texto, array $enlaces = [], array $sugerencias = [])
    {
        return response()->json(compact('texto', 'enlaces', 'sugerencias'));
    }

    private function normalizar(string $texto): string
    {
        return Str::lower(Str::ascii($texto));
    }

    private function tiene(string $texto, array $palabras): bool
    {
        foreach ($palabras as $p) {
            if (str_contains($texto, $p)) {
                return true;
            }
        }

        return false;
    }
}
