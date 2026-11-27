<?php

namespace App\Services;

use App\Models\Habitacion;
use App\Models\Trabajo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Respaldo con inteligencia artificial para el asistente de TAMPU.
 *
 * El asistente responde primero con sus reglas (rápido y sin costo). Solo cuando no entiende
 * un mensaje llama a este servicio, que envía la pregunta a un modelo de lenguaje junto con
 * la información de TAMPU. El modelo devuelve un texto y, si corresponde, qué buscar.
 *
 * Proveedores (variables en .env):
 *   IA_PROVEEDOR=gemini    IA_CLAVE=...   (gratis: aistudio.google.com/apikey)
 *   IA_PROVEEDOR=groq      IA_CLAVE=...   (gratis: console.groq.com/keys)
 *   IA_PROVEEDOR=anthropic IA_CLAVE=...   (de pago: console.anthropic.com)
 * Opcionales: IA_MODELO, IA_URL. Sin IA_CLAVE el asistente funciona solo con sus reglas.
 * Respaldo opcional (si el principal está saturado): IA_RESPALDO_PROVEEDOR=groq  IA_RESPALDO_CLAVE=...
 * IA_TIEMPO: segundos máximos que se espera a la IA (2.5 por defecto); después responden las reglas.
 */
class AsistenteIA
{
    const PROVEEDORES = [
        'gemini' => ['url' => 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions', 'modelo' => 'gemini-flash-latest', 'ligero' => 'gemini-flash-lite-latest'],
        'groq' => ['url' => 'https://api.groq.com/openai/v1/chat/completions', 'modelo' => 'llama-3.3-70b-versatile'],
        'openai' => ['url' => 'https://api.openai.com/v1/chat/completions', 'modelo' => 'gpt-4o-mini'],
        'anthropic' => ['url' => 'https://api.anthropic.com/v1/messages', 'modelo' => 'claude-haiku-5-5'],
    ];

    public static function activo(): bool
    {
        return filled(config('services.ia.clave'));
    }

    /**
     * @param  array  $historial  [['rol' => 'usuario'|'asistente', 'texto' => '...'], ...]
     * @return array|null  ['texto' => string, 'accion' => array|null] o null si falla
     */
    public function responder(string $mensaje, array $historial = []): ?array
    {
        if (! self::activo()) {
            return null;
        }
        $mensajes = [];
        foreach (array_slice($historial, -6) as $h) {
            $mensajes[] = ['role' => $h['rol'] === 'usuario' ? 'user' : 'assistant', 'content' => mb_substr($h['texto'], 0, 600)];
        }
        $mensajes[] = ['role' => 'user', 'content' => mb_substr($mensaje, 0, 400)];

        // misma pregunta en el mismo punto de la conversación: respuesta guardada, al instante
        $ultimo = end($historial);
        $clave = 'ia.' . md5(mb_strtolower(trim($mensaje)) . '|' . ($ultimo['texto'] ?? ''));
        if ($guardada = Cache::get($clave)) {
            return $guardada;
        }

        // tiempo máximo total (IA_TIEMPO, 2.5 s por defecto): si el primer modelo no contesta a tiempo,
        // se prueba el respaldo con lo que queda; si nada llega, responden las reglas
        $limite = (float) config('services.ia.tiempo', 2.5);
        $inicio = microtime(true);
        foreach ($this->intentos() as $i) {
            $restante = $limite - (microtime(true) - $inicio);
            if ($restante < 0.6) {
                break;
            }
            $contenido = $this->llamar($i, $mensajes, $restante);
            if ($contenido && ($r = $this->leer($contenido))) {
                Cache::put($clave, $r, now()->addHours(12));

                return $r;
            }
        }

        return null;
    }

    // orden: modelo principal, proveedor de respaldo y, al final, los modelos livianos de cada uno
    private function intentos(): array
    {
        $principales = [];
        $livianos = [];
        $agregar = function (?string $proveedor, ?string $clave, ?string $modelo = null, ?string $url = null) use (&$principales, &$livianos) {
            if (! filled($clave)) {
                return;
            }
            $proveedor = isset(self::PROVEEDORES[$proveedor]) ? $proveedor : 'gemini';
            $base = self::PROVEEDORES[$proveedor];
            $principales[] = ['proveedor' => $proveedor, 'clave' => $clave, 'modelo' => $modelo ?: $base['modelo'], 'url' => $url ?: $base['url']];
            if (! $modelo && isset($base['ligero'])) {
                $livianos[] = ['proveedor' => $proveedor, 'clave' => $clave, 'modelo' => $base['ligero'], 'url' => $url ?: $base['url']];
            }
        };
        $agregar(config('services.ia.proveedor', 'gemini'), config('services.ia.clave'), config('services.ia.modelo'), config('services.ia.url'));
        $agregar(config('services.ia.respaldo_proveedor', 'groq'), config('services.ia.respaldo_clave'), config('services.ia.respaldo_modelo'));

        return array_merge($principales, $livianos);
    }

    private function llamar(array $i, array $mensajes, float $segundos): ?string
    {
        try {
            if ($i['proveedor'] === 'anthropic') {
                $r = Http::connectTimeout(min(1.5, $segundos))->timeout($segundos)->withHeaders([
                    'x-api-key' => $i['clave'],
                    'anthropic-version' => '2023-06-01',
                ])->post($i['url'], [
                    'model' => $i['modelo'], 'max_tokens' => 350, 'temperature' => 0.3,
                    'system' => $this->instrucciones(), 'messages' => $this->alternar($mensajes),
                ]);
                $contenido = $r->successful() ? collect($r->json('content'))->where('type', 'text')->pluck('text')->implode('') : null;
            } else {
                $cuerpo = [
                    'model' => $i['modelo'], 'max_tokens' => 350, 'temperature' => 0.3,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => array_merge([['role' => 'system', 'content' => $this->instrucciones()]], $mensajes),
                ];
                if ($i['proveedor'] === 'gemini') {
                    // Gemini "piensa" antes de responder: lo dejamos en lo mínimo para que conteste rápido
                    $cuerpo['reasoning_effort'] = 'low';
                    $cuerpo['max_tokens'] = 2000;
                }
                $r = Http::connectTimeout(min(1.5, $segundos))->timeout($segundos)->withToken($i['clave'])->post($i['url'], $cuerpo);
                $contenido = $r->successful() ? $r->json('choices.0.message.content') : null;
            }
            if (! $contenido) {
                Log::warning("Asistente IA sin respuesta ({$i['proveedor']} {$i['modelo']})", ['estado' => $r->status(), 'cuerpo' => mb_substr($r->body(), 0, 300)]);
            }

            return $contenido ?: null;
        } catch (\Throwable $e) {
            Log::warning("Asistente IA falló ({$i['proveedor']} {$i['modelo']}): " . $e->getMessage());

            return null;
        }
    }

    // Anthropic exige que los mensajes alternen usuario/asistente y empiecen por el usuario
    private function alternar(array $mensajes): array
    {
        $salida = [];
        foreach ($mensajes as $m) {
            if ($salida && end($salida)['role'] === $m['role']) {
                $salida[count($salida) - 1]['content'] .= "\n" . $m['content'];
            } else {
                $salida[] = $m;
            }
        }
        while ($salida && $salida[0]['role'] !== 'user') {
            array_shift($salida);
        }

        return $salida;
    }

    // el modelo debe devolver JSON; si viene con texto alrededor, se extrae el objeto
    private function leer(string $contenido): ?array
    {
        $json = json_decode($contenido, true);
        if (! is_array($json) && preg_match('/\{.*\}/s', $contenido, $m)) {
            $json = json_decode($m[0], true);
        }
        if (! is_array($json) || ! filled($json['respuesta'] ?? null)) {
            // sin JSON válido: se usa el texto tal cual, si es razonable
            $texto = trim(strip_tags($contenido));

            return $texto !== '' && mb_strlen($texto) < 900 && ! str_starts_with($texto, '{') ? ['texto' => $texto, 'accion' => null] : null;
        }
        $accion = is_array($json['accion'] ?? null) ? $json['accion'] : null;
        if ($accion && ! in_array($accion['buscar'] ?? null, ['cuartos', 'trabajos'])) {
            $accion = null;
        }

        return ['texto' => mb_substr(trim($json['respuesta']), 0, 900), 'accion' => $accion];
    }

    private function instrucciones(): string
    {
        $datos = Cache::remember('asistente.ia.contexto', 600, function () {
            $precios = Habitacion::publicadas()->selectRaw('distrito, round(avg(precio)) as promedio, min(precio) as minimo, count(*) as total')
                ->groupBy('distrito')->get()
                ->map(fn ($p) => "- {$p->distrito}: {$p->total} viviendas, promedio S/ {$p->promedio}, desde S/ {$p->minimo}")->implode("\n");

            return ['precios' => $precios, 'trabajos' => Trabajo::where('estado', 'activa')->count()];
        });
        $tipos = implode(', ', array_keys(Habitacion::TIPOS));
        $lugares = collect(\App\Support\Lugares::UNIVERSIDADES)
            ->map(fn ($u) => $u['completo'] . ($u['lat'] !== null ? " ({$u['lugar']})" : ''))->implode('; ');

        return <<<TXT
Eres Tampu, el asistente virtual de TAMPU, una página web gratuita para estudiantes que llegan de provincia a la ciudad del Cusco (Perú).
TAMPU tiene dos partes: (1) alquiler de cuartos, minidepartamentos, depas con roomies y casas de estudiantes, publicados solo por arrendadores cuyo DNI revisó el administrador; (2) ofertas de trabajo de medio tiempo de empresas con RUC verificado, que el estudiante puede filtrar según su horario de clases.
Reglas de la página: el estudiante pide una visita y el celular del dueño aparece recién cuando la acepta; nunca se debe pagar adelanto sin visitar; usar TAMPU es gratis y no cobra comisión; hay reseñas con estrellas después de una visita aceptada; hay mensajes internos, favoritos, calendario y constancias en PDF; cualquier usuario puede reportar una publicación sospechosa y el administrador puede suspender la cuenta. El registro pide un DNI peruano de 8 dígitos (los extranjeros deben escribir desde Contacto). No hay botón para borrar la cuenta: se pide desde Contacto.
Solo funciona en seis distritos de la ciudad del Cusco: Cusco, Wanchaq, Santiago, San Sebastián, San Jerónimo y Saylla. Busca cuartos cerca de estas universidades e institutos: {$lugares}. Si el estudiante menciona otro, pregúntale en qué zona o distrito queda.
Precios actuales en TAMPU:
{$datos['precios']}
Ofertas de trabajo activas: {$datos['trabajos']}.
Emergencias en Perú: Policía 105, SAMU 106, Bomberos 116. Salud mental: Línea 113, opción 5.
TAMPU es el proyecto final del curso Programación Web de la Universidad Continental (Cusco, 2026), hecho por Wernher Alvares, César Juro, David Achahuanco y Olenka Aedo. "Tampu" en quechua era un lugar de descanso en el camino.

Cómo responder:
- Responde en el idioma del usuario (normalmente español), con tono amable y cercano, en máximo 80 palabras, sin títulos ni markdown.
- Entiende errores de ortografía, jerga peruana y mensajes desordenados.
- Si preguntan algo fuera de TAMPU (otras ciudades, tareas, temas generales), responde breve y con amabilidad, y vuelve a ofrecer ayuda con cuartos o trabajos en Cusco.
- Si el mensaje tiene doble sentido sexual o vulgar (jerga peruana como "kachar", "telo", "jugar al papá y la mamá", cuartos "por un rato" o por horas), responde con humor breve y respetuoso, aclara que TAMPU no alquila cuartos por horas y no uses "accion". Vivir en pareja sí es válido: sugiere minidepartamentos.
- Nunca inventes cuartos, trabajos, precios, teléfonos ni enlaces. Si el usuario quiere ver cuartos o trabajos, usa "accion" y la página mostrará resultados reales.
- No pidas datos personales ni contraseñas. Si alguien está en crisis o peligro, da los números de emergencia.

Devuelve SOLO un objeto JSON, sin texto adicional:
{"respuesta": "texto para el usuario", "accion": null}
o, si conviene mostrar resultados:
{"respuesta": "texto corto", "accion": {"buscar": "cuartos", "distrito": "Wanchaq|Cusco|Santiago|San Sebastián|San Jerónimo|Saylla|null", "precio_max": numero_o_null, "tipo": "{$tipos}|null", "cerca_de": "nombre de la universidad o instituto (ej. UNSAAC, SENATI, Khipu)|null"}}
{"respuesta": "texto corto", "accion": {"buscar": "trabajos", "palabra": "rubro o puesto o null", "turno": "mañana|tarde|noche|null", "fines_de_semana": true_o_false}}
TXT;
    }
}
