<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Trabajo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

// Asistente "Tampu": entiende la pregunta por palabras clave (no usa APIs externas).
// Reconoce distrito, universidad, tipo de vivienda, precio, servicios, turnos y rubro de trabajo,
// y recuerda la ultima busqueda para preguntas como "¿y en Santiago?" o "¿más baratos?".
class ChatbotController extends Controller
{
    const SUGERENCIAS = ['Cuartos en Wanchaq hasta 400', 'Trabajos compatibles', '¿Cómo evito estafas?', 'Hablar con un asesor'];

    const DISTRITOS = [
        'Wanchaq' => ['wanchaq', 'huanchac', 'wanchac', 'wanchak'],
        'San Sebastián' => ['san sebastian', 'sn sebastian', 'sansebastian'],
        'San Jerónimo' => ['san jeronimo', 'sanje', 'sn jeronimo'],
        'Santiago' => ['santiago'],
        'Saylla' => ['saylla', 'angostura'],
        'Cusco' => ['centro historico', 'el centro', 'plaza de armas', 'cercado', 'san blas', 'cusco centro', 'en cusco'],
    ];

    const UNIVERSIDADES = [
        'UNSAAC' => ['unsaac', 'san antonio abad', 'la san antonio', 'la nacional'],
        'U. Andina' => ['andina', 'uac', 'larapa'],
        'U. Continental' => ['continental', 'ucontinental'],
        'IST Túpac Amaru' => ['tupac amaru', 'instituto'],
    ];

    const TIPOS = [
        'roomies' => ['roomie', 'rumi', 'compartir depa', 'depa compartido', 'departamento compartido', 'companero de depa', 'companeros de depa'],
        'residencia' => ['casa de estudiantes', 'casa estudiantil', 'residencia', 'pension', 'casa de estudiante'],
        'minidepartamento' => ['minidepa', 'mini depa', 'minidepartamento', 'mini departamento', 'departamento', 'depa '],
        'compartida' => ['compartid', 'doble', 'para dos', 'con otra persona'],
        'individual' => ['individual', 'para mi solo', 'para mi sola', 'solo para mi', 'privado'],
    ];

    const SERVICIOS = [
        'Baño propio' => ['bano propio', 'bano privado', 'con bano'],
        'Agua caliente' => ['agua caliente', 'terma', 'ducha caliente'],
        'Amoblado' => ['amoblado', 'amueblado', 'con muebles'],
        'Wifi' => ['wifi', 'internet'],
        'Cocina' => ['cocina', 'cocinar'],
        'Lavandería' => ['lavanderia', 'lavadora', 'lavar ropa'],
        'Desayuno' => ['desayuno', 'comida incluida', 'alimentacion'],
    ];

    // rubros de trabajo: lo que escribe el estudiante => palabras a buscar en titulo y descripcion
    const RUBROS = [
        'mozo' => ['mozo', 'mesa'], 'mesero' => ['mozo', 'mesa'], 'cocina' => ['cocina', 'pasteler', 'panader'],
        'barista' => ['barista', 'cafe'], 'cafe' => ['barista', 'cafe'], 'repart' => ['repartidor', 'delivery'], 'delivery' => ['repartidor', 'delivery'],
        'profesor' => ['profesor', 'tutor'], 'clases de' => ['profesor', 'tutor'], 'ensenar' => ['profesor', 'tutor'], 'tutor' => ['tutor', 'profesor'],
        'recepcion' => ['recepcion'], 'hotel' => ['hotel', 'hostal'], 'hostal' => ['hostal', 'hotel'],
        'call center' => ['teleoperador', 'agente'], 'teleoperador' => ['teleoperador'], 'venta' => ['venta', 'vendedor'], 'vendedor' => ['vendedor', 'venta'],
        'turismo' => ['tour', 'guia', 'turist'], 'guia' => ['guia', 'tour'], 'sistemas' => ['sistemas', 'soporte'], 'contab' => ['contabilidad', 'contador'],
        'civil' => ['ingenieria civil', 'topograf'], 'arquitect' => ['arquitect', 'cad'], 'diseno' => ['disen'], 'caja' => ['cajero', 'caja'], 'cajero' => ['cajero', 'caja'],
        'ingles' => ['ingles', 'bilingue'], 'animador' => ['animador', 'ninos'], 'ninos' => ['ninos', 'ludoteca'], 'veterinar' => ['veterinar', 'perros'], 'perro' => ['perros'],
        'gimnasio' => ['gimnasio', 'fitness'], 'marketing' => ['marketing', 'redes'], 'redes sociales' => ['redes', 'community'],
    ];

    // dudas frecuentes: [palabras, respuesta, enlaces opcionales]
    const PREGUNTAS = [
        'estafa' => [['estafa', 'enganar', 'confiar', 'fraude', 'me robaron', 'es seguro', 'adelanto'],
            'Tres reglas de oro: 1) nunca pagues adelanto sin ver el cuarto, 2) desconfía de precios muy bajos o de quien te apura ("quedan pocos"), 3) pide recibo o contrato. En TAMPU solo publican personas con DNI o RUC revisado y puedes usar el botón "Reportar" si algo te parece raro.'],
        'visita' => [['que revisar', 'que debo ver', 'que preguntar', 'al visitar', 'en la visita', 'checklist'],
            'En la visita revisa: que el agua caliente funcione, la presión del agua, la señal de wifi y de celular, si entra luz natural, las cerraduras, la humedad en paredes, el ruido de la calle y cuánto demoras a tu universidad. Pregunta qué incluye el precio (luz, agua, internet) y las reglas de la casa.'],
        'contrato' => [['contrato', 'garantia', 'recibo', 'deposito', 'mes adelantado'],
            'Lo normal en Cusco es pagar un mes adelantado y un mes de garantía, que te devuelven al salir si dejas todo en buen estado. Pide siempre recibo o un contrato simple con el precio, qué servicios incluye, la fecha de pago y las condiciones para devolver la garantía.'],
        'verificar' => [['verific', 'documento', 'ruc', 'insignia', 'sello'],
            'Arrendadores y empresas suben su DNI o RUC al registrarse. El administrador lo revisa y, cuando los verifica, aparece el sello "Verificado" y recién pueden publicar.'],
        'registro' => [['registr', 'crear cuenta', 'cuenta nueva', 'inscribir'],
            'Entra a "Iniciar sesión" → "Regístrate aquí" y elige si eres estudiante, arrendador o empleador. Te llegará un código de 6 números a tu correo para confirmarlo.',
            [['texto' => 'Crear mi cuenta', 'ruta' => 'registro']]],
        'publicar' => [['publicar', 'anunciar', 'tengo un cuarto', 'alquilo', 'ofrezco trabajo', 'tengo un negocio'],
            'Para publicar, crea una cuenta de arrendador (cuartos) o de empleador (trabajos) y sube tu DNI o RUC. Cuando el administrador te verifique, podrás publicar gratis desde tu panel.',
            [['texto' => 'Registrarme para publicar', 'ruta' => 'registro']]],
        'precio_tampu' => [['cuesta usar', 'es gratis', 'cobran', 'comision', 'pagar por usar'],
            'Usar TAMPU es gratis para estudiantes, arrendadores y empresas. No cobramos comisión. Si alguien te pide pagar "por TAMPU", repórtalo.'],
        'contrasena' => [['contrasena', 'olvide', 'clave', 'password'],
            'En "Iniciar sesión" toca "¿Olvidaste tu contraseña?" y te enviamos un enlace a tu correo para crear una nueva.'],
        'dos_pasos' => [['dos pasos', '2 pasos', 'autenticador', 'google authenticator', 'codigo de seguridad'],
            'En "Mi perfil" → "Seguridad de la cuenta" puedes activar la verificación en dos pasos con Google o Microsoft Authenticator. Así, aunque alguien sepa tu contraseña, no podrá entrar.'],
        'pedir_visita' => [['solicitar visita', 'pedir visita', 'agendar', 'como visito', 'quiero visitar'],
            'Abre la habitación, elige la fecha y toca "Solicitar visita". Cuando el arrendador acepta, ves su celular y puedes descargar una constancia en PDF.'],
        'postular' => [['postul', 'aplicar a', 'enviar cv'],
            'En Trabajos toca "Postular" en la oferta que te guste y escribe un mensaje corto. La empresa acepta o rechaza y te llega una notificación; si te aceptan, el turno aparece en tu calendario.'],
        'resena' => [['resena', 'estrellas', 'calific', 'opinion', 'comentar'],
            'Puedes dejar una reseña con estrellas cuando ya visitaste la habitación (la visita debe estar aceptada y la fecha ya pasada). Así las opiniones son reales.'],
        'reportar' => [['report', 'denunci', 'sospech'],
            'Dentro de cada habitación o trabajo está el botón "Reportar". El administrador lo revisa y puede suspender la cuenta y ocultar sus publicaciones.'],
        'favoritos' => [['favorit', 'guardar', 'corazon'],
            'Toca el corazón en una habitación para guardarla. Las encuentras en "Favoritos" desde tu panel.'],
        'calendario' => [['calendario', 'agenda'],
            'En tu panel está el Calendario: ves juntos tus clases, tus turnos de trabajo aceptados y las visitas que pediste.'],
        'mensajes' => [['mensaje', 'chat', 'escribirle', 'contactar al', 'hablar con el dueno', 'hablar con la empresa'],
            'Dentro de cada habitación o trabajo toca "Enviar mensaje" para hablar con el arrendador o la empresa sin dar tu número.'],
        'horario' => [['horario', 'mis clases', 'compatible', 'no choque', 'no se cruce'],
            'En "Mi panel" marcas los turnos en los que tienes clases (mañana, tarde o noche de cada día). Luego en Trabajos activas "Solo compatibles con mi horario" y ves solo las ofertas que no se cruzan.'],
        'app' => [['instalar', 'aplicacion', 'app', 'celular'],
            'TAMPU se instala como app desde el navegador: en Chrome toca el menú ⋮ → "Instalar aplicación" (en iPhone: Compartir → "Agregar a inicio").'],
        'altura' => [['altura', 'soroche', 'mal de altura', 'oxigeno'],
            'Cusco está a unos 3 400 m. Los primeros días camina despacio, toma mate de coca o agua, come ligero y evita el alcohol. Si el dolor de cabeza fuerte no pasa, acude a un centro de salud.'],
        'frio' => [['frio', 'clima', 'abrigo', 'temperatura', 'lluvia'],
            'De día suele haber sol, pero las noches son frías casi todo el año (pueden bajar a 0 °C entre junio y agosto). Pregunta si el cuarto tiene agua caliente y lleva frazadas. De diciembre a marzo llueve bastante.'],
        'transporte' => [['bus', 'combi', 'transporte', 'pasaje', 'como llego', 'movilidad', 'carro'],
            'En Cusco el pasaje en bus o combi cuesta alrededor de S/ 1 a S/ 1.50. Por la Av. de la Cultura pasan muchas líneas hacia la UNSAAC, la U. Andina (Larapa) y San Jerónimo; para la U. Continental (Angostura) hay buses por la Vía Expresa. En el detalle de cada cuarto ves cuánto demoras a cada universidad.'],
        'costo_vida' => [['costo de vida', 'cuanto gasto', 'menu', 'presupuesto', 'cuanto necesito'],
            'Como referencia: un menú cuesta entre S/ 8 y S/ 12, el pasaje alrededor de S/ 1, y un cuarto individual entre S/ 250 y S/ 450 al mes. Muchos estudiantes gastan entre S/ 700 y S/ 1 100 al mes en total.'],
    ];

    public function responder(Request $request)
    {
        $request->validate(['mensaje' => 'required|string|max:200']);
        $original = trim($request->mensaje);
        $texto = ' ' . $this->normalizar($original) . ' ';

        // 1. pedir una persona
        if ($this->tiene($texto, ['asesor', 'hablar con alguien', 'hablar con una persona', 'persona real', 'humano', 'soporte', 'atencion al cliente'])) {
            return $this->pedirAsesor($request);
        }

        // 2. conversacion corta
        if (preg_match('/^\s*(hola|holi|buenas|buenos dias|buenas tardes|buenas noches|hey|que tal)\W*$/', $texto)) {
            $nombre = $request->user() ? ' ' . explode(' ', $request->user()->nombre)[0] : '';

            return $this->respuesta("¡Hola$nombre! Soy Tampu 👋 Puedo buscarte cuartos (por distrito, precio, tipo o universidad), trabajos que encajen con tus clases y resolver dudas sobre vivir en Cusco.", [], self::SUGERENCIAS);
        }
        if ($this->tiene($texto, [' gracias', 'chau', 'adios', 'hasta luego', 'listo eso es todo'])) {
            return $this->respuesta('¡De nada! Que te vaya muy bien en tus estudios. Si necesitas algo más, aquí estaré.');
        }
        if ($this->tiene($texto, ['quien eres', 'que eres', 'eres un bot', 'eres humano', 'como te llamas'])) {
            return $this->respuesta('Soy Tampu, el asistente virtual de TAMPU. Respondo al instante con la información de la página; si necesitas a una persona, escribe "hablar con un asesor".', [], self::SUGERENCIAS);
        }
        if ($this->tiene($texto, ['que puedes hacer', 'como funcionas', 'que haces']) || trim($texto) === 'ayuda') {
            return $this->respuesta('Puedo: buscar cuartos ("minidepa en Wanchaq hasta 600"), buscar roomies o casas de estudiantes, mostrarte trabajos ("trabajo de mozo los fines de semana"), decirte precios promedio por distrito y darte consejos para no caer en estafas.',
                [], ['Precios promedio por distrito', 'Busco roomies', 'Trabajo por horas los sábados', 'Qué revisar en la visita']);
        }

        // 3. que pide: datos que se reconocen en el mensaje
        $datos = $this->extraer($texto);
        $quiereCuarto = $this->tiene($texto, ['cuarto', 'habitacion', 'alquil', 'vivir', 'vivienda', 'hospedaje', 'donde quedarme', 'roomie', 'rumi', 'minidepa', 'depa', 'residencia', 'pension', 'casa de estudiantes'])
            || isset($datos['tipo']) || isset($datos['servicios']);
        $quiereTrabajo = $this->tiene($texto, ['trabajo', 'empleo', 'chamba', 'cachuelo', 'practica', 'chambear', 'trabajar', 'medio tiempo', 'por horas', 'oferta'])
            || isset($datos['rubro']);

        // 4. precios promedio y cantidades
        if ($this->tiene($texto, ['promedio', 'cuanto cuesta', 'cuanto cuestan', 'precio normal', 'cuanto se paga', 'precios por distrito', 'precio de ', 'precios de', 'cuanto sale'])
            && ! $quiereTrabajo) {
            return $this->precios($datos);
        }

        // 5. dudas frecuentes (si pregunta "como...", "que...", etc. va primero la duda)
        $faq = $this->buscarPregunta($texto);
        // estas dudas ganan aunque el mensaje mencione "cuarto" o "trabajo" (ej. "quiero publicar mi cuarto")
        $fuertes = ['publicar', 'registro', 'contrasena', 'dos_pasos', 'precio_tampu', 'pedir_visita', 'postular', 'resena', 'reportar', 'favoritos', 'contrato', 'visita'];
        if ($faq && in_array($faq['clave'], $fuertes)) {
            return $this->respuestaFaq($faq);
        }
        $esPregunta = $this->tiene($texto, ['como ', 'que ', 'puedo', 'debo', 'donde ', 'para que', 'cuanto', 'es seguro', 'por que']);
        if ($faq && ($esPregunta || (! $quiereCuarto && ! $quiereTrabajo))) {
            return $this->respuestaFaq($faq);
        }

        // 6. busquedas; si solo agrega un detalle ("solo con baño propio") se suma a la busqueda anterior
        $ultima = $request->session()->get('asistente.ultima');
        $nombraCuarto = $this->tiene($texto, ['cuarto', 'habitacion', 'alquil', 'vivienda', 'hospedaje', 'roomie', 'rumi', 'depa', 'residencia', 'pension', 'casa de estudiantes']);
        $nombraTrabajo = $this->tiene($texto, ['trabajo', 'empleo', 'chamba', 'cachuelo', 'practica', 'oferta']);
        if ($this->tiene($texto, ['cualquier distrito', 'otro distrito', 'todos los distritos']) && $ultima) {
            $previos = $ultima['datos'];
            unset($previos['distrito']);

            return $ultima['busqueda'] === 'trabajos' ? $this->buscarTrabajos($request, $texto, $previos) : $this->buscarHabitaciones($request, $previos);
        }
        if ($quiereTrabajo && ! $quiereCuarto) {
            $base = ! $nombraTrabajo && $ultima && $ultima['busqueda'] === 'trabajos' ? $ultima['datos'] : [];

            return $this->buscarTrabajos($request, $texto, array_merge($base, $datos));
        }
        if ($quiereCuarto) {
            $base = ! $nombraCuarto && $ultima && $ultima['busqueda'] === 'cuartos' ? $ultima['datos'] : [];
            if (isset($base['servicios'], $datos['servicios'])) {
                $datos['servicios'] = array_values(array_unique(array_merge($base['servicios'], $datos['servicios'])));
            }

            return $this->buscarHabitaciones($request, array_merge($base, $datos));
        }

        // 7. seguimiento de la ultima busqueda: "¿y en Santiago?", "más baratos", "cerca de la UNSAAC"
        $esSeguimiento = $datos || $this->tiene($texto, ['mas barato', 'mas baratos', 'mas economico', 'otro', 'otros', 'mas opciones', ' y ']);
        if ($ultima && $esSeguimiento) {
            if ($ultima['busqueda'] === 'trabajos') {
                return $this->buscarTrabajos($request, $texto, array_merge($ultima['datos'], $datos));
            }
            if ($this->tiene($texto, ['mas barato', 'mas baratos', 'mas economico']) && isset($ultima['datos']['precio'])) {
                $datos['precio'] = max(150, $ultima['datos']['precio'] - 80);
            }

            return $this->buscarHabitaciones($request, array_merge($ultima['datos'], $datos));
        }

        if ($faq) {
            return $this->respuestaFaq($faq);
        }

        return $this->respuesta('No te entendí del todo 🤔. Puedes escribirme cosas como "cuarto con baño propio cerca de la Andina", "busco roomies en Wanchaq", "trabajo de mozo los sábados" o "¿cuánto cuesta un cuarto en Santiago?".',
            [], self::SUGERENCIAS);
    }

    // ---------------------------------------------------------------- lectura del mensaje

    private function extraer(string $texto): array
    {
        $datos = [];
        foreach (self::DISTRITOS as $distrito => $palabras) {
            if ($this->tiene($texto, $palabras)) {
                $datos['distrito'] = $distrito;
                break;
            }
        }
        foreach (self::UNIVERSIDADES as $uni => $palabras) {
            if ($this->tiene($texto, $palabras)) {
                $datos['uni'] = $uni;
                break;
            }
        }
        foreach (self::TIPOS as $tipo => $palabras) {
            if ($this->tiene($texto, $palabras)) {
                $datos['tipo'] = $tipo;
                break;
            }
        }
        foreach (self::SERVICIOS as $servicio => $palabras) {
            if ($this->tiene($texto, $palabras)) {
                $datos['servicios'][] = $servicio;
            }
        }

        // precio: "entre 200 y 300", "hasta 300", "300 soles", "s/ 300"
        if (preg_match('/entre\s*s?\/?\s*(\d{2,4})\s*y\s*s?\/?\s*(\d{2,4})/', $texto, $m)) {
            $datos['precio_min'] = (int) $m[1];
            $datos['precio'] = (int) $m[2];
        } elseif (preg_match('/(\d{3,4})/', $texto, $m) && (int) $m[1] >= 100 && (int) $m[1] <= 3000) {
            $datos['precio'] = (int) $m[1];
        } elseif ($this->tiene($texto, ['barato', 'economico', 'poco dinero', 'no tengo mucha plata', 'accesible'])) {
            $datos['precio'] = 300;
        }

        // trabajo: modalidad, turnos y rubro
        if ($this->tiene($texto, ['practica', 'practicante'])) {
            $datos['modalidad'] = 'practicas';
        } elseif ($this->tiene($texto, ['por horas', 'por hora'])) {
            $datos['modalidad'] = 'por horas';
        } elseif ($this->tiene($texto, ['medio tiempo', 'part time', 'part-time'])) {
            $datos['modalidad'] = 'medio tiempo';
        }
        if ($this->tiene($texto, ['manana', 'temprano'])) {
            $datos['turno'] = 'manana';
        } elseif ($this->tiene($texto, ['tarde'])) {
            $datos['turno'] = 'tarde';
        } elseif ($this->tiene($texto, ['noche', 'nocturno'])) {
            $datos['turno'] = 'noche';
        }
        if ($this->tiene($texto, ['fin de semana', 'fines de semana', 'finde', 'sabado y domingo'])) {
            $datos['dias'] = ['sabado', 'domingo'];
        } elseif ($this->tiene($texto, ['sabado'])) {
            $datos['dias'] = ['sabado'];
        } elseif ($this->tiene($texto, ['domingo'])) {
            $datos['dias'] = ['domingo'];
        }
        foreach (self::RUBROS as $clave => $buscar) {
            if (str_contains($texto, $clave)) {
                $datos['rubro'] = $buscar;
                $datos['rubro_texto'] = trim($clave);
                break;
            }
        }

        return $datos;
    }

    // ---------------------------------------------------------------- cuartos

    private function buscarHabitaciones(Request $request, array $datos)
    {
        $request->session()->put('asistente.ultima', ['busqueda' => 'cuartos', 'datos' => $datos]);

        $consulta = Habitacion::publicadas()->withAvg('resenas', 'estrellas');
        $filtros = [];
        $partes = [];

        if (isset($datos['tipo'])) {
            $consulta->where('tipo', $datos['tipo']);
            $filtros['tipo'] = $datos['tipo'];
            $partes[] = mb_strtolower(Habitacion::TIPOS[$datos['tipo']]);
        }
        if (isset($datos['distrito'])) {
            $consulta->where('distrito', $datos['distrito']);
            $filtros['distrito'] = $datos['distrito'];
            $partes[] = 'en ' . $datos['distrito'];
        }
        if (isset($datos['uni'])) {
            $consulta->where('cerca_de', $datos['uni']);
            $filtros['cerca_de'] = $datos['uni'];
            $partes[] = 'cerca de la ' . $datos['uni'];
        }
        if (isset($datos['precio'])) {
            $consulta->where('precio', '<=', $datos['precio']);
            $filtros['precio_max'] = $datos['precio'];
            $partes[] = 'hasta S/ ' . $datos['precio'];
        }
        if (isset($datos['precio_min'])) {
            $consulta->where('precio', '>=', $datos['precio_min']);
            $partes[count($partes) - 1] = 'entre S/ ' . $datos['precio_min'] . ' y S/ ' . $datos['precio'];
        }
        foreach ($datos['servicios'] ?? [] as $s) {
            $consulta->where('servicios', 'like', '%' . $s . '%');
            $partes[] = 'con ' . mb_strtolower($s);
        }

        $total = (clone $consulta)->count();
        $lista = $consulta->orderBy('precio')->take(4)->get();
        $resumen = $partes ? ' (' . implode(', ', $partes) . ')' : '';

        if ($lista->isEmpty()) {
            $sug = [];
            if (isset($datos['precio'])) {
                $sug[] = 'Hasta S/ ' . ($datos['precio'] + 100);
            }
            if (isset($datos['distrito'])) {
                $sug[] = 'En cualquier distrito';
            }
            $sug[] = 'Ver todos los tipos de vivienda';

            return $this->respuesta("No encontré viviendas$resumen 😕. Prueba subir un poco el precio o buscar en otro distrito.",
                [['texto' => 'Ver todas las habitaciones', 'url' => route('habitaciones.index')]], $sug);
        }

        $enlaces = $lista->map(fn ($h) => [
            'texto' => $h->titulo . ' · ' . $h->distrito . ' · S/ ' . (int) $h->precio
                . ($h->resenas_avg_estrellas ? ' · ★ ' . number_format($h->resenas_avg_estrellas, 1) : ''),
            'url' => route('habitaciones.show', $h),
        ])->all();
        if ($total > $lista->count()) {
            $enlaces[] = ['texto' => "Ver las $total opciones en el mapa", 'url' => route('habitaciones.index', $filtros)];
        }

        $consejo = isset($datos['tipo']) && $datos['tipo'] === 'roomies'
            ? ' Tip: antes de mudarte, conversa con tus futuros roomies sobre horarios, visitas y limpieza.'
            : '';
        $intro = $total === 1 ? "Encontré 1 opción$resumen:" : "Encontré $total opciones$resumen.$consejo Estas son las más económicas:";
        if ($total === 1) {
            $intro .= $consejo;
        }

        // sugerencias para seguir afinando
        $sug = [];
        $sinBanoPropio = in_array($datos['tipo'] ?? '', ['roomies', 'compartida', 'residencia']);
        if (! $sinBanoPropio && (! isset($datos['servicios']) || ! in_array('Baño propio', $datos['servicios']))) {
            $sug[] = 'Solo con baño propio';
        }
        if (isset($datos['precio'])) {
            $sug[] = 'Más baratos';
        }
        if (! isset($datos['uni']) && ! isset($datos['distrito'])) {
            $sug[] = 'Cerca de la UNSAAC';
        }
        if (! isset($datos['tipo'])) {
            $sug[] = 'Busco roomies';
        }
        $sug[] = 'Qué revisar en la visita';

        return $this->respuesta($intro, $enlaces, array_slice($sug, 0, 3));
    }

    private function precios(array $datos)
    {
        $consulta = Habitacion::publicadas();
        if (isset($datos['tipo'])) {
            $consulta->where('tipo', $datos['tipo']);
        }
        $filas = $consulta->selectRaw('distrito, round(avg(precio)) as promedio, min(precio) as minimo, count(*) as total')
            ->groupBy('distrito')->orderBy('promedio')->get();

        if ($filas->isEmpty()) {
            return $this->respuesta('Aún no tengo suficientes publicaciones para calcular precios.');
        }
        $tipo = isset($datos['tipo']) ? ' (' . mb_strtolower(Habitacion::TIPOS[$datos['tipo']]) . ')' : '';

        if (isset($datos['distrito']) && $f = $filas->firstWhere('distrito', $datos['distrito'])) {
            return $this->respuesta("En {$f->distrito}$tipo el alquiler promedio es S/ " . (int) $f->promedio . ' al mes, desde S/ ' . (int) $f->minimo . " ({$f->total} publicaciones).",
                [['texto' => "Ver cuartos en {$f->distrito}", 'url' => route('habitaciones.index', ['distrito' => $f->distrito])]],
                ['Cuartos en ' . $f->distrito . ' hasta ' . (int) $f->promedio, 'Precios por distrito']);
        }

        $lineas = $filas->map(fn ($f) => "• {$f->distrito}: S/ " . (int) $f->promedio . ' (desde S/ ' . (int) $f->minimo . ')')->implode("\n");

        return $this->respuesta("Precio promedio mensual por distrito$tipo:\n$lineas\nSantiago y San Jerónimo suelen ser los más económicos; el centro y Wanchaq, los más caros.",
            [], ['Cuartos en Santiago hasta 300', 'Precio de minidepas', 'Busco roomies']);
    }

    // ---------------------------------------------------------------- trabajos

    private function buscarTrabajos(Request $request, string $texto, array $datos)
    {
        $request->session()->put('asistente.ultima', ['busqueda' => 'trabajos', 'datos' => $datos]);

        $usuario = $request->user();
        $clases = $usuario && $usuario->rol === 'estudiante' ? $usuario->horarios : collect();
        $soloCompatibles = $clases->isNotEmpty() && ! $this->tiene($texto, ['todos los trabajos', 'cualquier horario']);

        $consulta = Trabajo::publicadas()->with('horarios');
        $partes = [];
        $filtros = [];
        if (isset($datos['modalidad'])) {
            $consulta->where('modalidad', $datos['modalidad']);
            $filtros['modalidad'] = $datos['modalidad'];
            $partes[] = $datos['modalidad'] === 'practicas' ? 'prácticas' : $datos['modalidad'];
        }
        if (isset($datos['distrito'])) {
            $consulta->where('distrito', $datos['distrito']);
            $filtros['distrito'] = $datos['distrito'];
            $partes[] = 'en ' . $datos['distrito'];
        }
        if (isset($datos['rubro'])) {
            $consulta->where(function ($q) use ($datos) {
                foreach ($datos['rubro'] as $palabra) {
                    $q->orWhereLike('titulo', "%$palabra%")->orWhereLike('descripcion', "%$palabra%");
                }
            });
            $partes[] = 'de ' . $datos['rubro_texto'];
        }
        if (isset($datos['turno']) || isset($datos['dias'])) {
            $consulta->whereHas('horarios', function ($q) use ($datos) {
                if (isset($datos['turno'])) {
                    $q->where('turno', $datos['turno']);
                }
                if (isset($datos['dias'])) {
                    $q->whereIn('dia', $datos['dias']);
                }
            });
            $partes[] = trim((isset($datos['dias']) ? (count($datos['dias']) > 1 ? 'fines de semana' : 'los ' . $datos['dias'][0] . 's') : '')
                . (isset($datos['turno']) ? ' en la ' . ['manana' => 'mañana', 'tarde' => 'tarde', 'noche' => 'noche'][$datos['turno']] : ''));
        }

        $trabajos = $consulta->latest()->get();
        if ($soloCompatibles) {
            $ocupado = $clases->map(fn ($h) => $h->dia . '-' . $h->turno)->all();
            $trabajos = $trabajos->reject(fn ($t) => $t->horarios->contains(fn ($h) => in_array($h->dia . '-' . $h->turno, $ocupado)));
            $filtros['compatible'] = 1;
        }

        $resumen = $partes ? ' (' . implode(', ', array_filter($partes)) . ')' : '';
        if ($trabajos->isEmpty()) {
            return $this->respuesta("No encontré ofertas$resumen" . ($soloCompatibles ? ' que no choquen con tus clases' : '') . '. Prueba con otro horario o rubro.',
                [['texto' => 'Ver todas las ofertas', 'url' => route('trabajos.index')]], ['Trabajos por horas', 'Prácticas', 'Trabajo los fines de semana']);
        }

        $enlaces = $trabajos->take(4)->map(fn ($t) => [
            'texto' => $t->titulo . ' · ' . $t->empresa . ' · ' . $t->pagoTexto(),
            'url' => route('trabajos.show', $t),
        ])->values()->all();
        if ($trabajos->count() > 4) {
            $enlaces[] = ['texto' => 'Ver las ' . $trabajos->count() . ' ofertas', 'url' => route('trabajos.index', $filtros)];
        }

        if ($soloCompatibles) {
            $intro = 'Encontré ' . $trabajos->count() . " ofertas$resumen que NO chocan con tus clases:";
        } elseif ($usuario && $usuario->rol === 'estudiante') {
            $intro = 'Encontré ' . $trabajos->count() . " ofertas$resumen. Si marcas tu horario de clases en tu panel, te muestro solo las compatibles.";
        } else {
            $intro = 'Encontré ' . $trabajos->count() . " ofertas$resumen. Inicia sesión como estudiante y registra tu horario para ver solo las que encajan con tus clases.";
        }

        return $this->respuesta($intro, $enlaces, ['Trabajo los fines de semana', 'Prácticas', 'Trabajo de profesor']);
    }

    // ---------------------------------------------------------------- otros

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

    private function buscarPregunta(string $texto): ?array
    {
        // gana la duda con mas palabras encontradas
        $mejor = null;
        $puntos = 0;
        foreach (self::PREGUNTAS as $clave => $p) {
            $n = 0;
            foreach ($p[0] as $palabra) {
                if (str_contains($texto, $palabra)) {
                    $n += strlen($palabra);
                }
            }
            if ($n > $puntos) {
                $puntos = $n;
                $mejor = $p + ['clave' => $clave];
            }
        }

        return $mejor;
    }

    private function respuestaFaq(array $faq)
    {
        $enlaces = array_map(fn ($e) => ['texto' => $e['texto'], 'url' => route($e['ruta'])], $faq[2] ?? []);

        return $this->respuesta($faq[1], $enlaces, ['Buscar cuarto', 'Buscar trabajo', 'Hablar con un asesor']);
    }

    private function respuesta(string $texto, array $enlaces = [], array $sugerencias = [])
    {
        return response()->json(compact('texto', 'enlaces', 'sugerencias'));
    }

    private function normalizar(string $texto): string
    {
        return preg_replace('/\s+/', ' ', Str::lower(Str::ascii($texto)));
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
