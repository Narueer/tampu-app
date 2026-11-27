<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Services\AsistenteIA;
use App\Models\Trabajo;
use App\Support\Lugares;
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

    // universidades e institutos: la lista completa (con cómo los escribe la gente) está en App\Support\Lugares

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

    // palabras cortas que solo cuentan como palabra completa ("bus" no debe coincidir con "busco")
    const ENTERAS = ['bus', 'chat', 'app', 'menu', 'carro', 'clave', 'mesa', 'cafe', 'guia', 'caja', 'venta', 'tour', 'perro', 'gato',
        'uac', 'ruc', 'sello', 'depa', 'civil', 'combi', 'clima', 'frio', 'rumi', 'oferta', 'mozo', 'hotel', 'hostal', 'ingles', 'redes',
        'ia', 'gpt', 'ok', 'si', 'no', 'ya', 'hi', 'ola', 'lima', 'ica', 'ate', 'usa', 'pareja', 'novia', 'novio', 'hora', 'casero',
        'room', 'rent', 'job', 'please', 'speak', 'english', 'hello', 'thanks', 'need', 'info', 'detalles', 'tour', 'lote'];

    // lugares donde TAMPU no funciona (para responder "cuartos en Lima" con amabilidad)
    const LUGARES = [
        'Lima' => ['lima', 'miraflores', 'san isidro', 'surco', 'barranco', 'los olivos', 'la molina', 'san juan de lurigancho', 'sjl', 'callao',
            'jesus maria', 'lince', 'pueblo libre', 'chorrillos', 'villa el salvador', 'san borja', 'rimac', 'comas'],
        'Arequipa' => ['arequipa'], 'Puno' => ['puno'], 'Juliaca' => ['juliaca'], 'Tacna' => ['tacna'], 'Trujillo' => ['trujillo'],
        'Chiclayo' => ['chiclayo'], 'Piura' => ['piura'], 'Ica' => ['ica'], 'Huancayo' => ['huancayo'], 'Ayacucho' => ['ayacucho'],
        'Abancay' => ['abancay'], 'Andahuaylas' => ['andahuaylas'], 'Puerto Maldonado' => ['puerto maldonado', 'madre de dios'],
        'Iquitos' => ['iquitos'], 'Cajamarca' => ['cajamarca'], 'Huaraz' => ['huaraz'], 'Tarapoto' => ['tarapoto'], 'Pucallpa' => ['pucallpa'],
        'Chimbote' => ['chimbote'], 'Moquegua' => ['moquegua'], 'Huánuco' => ['huanuco'], 'Tumbes' => ['tumbes'],
        'Sicuani' => ['sicuani'], 'Quillabamba' => ['quillabamba'], 'Espinar' => ['espinar'], 'Urubamba' => ['urubamba'], 'Calca' => ['calca'],
        'Pisac' => ['pisac'], 'Ollantaytambo' => ['ollantaytambo'], 'Chumbivilcas' => ['chumbivilcas'],
    ];
    const PAISES = ['venezuela', 'venezolan', 'colombia', 'colombian', 'bolivia', 'bolivian', 'chile', 'chilen', 'argentin', 'ecuador',
        'ecuatorian', 'brasil', 'mexic', 'espana', 'estados unidos', 'extranjer', 'carnet de extranjeria', 'pasaporte', 'no tengo dni'];

    const CHISTES = [
        'En Cusco hay dos estaciones: "hace frío" y "hace más frío" 🥶. Por eso siempre pregunta si el cuarto tiene agua caliente.',
        'Mi antiguo casero me dijo que el cuarto tenía vista al mar… era un póster en la pared 🌊. En TAMPU las fotos son reales.',
        '—¿Cuánto cuesta el cuarto? —S/ 300. —¿Y si lo comparto? —S/ 300 cada uno 😅. Por eso conviene comparar precios aquí.',
        '¿Por qué el estudiante llevó una escalera a la universidad? Porque le dijeron que iba a estudiar en el nivel superior 🎓.',
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
        'precio_tampu' => [['cuesta usar', 'es gratis', 'cobran', 'comision', 'pagar por usar', 'cuesta tampu', 'cuesta la pagina', 'cuesta la app', 'cuesta registrarme', 'cuesta publicar'],
            'Usar TAMPU es gratis para estudiantes, arrendadores y empresas. No cobramos comisión. Si alguien te pide pagar "por TAMPU", repórtalo.'],
        'contrasena' => [['contrasena', 'olvide', 'clave', 'password'],
            'En "Iniciar sesión" toca "¿Olvidaste tu contraseña?" y te enviamos un enlace a tu correo para crear una nueva.'],
        'dos_pasos' => [['dos pasos', '2 pasos', 'autenticador', 'google authenticator', 'codigo de seguridad'],
            'En "Mi perfil" → "Seguridad de la cuenta" puedes activar la verificación en dos pasos con Google o Microsoft Authenticator. Así, aunque alguien sepa tu contraseña, no podrá entrar.'],
        'pedir_visita' => [['solicitar visita', 'pedir visita', 'agendar', 'como visito', 'quiero visitar', 'pido una visita', 'pedir una visita',
            'solicito una visita', 'solicitar una visita', 'visitar el cuarto', 'ir a ver el cuarto', 'como veo el cuarto'],
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
        'mascotas' => [['mascota', 'perro', 'perrito', 'gato', 'gatito', 'mi llama', 'una llama', 'con llama', 'mis llamas', 'llamita', 'alpaca', 'cuyes', 'mi cuy', 'un cuy', 'conejo', 'hamster', 'loro', 'perico', 'tortuga', 'mi pez', 'un pez', 'peces', 'pececito', 'gallina', 'animal', 'animales', 'michi', 'firulais'],
            'Depende de cada arrendador: algunos aceptan mascotas pequeñas y otros no. Antes de pedir la visita, escríbele desde "Enviar mensaje" en la habitación y pregúntale; así no pierdes el viaje.'],
        'parejas' => [['pareja', 'con mi enamorad', 'con mi novi', 'con mi esposa', 'con mi esposo', 'con mi hijo', 'con mi hija', 'con mi familia'],
            'Algunos arrendadores aceptan parejas o familias, sobre todo en minidepartamentos. Busca "minidepa" y confirma por mensaje con el dueño antes de visitar.',
            [['texto' => 'Ver habitaciones', 'ruta' => 'habitaciones.index']]],
        'borrar_cuenta' => [['borrar mi cuenta', 'borro mi cuenta', 'elimino mi cuenta', 'borrar la cuenta', 'eliminar mi cuenta', 'darme de baja', 'cerrar mi cuenta', 'borrar cuenta', 'eliminar cuenta', 'borrar mis datos'],
            'Por ahora no hay un botón para borrar la cuenta. Escríbenos desde Contacto pidiéndolo y el equipo la desactiva; tus publicaciones dejarán de mostrarse.',
            [['texto' => 'Ir a Contacto', 'ruta' => 'contacto']]],
        'universidades' => [['que universidades', 'universidades hay', 'universidades de cusco', 'cuales universidades', 'que institutos', 'institutos hay', 'cuales institutos', 'donde estudiar'],
            "Te busco cuarto cerca de estos 13 lugares:\n🎓 Universidades: UNSAAC, U. Andina, U. Continental y UTEA.\n🛠️ Institutos: Túpac Amaru, Khipu, Antonio Lorena y Tuinen Star.\n📚 Pedagógico: Pukllasunchis.\n🍳 Turismo: CENFOTUR.\n🔧 Técnicos: SENATI, SENCICO y Leandro Alviña (música).\nEscribe por ejemplo \"cuarto cerca del SENATI\". Si tu instituto no está, dime en qué zona queda y te busco por distrito."],
    ];

    public function responder(Request $request)
    {
        $request->validate(['mensaje' => 'required|string|max:200']);
        $original = trim($request->mensaje);
        $this->original = $original;
        $texto = ' ' . $this->corregir($this->normalizar($original)) . ' ';

        // 0. mensajes muy cortos o sin sentido: emojis, risas, "ok", teclazos
        if ($r = $this->charlaCorta($request, $texto)) {
            return $r;
        }
        // "hola buenas tardes, busco cuarto" -> se responde al saludo solo si no pide nada más
        $sinSaludo = preg_replace('/^\s*((buenas tardes|buenas noches|buenos dias|buen dia|hola|holi|ola|buenas|hey|hi|hello|alo|saludos|que tal|que hay|amigo|amiga|tampu|bot)[\s,!.¡]*)+/', ' ', $texto);
        if (trim($sinSaludo) !== '' && trim($sinSaludo) !== trim($texto)) {
            $texto = ' ' . trim($sinSaludo) . ' ';
        }

        // 1. pedir una persona
        if ($this->tiene($texto, ['asesor', 'hablar con alguien', 'hablar con una persona', 'persona real', 'humano', 'soporte', 'atencion al cliente'])) {
            return $this->pedirAsesor($request);
        }

        // 2. conversacion corta
        if (preg_match('/^\s*((buenas tardes|buenas noches|buenos dias|buen dia|hola|holi|ola|buenas|hey|hi|hello|alo|saludos|que tal|que hay|tampu|bot)[\s,!.¡]*)+$/', $texto)) {
            $nombre = $request->user() ? ' ' . explode(' ', $request->user()->nombre)[0] : '';

            return $this->respuesta("¡Hola$nombre! Soy Tampu 👋 Puedo buscarte cuartos (por distrito, precio, tipo o universidad), trabajos que encajen con tus clases y resolver dudas sobre vivir en Cusco.", [], self::SUGERENCIAS);
        }
        if (preg_match('/^\s*(hola\W*)?(como estas|como te va|todo bien|que tal estas)\W*$/', $texto)) {
            return $this->respuesta('¡Muy bien, gracias por preguntar! 😊 Listo para ayudarte. ¿Buscas cuarto, trabajo o tienes alguna duda sobre Cusco?', [], self::SUGERENCIAS);
        }
        if ($this->tiene($texto, ['gracias', 'chau', 'adios', 'hasta luego', 'nos vemos', 'listo eso es todo', 'eso es todo'])) {
            return $this->respuesta('¡De nada! Que te vaya muy bien en tus estudios. Si necesitas algo más, aquí estaré. 👋');
        }

        // 3. temas especiales: qué es la página, otras ciudades, insultos, preguntas fuera de tema…
        $datos = $this->extraer($texto);
        if (AsistenteIA::activo() && $this->mejorConIA($texto, $datos) && ($r = $this->preguntarIA($request))) {
            return $r;
        }
        if ($r = $this->problemaComun($texto)) {
            return $r;
        }
        $pideTrabajo = $this->tiene($texto, ['trabajo', 'empleo', 'chamba', 'cachuelo', 'practica', 'chambear', 'trabajar', 'medio tiempo', 'por horas', 'oferta',
            'ganar plata', 'ganar platita', 'ganar dinero', 'plata extra', 'dinero extra', 'ingresos extra', 'cachuelito']);
        $pideCuarto = $this->tiene($texto, ['cuarto', 'habitacion', 'alquil', 'vivir', 'vivienda', 'hospedaje', 'donde quedarme', 'roomie', 'rumi', 'minidepa', 'mini depa', 'depa', 'departamento', 'residencia', 'pension', 'casa de estudiantes', 'casa estudiantil', 'donde vivo', 'donde vivir', 'alojarme', 'alojamiento'])
            || (isset($datos['uni']) && ! $pideTrabajo);   // "estudio en el SENATI, ¿dónde me quedo?"
        if ($r = $this->temaEspecial($request, $texto, $datos, $pideCuarto, $pideTrabajo)) {
            return $r;
        }
        $quiereCuarto = $this->tiene($texto, ['cuarto', 'habitacion', 'alquil', 'vivir', 'vivienda', 'hospedaje', 'donde quedarme', 'roomie', 'rumi', 'minidepa', 'depa', 'residencia', 'pension', 'casa de estudiantes', 'donde vivo', 'donde vivir', 'alojarme', 'alojamiento'])
            || isset($datos['tipo']) || isset($datos['servicios']) || (isset($datos['uni']) && ! $this->tiene($texto, ['trabajo', 'chamba', 'empleo']));
        $quiereTrabajo = $this->tiene($texto, ['trabajo', 'empleo', 'chamba', 'cachuelo', 'practica', 'chambear', 'trabajar', 'medio tiempo', 'por horas', 'oferta'])
            || isset($datos['rubro']);

        // 4. precios promedio y cantidades
        if ($this->tiene($texto, ['promedio', 'cuanto cuesta', 'cuanto cuestan', 'precio normal', 'cuanto se paga', 'precios por distrito', 'precio de ', 'precios de', 'cuanto sale'])
            && ! $quiereTrabajo && ! $this->tiene($texto, ['tampu', 'usar la', 'la pagina', 'la app', 'registrarme', 'publicar', 'pasaje'])) {
            return $this->precios($datos);
        }

        // solo cambia el orden de la búsqueda anterior: "los mejor calificados", "los más recientes"
        $previa = $request->session()->get('asistente.ultima');
        if (isset($datos['orden']) && $previa && $previa['busqueda'] === 'cuartos' && ! $quiereTrabajo) {
            return $this->buscarHabitaciones($request, array_merge($previa['datos'], $datos, ['pagina' => 0]));
        }

        // 5. dudas frecuentes (si pregunta "como...", "que...", etc. va primero la duda)
        $faq = $this->buscarPregunta($texto);
        // estas dudas ganan aunque el mensaje mencione "cuarto" o "trabajo" (ej. "quiero publicar mi cuarto")
        $fuertes = ['mascotas', 'publicar', 'registro', 'contrasena', 'dos_pasos', 'precio_tampu', 'pedir_visita', 'postular', 'resena', 'reportar', 'favoritos', 'contrato', 'visita'];
        if ($faq && in_array($faq['clave'], $fuertes) && ! ($faq['clave'] === 'mascotas' && $quiereTrabajo)) {
            return $this->respuestaFaq($faq);
        }
        $esPregunta = $this->tiene($texto, ['como ', 'que ', 'puedo', 'debo', 'donde ', 'para que', 'cuanto', 'es seguro', 'por que']);
        if ($faq && ($esPregunta || (! $quiereCuarto && ! $quiereTrabajo))) {
            return $this->respuestaFaq($faq);
        }

        // 6. busquedas; si solo agrega un detalle ("solo con baño propio") se suma a la busqueda anterior
        $ultima = $request->session()->get('asistente.ultima');
        $nombraCuarto = $this->tiene($texto, ['cuarto', 'habitacion', 'alquil', 'vivienda', 'hospedaje', 'roomie', 'rumi', 'depa', 'minidepa', 'mini depa',
            'departamento', 'minidepartamento', 'residencia', 'pension', 'casa de estudiantes', 'casa estudiantil']);
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

            return $this->buscarHabitaciones($request, array_merge($base, $datos, ['pagina' => 0]));
        }

        // 7. seguimiento de la ultima busqueda: "¿y en Santiago?", "más baratos", "cerca de la UNSAAC"
        $masDeLoMismo = $this->tiene($texto, ['mas opciones', 'ver mas', 'otras opciones', 'otros', 'otras', 'siguientes', 'mas resultados', 'muestrame mas', 'hay mas', 'algo mas']);
        $esSeguimiento = $datos || $masDeLoMismo || $this->tiene($texto, ['mas barato', 'mas baratos', 'mas economico', 'otro', ' y ']);
        if ($ultima && $esSeguimiento) {
            if ($ultima['busqueda'] === 'trabajos') {
                return $this->buscarTrabajos($request, $texto, array_merge($ultima['datos'], $datos));
            }
            if ($this->tiene($texto, ['mas barato', 'mas baratos', 'mas economico']) && isset($ultima['datos']['precio'])) {
                $datos['precio'] = max(150, $ultima['datos']['precio'] - 80);
            }
            // "más opciones" pasa a la siguiente página; un filtro nuevo vuelve a la primera
            $datos['pagina'] = $masDeLoMismo && ! array_diff_key($datos, ['orden' => 1]) ? ($ultima['datos']['pagina'] ?? 0) + 1 : 0;

            return $this->buscarHabitaciones($request, array_merge($ultima['datos'], $datos));
        }

        if ($faq) {
            return $this->respuestaFaq($faq);
        }

        return $this->noEntendi($request, $texto);
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
        if ($uni = Lugares::buscar($texto)) {
            $datos['uni'] = $uni;
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
        } elseif (preg_match('/(?<![\d.,])(\d{3,4})(?![\d.,])/', $texto, $m) && (int) $m[1] >= 100 && (int) $m[1] <= 3000) {
            $datos['precio'] = (int) $m[1];
        } elseif (preg_match('/(?:s\/\.?\s*|hasta )(\d{1,2})(?!\d)|(?<!\d)(\d{1,2})\s*(?:soles|sol|lucas|luquitas)/', $texto, $m)) {
            $datos['precio'] = (int) ($m[1] !== '' ? $m[1] : $m[2]);
        } elseif ($this->tiene($texto, ['barato', 'economico', 'poco dinero', 'no tengo mucha plata', 'accesible'])) {
            $datos['precio'] = 300;
        }

        // orden de los resultados
        if ($this->tiene($texto, ['mejor calificad', 'mejor puntuad', 'mejores resenas', 'mas estrellas', 'mejor valorad', 'los mejores', 'las mejores', 'mejor opinion'])) {
            $datos['orden'] = 'estrellas';
        } elseif ($this->tiene($texto, ['mas nuevos', 'mas nuevas', 'mas recientes', 'recien publicad', 'ultimos publicad', 'ultimas publicad', 'publicados hoy', 'lo ultimo', 'lo nuevo'])) {
            $datos['orden'] = 'nuevos';
        } elseif ($this->tiene($texto, ['mas barato', 'mas baratos', 'mas economico', 'el mas barato'])) {
            $datos['orden'] = 'precio';
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
            if ($this->coincide($texto, $clave)) {
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
            Lugares::filtrar($consulta, $datos['uni']);
            $filtros['cerca_de'] = $datos['uni'];
            $partes[] = 'cerca de ' . Lugares::UNIVERSIDADES[$datos['uni']]['nombre'];
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

        $orden = $datos['orden'] ?? 'precio';
        if ($orden === 'estrellas') {
            $consulta->whereHas('resenas');
        }
        $total = (clone $consulta)->count();
        $pagina = max(0, (int) ($datos['pagina'] ?? 0));
        if ($pagina * 4 >= $total) {
            $pagina = 0;   // se acabaron: vuelve a empezar
        }
        $consulta = match ($orden) {
            'estrellas' => $consulta->orderByDesc('resenas_avg_estrellas')->orderBy('precio'),
            'nuevos' => $consulta->orderByDesc('created_at'),
            default => $consulta->orderBy('precio'),
        };
        $lista = $consulta->skip($pagina * 4)->take(4)->get();
        $resumen = $partes ? ' (' . implode(', ', $partes) . ')' : '';
        $titulo = ['estrellas' => 'las mejor calificadas', 'nuevos' => 'las publicadas más recientemente'][$orden] ?? 'las más económicas';

        // instituto sin ubicación confirmada y sin cuartos marcados: se muestran zonas céntricas y bien conectadas
        if ($lista->isEmpty() && isset($datos['uni']) && Lugares::UNIVERSIDADES[$datos['uni']]['lat'] === null && ! isset($datos['distrito'])) {
            $nombre = Lugares::UNIVERSIDADES[$datos['uni']]['nombre'];
            unset($datos['uni']);
            $this->prefijo = "Aún no tengo la ubicación exacta de $nombre, así que te muestro cuartos en zonas céntricas y bien conectadas. Revisa en el mapa de cada cuarto que te quede cerca.\n";

            return $this->buscarHabitaciones($request, $datos + ['distrito' => 'Wanchaq']);
        }
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
        $intro = $total === 1 ? "Encontré 1 opción$resumen:"
            : ($pagina > 0 ? 'Aquí van otras ' . $lista->count() . " de las $total opciones$resumen:"
                : "Encontré $total opciones$resumen.$consejo Estas son $titulo:");
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
        if ($total > ($pagina + 1) * 4) {
            array_unshift($sug, 'Más opciones');
        }
        if ($orden !== 'estrellas' && $total > 4) {
            $sug[] = 'Los mejor calificados';
        }
        $sug[] = 'Qué revisar en la visita';

        return $this->respuesta($intro, $enlaces, array_slice($sug, 0, 4));
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

    // ---------------------------------------------------------------- usuarios "difíciles"

    // texto que se antepone a la siguiente respuesta (ej. "¡Bienvenido desde Puno!")
    private string $prefijo = '';
    private bool $esFallo = false;
    private string $original = '';

    private function charlaCorta(Request $request, string $texto)
    {
        $limpio = trim(preg_replace('/[^a-z0-9 ]+/', ' ', $texto));
        $limpio = preg_replace('/\s+/', ' ', $limpio);

        // vacío, solo signos o solo emojis
        if ($limpio === '') {
            return $this->respuesta('¿Hola? 👀 Escríbeme qué necesitas: un cuarto, un trabajo o alguna duda sobre vivir en Cusco.', [], self::SUGERENCIAS);
        }
        // risas
        if (preg_match('/^((ja|je|ji|jo|ha|he|xd|lol|lmao|jsjs|js)\s*)+$/', $limpio)) {
            return $this->respuesta('😄 ¡Qué bueno verte de buen humor! ¿Te ayudo a buscar un cuarto, un trabajo o resolver alguna duda?', [], self::SUGERENCIAS);
        }
        // afirmaciones sueltas
        if (preg_match('/^(ok|okey|okay|si|sip|ya|dale|listo|bueno|vale|perfecto|claro|genial|chevere|bacan|entendido|de acuerdo|aja)( gracias)?$/', $limpio)) {
            $ultima = $request->session()->get('asistente.ultima');
            $sug = $ultima ? ['Más opciones', '¿Y más baratos?', 'Buscar trabajo', 'Hablar con un asesor'] : self::SUGERENCIAS;

            return $this->respuesta('¡Perfecto! 👍 ¿Qué más necesitas?', [], $sug);
        }
        // negaciones sueltas
        if (preg_match('/^(no|nop|nada|no gracias|ninguno|ninguna|nel)$/', $limpio)) {
            return $this->respuesta('Está bien 🙂. Si luego necesitas algo, aquí estaré.');
        }
        // teclazos: "asdfgh", "jkjkjk", palabras sin vocales
        $palabras = explode(' ', $limpio);
        $raras = array_filter($palabras, fn ($p) => strlen($p) >= 4 && (! preg_match('/[aeiou]/', $p)
            || preg_match('/(asd|sdf|dsa|qwe|zxc|hjk|dfg|fgh|wer|ert|rty|uio|jkj|ghg|bla(bla)+|lalala|ñañaña)/', $p)
            || preg_match('/[bcdfghjklmnpqrstvwxz]{5,}/', $p)));
        if ($raras && count($raras) >= max(1, count($palabras) / 2)) {
            return $this->respuesta('Creo que se escribió algo por accidente 😅. Cuéntame con tus palabras qué buscas, por ejemplo "cuarto barato en Santiago".', [], self::SUGERENCIAS);
        }

        return null;
    }

    private function temaEspecial(Request $request, string $texto, array &$datos, bool $pideCuarto, bool $pideTrabajo)
    {
        $hayBusqueda = $pideCuarto || $pideTrabajo;

        // crisis: siempre primero
        if ($this->tiene($texto, ['suicid', 'matarme', 'quitarme la vida', 'no quiero vivir', 'hacerme dano', 'me quiero morir', 'quiero morirme', 'quisiera morir'])
            && ! preg_match('/morir(me)? de (frio|hambre|risa|sueno|calor|verguenza|aburrimiento)/', $texto)) {
            return $this->respuesta("Lamento mucho que te sientas así 💛. No estás solo/a y lo que sientes importa.\nPor favor habla ahora con alguien: en Perú puedes llamar gratis a la Línea 113, opción 5 (salud mental), a cualquier hora. Si estás en peligro inmediato, llama al 106 (SAMU).\nTambién puedes acercarte al área de bienestar de tu universidad o contarle a alguien de confianza.");
        }

        // insultos: responder con calma
        if ($this->tiene($texto, ['tonto', 'tonta', 'idiota', 'estupid', 'inutil', 'basura', 'mierda', 'ctm', 'csm', 'conchatuma', 'huevon', 'cojudo',
            'imbecil', 'maldito', 'carajo', 'puta', 'puto', 'pendejo', 'no sirves', 'bot malo', 'no entiendes nada', 'burro', 'pesimo'])) {
            return $this->respuesta('Uy, parece que no te estoy ayudando bien 😅. Perdón. Probemos otra vez: dime qué buscas (un cuarto, un trabajo o una duda) o escribe "asesor" para hablar con una persona del equipo.',
                [], ['Buscar cuarto', 'Buscar trabajo', 'Hablar con un asesor']);
        }

        // identidad: "eres chatgpt", "eres una ia", "eres humano"
        if ($this->tiene($texto, ['chatgpt', 'gpt', 'inteligencia artificial', 'eres una ia', 'eres ia', 'eres un robot', 'eres robot', 'eres real',
            'eres humano', 'eres una persona', 'quien eres', 'que eres', 'eres un bot', 'como te llamas', 'tu nombre'])) {
            return $this->respuesta('Soy Tampu 🤖, el asistente virtual de TAMPU. No soy ChatGPT ni una persona: el equipo me programó para entender preguntas sobre cuartos, trabajos y vida estudiantil en Cusco, y respondo con la información real de la página. Si necesitas a una persona, escribe "asesor".',
                [], self::SUGERENCIAS);
        }

        // datos personales del usuario: "¿me aceptaron la visita?", "¿tengo mensajes?"
        // doble sentido o contenido sexual: límite claro, aunque venga junto a "cuarto"
        if ($this->tiene($texto, ['kachar', 'cachar', 'kacha', 'cacha rico', 'chapar', 'tirar con', 'telo', 'hotel por horas', 'cuarto por horas', 'cuartos por horas',
            'alquiler por horas', 'por un rato', 'jugar al papa', 'jugamos al papa', 'juguemos al papa', 'papa y la mama', 'papi y mami', 'hacer el amor', 'hacer cositas',
            'hacer travesuras', 'noche de pasion', 'sexo', 'sexual', 'porno', 'xxx', 'calato', 'calata', 'desnud', 'pack', 'nudes', 'prostitu', 'escort', 'putas', 'pajear'])) {
            return $this->respuesta('Jaja, por ahí no va la cosa 🙈. TAMPU es para estudiantes que buscan dónde vivir o un trabajo en Cusco: no alquilamos cuartos por horas.'
                . ' Si lo que quieres es vivir con tu pareja, eso sí: algunos arrendadores aceptan parejas, sobre todo en minidepartamentos.',
                [], ['Minidepa para vivir en pareja', 'Cuartos en Wanchaq hasta 400', 'Trabajos compatibles']);
        }
        // le terminaron: empatía primero
        if ($this->tiene($texto, ['me dejo', 'me termino', 'terminamos', 'me engano', 'me engana', 'me puso los cuernos', 'me saco la vuelta'])
            && $this->tiene($texto, ['novi', 'enamorad', 'pareja', 'flaca', 'flaco', 'hembrita', 'jerma', 'gila', 'esposa', 'esposo'])) {
            return $this->respuesta('Uf, lo siento mucho 💛. Date tu tiempo; hablarlo con un amigo o con tu familia ayuda bastante.'
                . ' Si con esto necesitas mudarte o cambiar de cuarto, aquí estoy para ayudarte a buscar algo rápido.', [], ['Cuarto individual', 'Cuartos en Wanchaq hasta 400', 'Hablar con un asesor']);
        }
        // vivir con la pareja (en jerga también): pregunta válida
        if ($this->tiene($texto, ['en pareja', 'para pareja', 'para parejas', 'con pareja', 'mi hembrita', 'mi hembra', 'mi flaca', 'mi flaco', 'mi jerma', 'mi gila', 'mi chica', 'mi chico', 'mi mujer', 'mi marido', 'mi pareja',
            'y mi enamorad', 'y mi novi', 'con mi enamorad', 'con mi novi', 'mi enamorada', 'mi enamorado', 'mi novia', 'mi novio', 'vivir juntos', 'convivir con'])) {
            if (! $pideCuarto && ! $this->tiene($texto, ['estar con', 'quedarme', 'vivir', 'mudarme', 'mudarnos', 'cuarto', 'depa'])) {
                return $this->respuestaFaq(self::PREGUNTAS['parejas']);
            }
            $datos['tipo'] = 'minidepartamento';
            $this->prefijo = "Para vivir en pareja lo que más conviene es un minidepa. Algunos dueños aceptan parejas: confírmalo por mensaje antes de la visita.\n";

            return $this->buscarHabitaciones($request, $datos);
        }
        if ($r = $this->misDatos($request, $texto)) {
            return $r;
        }
        // recién llegado o pedido muy vago: "acabo de llegar", "ayúdame"
        if (! $hayBusqueda && $this->tiene($texto, ['acabo de llegar', 'recien llegue', 'recien llego', 'soy nuevo', 'soy nueva', 'no se que hacer', 'no se por donde',
            'por donde empiezo', 'que me recomiendas', 'ayudame', 'necesito ayuda', 'busco algo', 'quiero algo', 'primera vez en cusco', 'no conozco cusco', 'no conozco la ciudad'])) {
            return $this->respuesta("¡Bienvenido/a a Cusco! 🏔️ Te recomiendo ir en este orden:\n1) Busca cuarto cerca de tu universidad y compara precios.\n2) Pide la visita y conoce el cuarto antes de pagar (nunca des adelantos a ciegas).\n3) Regístrate, marca tu horario de clases y busca un trabajo que no se cruce.\nLos primeros días toma con calma la altura. ¿Empezamos por el cuarto?",
                [], ['Cuarto cerca de la UNSAAC', 'Cuarto cerca de la Andina', 'Cuarto cerca de la Continental', '¿Qué distrito me conviene?']);
        }
        // comparar distritos o pedir recomendación de zona
        if ($r = $this->compararDistritos($texto, $datos)) {
            return $r;
        }

        // "más información", "info", "cuéntame" sin pedir nada concreto -> explicar TAMPU
        $pideInfo = ! $hayBusqueda && $this->tiene($texto, ['mas informacion', 'informacion', 'info', 'mas info', 'cuentame mas', 'cuentame', 'mas detalles', 'detalles', 'quiero saber mas', 'saber mas']);

        // qué es la página
        if ($pideInfo && ! $this->lugarMencionado($texto)) {
            return $this->explicarTampu();
        }
        if ($this->tiene($texto, ['que es esta pagina', 'que es esta web', 'que es este sitio', 'que es esto', 'que es tampu', 'para que sirve', 'de que trata',
            'de que se trata', 'que hace esta pagina', 'que hace la pagina', 'que hacen', 'que ofrecen', 'como funciona la pagina', 'como funciona tampu',
            'como funciona esto', 'explicame', 'no entiendo la pagina', 'que significa tampu', 'por que se llama', 'en que me ayuda', 'como me ayuda'])) {
            return $this->explicarTampu();
        }
        if ($this->tiene($texto, ['que puedes hacer', 'como funcionas', 'que haces', 'en que me puedes ayudar', 'que sabes hacer']) || trim($texto) === 'ayuda') {
            return $this->respuesta('Puedo: buscar cuartos ("minidepa en Wanchaq hasta 600"), buscar roomies o casas de estudiantes, mostrarte trabajos ("trabajo de mozo los fines de semana"), decirte precios promedio por distrito y darte consejos para no caer en estafas.',
                [], ['Precios promedio por distrito', 'Busco roomies', 'Trabajo por horas los sábados', 'Qué revisar en la visita']);
        }

        // quiénes hicieron la página
        if ($this->tiene($texto, ['quienes son', 'quien creo', 'quien hizo', 'quienes hicieron', 'quienes crearon', 'creador', 'desarrollador', 'quien te creo',
            'quien te hizo', 'quien te programo', 'son una empresa', 'de que universidad son'])) {
            return $this->respuesta('TAMPU es el proyecto final del curso Programación Web de la Universidad Continental (Cusco, 2026). Lo hicieron Wernher Alvares, César Juro, David Achahuanco y Olenka Aedo, después de entrevistar a estudiantes que llegaron de provincia.',
                [['texto' => 'Conocer al equipo', 'url' => route('nosotros')]], self::SUGERENCIAS);
        }

        // contacto con el equipo
        if ($this->tiene($texto, ['telefono de', 'su telefono', 'tu telefono', 'numero de ustedes', 'su numero', 'su correo', 'correo de ustedes', 'whatsapp',
            'contactarlos', 'contactar con ustedes', 'comunicarme con ustedes', 'contacto', 'escribirles'])) {
            return $this->respuesta('Puedes escribirle al equipo de TAMPU desde la página de Contacto, o hablar con un asesor por el chat de Mensajes si ya tienes cuenta.',
                [['texto' => 'Ir a Contacto', 'url' => route('contacto')], ['texto' => 'Hablar con un asesor', 'url' => route('asesor')]]);
        }

        // inglés
        if ($this->tiene($texto, ['hello', 'english', 'speak', 'room', 'apartment', 'rent', 'job', 'please', 'looking for', 'thank you', 'thanks', 'how much', 'i need'])
            && ! $this->tiene($texto, ['cuarto', 'trabajo', 'quiero', 'busco', 'hola'])) {
            return $this->respuesta("Hi! 👋 I'm Tampu. For now I only understand Spanish, but you can still browse everything: rooms (\"cuartos\") and jobs (\"trabajos\") are open to everyone. Try writing \"cuarto en Wanchaq hasta 400\".",
                [['texto' => 'Rooms / Cuartos', 'url' => route('habitaciones.index')], ['texto' => 'Jobs / Trabajos', 'url' => route('trabajos.index')]]);
        }

        // pareja/familia antes que "novia" (es una pregunta válida)
        if ($this->tiene($texto, ['pareja', 'con mi enamorad', 'con mi novi', 'con mi esposa', 'con mi esposo', 'con mi familia', 'con mi hijo', 'con mi hija'])) {
            return $this->respuestaFaq(self::PREGUNTAS['parejas']);
        }
        // temas que no corresponden
        if ($this->tiene($texto, ['novia', 'novio', 'enamorada', 'enamorado', 'citas', 'ligar', 'sexo', 'sexual', 'desnud', 'prostitu', 'escort', 'droga',
            'marihuana', 'cocaina', 'pistola', 'hackear', 'contrasena de otro'])) {
            return $this->respuesta('Con eso no te puedo ayudar 🙈. Yo sé de cuartos, trabajos y vida estudiantil en Cusco. ¿Te busco algo de eso?', [], self::SUGERENCIAS);
        }

        // emergencias y estafas ya ocurridas
        if ($this->tiene($texto, ['me estafaron', 'me timaron', 'me enganaron', 'me robaron el adelanto', 'perdi mi dinero', 'pague y no', 'deposite y no', 'fui estafad'])) {
            return $this->respuesta("Siento mucho lo que pasó 😔. Haz esto lo antes posible:\n1) Guarda pruebas: capturas del chat, el anuncio, el voucher o la constancia de Yape/Plin.\n2) Llama a tu banco o billetera digital para reportar la operación.\n3) Denuncia en la comisaría más cercana o en la fiscalía.\n4) Si la publicación está en TAMPU, usa el botón \"Reportar\" para que el administrador suspenda la cuenta.",
                [['texto' => 'Ver cómo reportar', 'url' => route('habitaciones.index')]], ['¿Cómo evito estafas?', 'Hablar con un asesor']);
        }
        if ($this->tiene($texto, ['emergencia', 'policia', 'bomberos', 'ambulancia', 'accidente', 'me asaltaron', 'me robaron', 'me atacaron'])) {
            return $this->respuesta("Si es una emergencia, llama ahora:\n🚓 Policía: 105\n🚑 SAMU (ambulancia): 106\n🚒 Bomberos: 116\nCuando estés a salvo, si el problema tiene que ver con una publicación de TAMPU, repórtala desde la página.");
        }

        // problemas con el dueño
        if ($this->tiene($texto, ['no me devuelve', 'no me devuelven', 'me quiere botar', 'me botaron', 'me desalojan', 'me quieren sacar', 'me cobra de mas',
            'me cobran de mas', 'problema con mi casero', 'problema con el dueno', 'problema con mi arrendador', 'mi casero', 'mi arrendador'])) {
            return $this->respuesta("Lo siento, es una situación incómoda. Te recomiendo:\n1) Revisar tu contrato o recibos (qué se acordó sobre la garantía y los pagos).\n2) Pedirle por escrito (mensaje o carta) lo que te debe, con fecha.\n3) Si no hay acuerdo, acudir a un centro de conciliación o a la municipalidad de tu distrito.\nSi el arrendador publica en TAMPU, también puedes reportarlo para que el administrador lo revise.",
                [], ['¿Cómo funciona la garantía?', 'Hablar con un asesor']);
        }

        // estudiantes extranjeros
        if ($this->tiene($texto, self::PAISES) && ! $hayBusqueda) {
            return $this->respuesta('¡Bienvenido/a! Puedes ver todos los cuartos y trabajos sin registrarte. Para crear una cuenta, por ahora el registro pide un DNI peruano de 8 dígitos; si tienes carné de extranjería o pasaporte, escríbenos desde Contacto y te ayudamos.',
                [['texto' => 'Ver cuartos', 'url' => route('habitaciones.index')], ['texto' => 'Ir a Contacto', 'url' => route('contacto')]]);
        }

        // otras ciudades: "cuarto en Lima" (fuera) vs "soy de Puno" (viene de allí)
        if ($lugar = $this->lugarMencionado($texto)) {
            [$nombre, $palabra] = $lugar;
            $esOrigen = preg_match('/(soy|vengo|vine|llegue|llego|somos|venimos|estoy llegando|recien llegue|me mude)\s+(de|desde)\s+(la |el )?' . preg_quote($palabra, '/') . '/', $texto);
            if ($esOrigen) {
                $this->prefijo = "¡Bienvenido/a desde $nombre! 🙌 ";
                if ($pideInfo) {
                    return $this->explicarTampu();
                }
                if (! $hayBusqueda) {
                    return $this->respuesta('Aquí te ayudo a encontrar cuarto en la ciudad del Cusco y trabajos que no choquen con tus clases. ¿Por dónde empezamos?', [], self::SUGERENCIAS);
                }
            } else {
                $enCusco = in_array($nombre, ['Sicuani', 'Quillabamba', 'Espinar', 'Urubamba', 'Calca', 'Pisac', 'Ollantaytambo', 'Chumbivilcas']);

                return $this->respuesta('Por ahora TAMPU solo funciona en la ciudad del Cusco' . ($enCusco ? " (no en $nombre)" : '') . ': Cusco, Wanchaq, Santiago, San Sebastián, San Jerónimo y Saylla 📍. Si vas a estudiar en Cusco, te ayudo a buscar aquí.',
                    [], ['Cuartos en Wanchaq hasta 400', 'Precios promedio por distrito', 'Trabajos compatibles']);
            }
        }

        // comprar/vender, anticresis
        if ($this->tiene($texto, ['comprar', 'compro', 'venden casa', 'venta de casa', 'vendo mi', 'terreno', 'lote', 'anticresis', 'hipoteca'])) {
            return $this->respuesta('TAMPU no vende casas ni terrenos: solo publica alquileres mensuales para estudiantes (cuartos, minidepas, roomies y casas de estudiantes).',
                [], ['Buscar cuarto', 'Precios promedio por distrito']);
        }

        // turismo y hospedaje por noches
        if (! $pideTrabajo && ! isset($datos['turno']) && ! isset($datos['dias']) && $this->tiene($texto, ['turista', 'por noche', 'noches', 'airbnb', 'booking', 'vacaciones', 'de viaje', 'machu picchu', 'machupicchu',
            'valle sagrado', 'montana de colores', 'que visitar', 'lugares para visitar', 'lugares visitar', 'lugares turisticos', 'sitios turisticos', 'que conocer',
            'conocer cusco', 'que hacer en cusco', 'lugares bonitos', 'paseo', 'tour', 'sacsayhuaman', 'qorikancha'])) {
            return $this->respuesta('TAMPU es para estudiantes que alquilan por meses, no para hospedaje turístico por noches 🏔️. Si vienes de visita: no te pierdas la Plaza de Armas, San Blas, el Qorikancha y Sacsayhuamán; y para Machu Picchu compra la entrada con anticipación en la web oficial del Ministerio de Cultura. Si te quedas a estudiar, aquí te ayudo con el cuarto.',
                [], ['Buscar cuarto', 'Consejos para la altura']);
        }

        // precio imposible: "cuarto a 50 soles", "cuarto gratis"
        $gratis = $this->tiene($texto, ['gratis', 'gratuito', 'regalado', 'sin pagar', 'cero soles']) && ! $this->tiene($texto, ['es gratis', 'cobran', 'cuesta usar', 'tampu']);
        if (($pideCuarto || isset($datos['tipo'])) && ($gratis || (isset($datos['precio']) && $datos['precio'] < 130))) {
            $min = (int) Habitacion::publicadas()->min('precio');
            $this->prefijo = ($gratis ? 'No hay cuartos gratis 😅' : "A S/ {$datos['precio']} no encontré cuartos 😅") . ", los más económicos en TAMPU empiezan en S/ $min. ";
            $datos['precio'] = max($min + 100, 250);
            unset($datos['precio_min']);

            return $this->buscarHabitaciones($request, $datos);
        }

        // matemáticas sencillas
        $soloOperacion = preg_match('/^\s*-?\d+(?:[.,]\d+)?\s*[\+\-x\*\/]\s*-?\d+(?:[.,]\d+)?\s*[=?]*\s*$/', $texto);
        if (preg_match('/(-?\d+(?:[.,]\d+)?)\s*([\+\-x\*\/])\s*(-?\d+(?:[.,]\d+)?)/', $texto, $m) && ($soloOperacion || $this->tiene($texto, ['cuanto es', 'cuanto da', 'calcula', 'resuelve', '=']))) {
            $a = (float) str_replace(',', '.', $m[1]);
            $b = (float) str_replace(',', '.', $m[3]);
            $r = match ($m[2]) { '+' => $a + $b, '-' => $a - $b, 'x', '*' => $a * $b, '/' => $b == 0 ? null : $a / $b };
            $txt = $r === null ? 'No se puede dividir entre cero 🤓' : 'Eso da ' . rtrim(rtrim(number_format($r, 2, '.', ''), '0'), '.') . ' 🤓';

            return $this->respuesta($txt . '. Igual lo mío son los números de los alquileres: ¿quieres ver precios promedio por distrito?', [], ['Precios promedio por distrito', 'Buscar cuarto']);
        }
        if ($this->tiene($texto, ['tarea', 'examen', 'hazme la', 'resuelve mi', 'ensayo', 'monografia'])) {
            return $this->respuesta('Las tareas no son lo mío 😅, pero sí te puedo ayudar a encontrar un cuarto tranquilo con wifi y escritorio para estudiar.', [], ['Cuarto con wifi y escritorio', 'Buscar trabajo']);
        }

        // temas de actualidad que no maneja: política, deportes, farándula
        if (! $hayBusqueda && $this->tiene($texto, ['gobierno', 'presidente', 'politic', 'elecciones', 'congreso', 'partido de', 'gano el partido', 'futbol',
            'seleccion', 'mundial', 'cancion', 'pelicula', 'serie', 'farandula', 'noticias', 'dolar', 'bitcoin'])) {
            return $this->respuesta('De ese tema no sé nada 😅: soy especialista en cuartos, trabajos y vida estudiantil en Cusco. ¿Te ayudo con algo de eso?', [], self::SUGERENCIAS);
        }

        // hora y fecha
        if ($this->tiene($texto, ['que hora es', 'hora actual', 'que dia es', 'fecha de hoy', 'que fecha es', 'dia de hoy'])) {
            $ahora = now('America/Lima')->locale('es');

            return $this->respuesta('En Cusco es ' . $ahora->isoFormat('dddd D [de] MMMM, h:mm a') . ' 🕒. ¿Te ayudo con algo más?', [], self::SUGERENCIAS);
        }
        // clima de hoy (no tenemos pronóstico en vivo)
        if ($this->tiene($texto, ['clima', 'tiempo hoy', 'va a llover', 'llovera', 'hace frio hoy', 'temperatura']) && $this->tiene($texto, ['hoy', 'manana', 'ahora', 'esta semana', 'va a', 'llovera'])) {
            return $this->respuesta('No tengo el pronóstico en vivo 🌦️, pero en Cusco de día suele haber sol y las noches son frías casi todo el año; de diciembre a marzo llueve bastante. Lleva casaca y pregunta si tu cuarto tiene agua caliente.');
        }

        // chistes, cariño
        if ($this->tiene($texto, ['chiste', 'algo gracioso', 'hazme reir', 'cuentame algo', 'aburrido', 'aburrida'])) {
            return $this->respuesta(self::CHISTES[array_rand(self::CHISTES)] . ' ¿Te ayudo a buscar algo?', [], self::SUGERENCIAS);
        }
        if ($this->tiene($texto, ['te quiero', 'te amo', 'eres lindo', 'eres linda', 'me caes bien', 'eres genial', 'buen bot', 'eres el mejor', 'eres la mejor', 'que buen'])) {
            return $this->respuesta('¡Gracias! 💛 Tú también me caes bien. ¿Te ayudo a buscar algo?', [], self::SUGERENCIAS);
        }

        // ánimo, dinero y comida (solo si no está pidiendo una búsqueda concreta)
        if (! $hayBusqueda && $this->tiene($texto, ['triste', 'me siento solo', 'me siento sola', 'extrano a mi', 'deprim', 'ansiedad', 'estres', 'estresad', 'soledad', 'llorando', 'nadie me'])) {
            return $this->respuesta("Llegar a una ciudad nueva no es fácil y es normal sentirse así 💛. Algunas ideas: habla seguido con tu familia, únete a algún taller o grupo de tu universidad y acércate al área de bienestar estudiantil, que ofrece apoyo psicológico gratuito.\nSi necesitas hablar con alguien ahora, en Perú puedes llamar a la Línea 113, opción 5 (salud mental).",
                [], ['Busco roomies', 'Hablar con un asesor']);
        }
        if (! $hayBusqueda && $this->tiene($texto, ['no tengo dinero', 'no tengo plata', 'sin plata', 'sin dinero', 'estoy pobre', 'no me alcanza', 'beca', 'ayuda economica', 'prestamo'])) {
            return $this->respuesta('Te entiendo, el primer mes es el más caro. Tres ideas: compartir un depa con roomies o un cuarto doble (sale más barato), buscar trabajos por horas que no choquen con tus clases, y revisar las becas de Pronabec (como Beca 18 o Beca Permanencia) y la oficina de bienestar de tu universidad.',
                [], ['Busco roomies', 'Trabajo por horas los sábados', 'Cuartos baratos']);
        }
        if (! $hayBusqueda && $this->tiene($texto, ['hambre', 'donde comer', 'comida barata', 'menu barato', 'restaurante', 'almorzar', 'almuerzo'])) {
            return $this->respuesta('Un menú en Cusco cuesta entre S/ 8 y S/ 12 🍲. Los mercados, como el de San Pedro en el centro o el de Wanchaq, tienen comida rica y barata. Si quieres ahorrar, te busco un cuarto con cocina o con desayuno incluido.',
                [], ['Cuarto con cocina', 'Cuarto con desayuno']);
        }

        // cuántos cuartos/trabajos hay
        if ($this->tiene($texto, ['cuantos cuartos', 'cuantas habitaciones', 'cuantas viviendas', 'cuantos trabajos', 'cuantas ofertas'])) {
            $c = Habitacion::publicadas()->count();
            $t = Trabajo::where('estado', 'activa')->count();

            return $this->respuesta("Ahora mismo hay $c viviendas disponibles y $t ofertas de trabajo activas en TAMPU 📊.",
                [['texto' => 'Ver cuartos', 'url' => route('habitaciones.index')], ['texto' => 'Ver trabajos', 'url' => route('trabajos.index')]]);
        }

        return null;
    }

    // ---------------------------------------------------------------- datos del propio usuario

    private function misDatos(Request $request, string $texto)
    {
        $pregVisitas = $this->tiene($texto, ['mi visita', 'mis visitas', 'mi solicitud', 'mis solicitudes', 'aceptaron mi visita', 'aceptaron la visita', 'me aceptaron la visita', 'respondieron mi visita', 'estado de mi visita']);
        $pregPost = $this->tiene($texto, ['mi postulacion', 'mis postulaciones', 'me aceptaron en el trabajo', 'me contrataron', 'respondieron mi postulacion', 'estado de mi postulacion', 'me llamaron del trabajo']);
        $pregMsj = $this->tiene($texto, ['tengo mensajes', 'mensajes nuevos', 'me escribieron', 'me respondieron', 'mensajes sin leer', 'tengo algun mensaje']);
        $pregFav = $this->tiene($texto, ['mis favoritos', 'lo que guarde', 'cuartos guardados', 'mis guardados']);
        $pregPend = $this->tiene($texto, ['pendiente', 'que hay de nuevo', 'resumen de hoy', 'tengo solicitudes', 'quien quiere visitar', 'tengo postulantes', 'mis postulantes', 'mis cuartos', 'mis publicaciones', 'mis ofertas', 'resumen de mi cuenta', 'como voy']);
        if (! ($pregVisitas || $pregPost || $pregMsj || $pregFav || $pregPend)) {
            return null;
        }
        $yo = $request->user();
        if (! $yo) {
            return $this->respuesta('Para revisar tus visitas, postulaciones o mensajes primero inicia sesión 🔐.', [['texto' => 'Iniciar sesión', 'url' => route('login')]]);
        }
        $estado = fn ($e) => ['pendiente' => '⏳ pendiente', 'aceptada' => '✅ aceptada', 'rechazada' => '❌ rechazada'][$e] ?? $e;

        if ($pregMsj) {
            $ids = \App\Models\Conversacion::de($yo)->pluck('id');
            $n = \App\Models\Mensaje::whereIn('conversacion_id', $ids)->where('usuario_id', '!=', $yo->id)->where('leido', false)->count();

            return $this->respuesta($n ? "Tienes $n mensaje" . ($n > 1 ? 's' : '') . ' sin leer 📩.' : 'No tienes mensajes sin leer 📭.', [['texto' => 'Abrir mensajes', 'url' => route('mensajes')]]);
        }

        if ($yo->rol === 'estudiante') {
            if ($pregFav) {
                $n = $yo->favoritos()->count();

                return $this->respuesta($n ? "Tienes $n habitación" . ($n > 1 ? 'es' : '') . ' guardada' . ($n > 1 ? 's' : '') . ' en favoritos ❤️.' : 'Aún no guardaste habitaciones. Toca el corazón ❤️ en las que te gusten.',
                    [['texto' => 'Ver mis favoritos', 'url' => route('estudiante.favoritos')]]);
            }
            if ($pregPost) {
                $lista = $yo->postulaciones()->with('trabajo')->latest()->take(3)->get();
                if ($lista->isEmpty()) {
                    return $this->respuesta('Todavía no postulaste a ningún trabajo. ¿Te busco ofertas que encajen con tu horario?', [], ['Trabajos compatibles']);
                }
                $txt = "Tus últimas postulaciones:\n" . $lista->map(fn ($p) => '• ' . ($p->trabajo->titulo ?? 'Oferta') . ': ' . $estado($p->estado))->implode("\n");

                return $this->respuesta($txt, [['texto' => 'Ver mi panel', 'url' => route('estudiante.panel')]]);
            }
            $lista = $yo->solicitudes()->with('habitacion')->latest()->take(3)->get();
            if ($lista->isEmpty()) {
                return $this->respuesta('Todavía no pediste ninguna visita. Abre una habitación y toca "Solicitar visita".', [], ['Buscar cuarto']);
            }
            $txt = "Tus últimas visitas:\n" . $lista->map(fn ($s) => '• ' . ($s->habitacion->titulo ?? 'Habitación') . ' (' . optional($s->fecha_visita)->format('d/m') . '): ' . $estado($s->estado))->implode("\n");
            if ($lista->contains('estado', 'aceptada')) {
                $txt .= "\nEn las aceptadas ya puedes ver el celular del dueño y descargar tu constancia.";
            }

            return $this->respuesta($txt, [['texto' => 'Ver mi panel', 'url' => route('estudiante.panel')]]);
        }
        if ($yo->rol === 'arrendador') {
            $cuartos = $yo->habitaciones()->count();
            $pend = \App\Models\Solicitud::whereIn('habitacion_id', $yo->habitaciones()->pluck('id'))->where('estado', 'pendiente')->count();

            return $this->respuesta("Tienes $cuartos " . ($cuartos === 1 ? 'habitación publicada' : 'habitaciones publicadas') . " y $pend " . ($pend === 1 ? 'solicitud de visita pendiente' : 'solicitudes de visita pendientes') . ' 🏠.',
                [['texto' => 'Responder solicitudes', 'url' => route('arrendador.solicitudes')], ['texto' => 'Mis habitaciones', 'url' => route('arrendador.habitaciones.index')]]);
        }
        if ($yo->rol === 'empleador') {
            $ofertas = $yo->trabajos()->count();
            $pend = \App\Models\Postulacion::whereIn('trabajo_id', $yo->trabajos()->pluck('id'))->where('estado', 'pendiente')->count();

            return $this->respuesta("Tienes $ofertas " . ($ofertas === 1 ? 'oferta publicada' : 'ofertas publicadas') . " y $pend " . ($pend === 1 ? 'postulación pendiente' : 'postulaciones pendientes') . ' 💼.',
                [['texto' => 'Ver postulaciones', 'url' => route('empleador.postulaciones')], ['texto' => 'Mis ofertas', 'url' => route('empleador.trabajos.index')]]);
        }
        if ($yo->rol === 'admin') {
            $ver = \App\Models\Usuario::where('verificacion', 'pendiente')->count();
            $rep = \App\Models\Reporte::where('estado', 'pendiente')->count();

            return $this->respuesta("Hay $ver cuenta" . ($ver === 1 ? '' : 's') . " por verificar y $rep reporte" . ($rep === 1 ? '' : 's') . ' pendiente' . ($rep === 1 ? '' : 's') . ' 🛡️.',
                [['texto' => 'Ir al panel', 'url' => route('admin.panel')], ['texto' => 'Ver reportes', 'url' => route('admin.reportes')]]);
        }

        return null;
    }

    // ---------------------------------------------------------------- zonas

    const ZONAS = [
        'Cusco' => 'centro histórico; muy bien ubicado pero más turístico, ruidoso y caro',
        'Wanchaq' => 'céntrico y comercial, con mucho transporte por la Av. de la Cultura; cerca de la UNSAAC',
        'Santiago' => 'suele ser de los más económicos; tiene zonas muy tranquilas y otras más movidas',
        'San Sebastián' => 'residencial y tranquilo, a medio camino entre la UNSAAC y la U. Andina',
        'San Jerónimo' => 'tranquilo y cerca de la U. Andina (Larapa); precios accesibles',
        'Saylla' => 'más alejado del centro pero cerca de la U. Continental (Angostura)',
    ];

    private function compararDistritos(string $texto, array $datos)
    {
        $nombrados = [];
        foreach (self::DISTRITOS as $d => $palabras) {
            if ($this->tiene($texto, $palabras)) {
                $nombrados[] = $d;
            }
        }
        $compara = count($nombrados) >= 2 && $this->tiene($texto, [' o ', ' vs ', 'versus', 'mejor', 'diferencia', 'compar', 'conviene', 'cual']);
        $recomienda = $this->tiene($texto, ['que distrito', 'que zona', 'donde me conviene', 'que barrio', 'mejor zona', 'mejor distrito', 'me conviene vivir', 'donde vivir', 'donde es mejor vivir']);
        if (! $compara && ! $recomienda) {
            return null;
        }
        if (! $compara) {
            $nombrados = match ($datos['uni'] ?? null) {
                'UNSAAC' => ['Wanchaq', 'Cusco', 'Santiago'],
                'U. Andina' => ['San Jerónimo', 'San Sebastián'],
                'U. Andina Qollana' => ['San Jerónimo', 'Saylla', 'San Sebastián'],
                'U. Continental' => ['Saylla', 'San Jerónimo'],
                default => array_keys(self::ZONAS),
            };
        }
        $precios = Habitacion::publicadas()->whereIn('distrito', $nombrados)->selectRaw('distrito, avg(precio) as promedio, count(*) as total')
            ->groupBy('distrito')->get()->keyBy('distrito');
        $lineas = collect($nombrados)->map(function ($d) use ($precios) {
            $p = $precios[$d] ?? null;

            return "• $d: " . self::ZONAS[$d] . ($p ? '. Promedio S/ ' . (int) round($p->promedio) . " ({$p->total} opciones)" : '') . '.';
        })->implode("\n");
        $intro = $compara ? 'Así se comparan:' : (isset($datos['uni']) ? 'Si estudias en ' . Lugares::UNIVERSIDADES[$datos['uni']]['nombre'] . ', te convienen estas zonas:' : 'Así es cada distrito donde hay cuartos en TAMPU:');
        $sug = collect($nombrados)->take(3)->map(fn ($d) => "Cuartos en $d")->all();

        return $this->respuesta("$intro\n$lineas\nLo más importante: que quede cerca de tu universidad o con un bus directo.", [], $sug);
    }

    private function lugarMencionado(string $texto): ?array
    {
        foreach (self::LUGARES as $nombre => $palabras) {
            foreach ($palabras as $p) {
                if ($this->coincide($texto, $p)) {
                    return [$nombre, $p];
                }
            }
        }

        return null;
    }

    // ¿conviene que responda la IA? Mensajes largos que no son una búsqueda simple, o que plantean un problema u opinión.
    // problemas frecuentes de convivencia y pagos (respuesta fija si la IA no está o no respondió)
    private function problemaComun(string $texto)
    {
        $conRoomie = $this->tiene($texto, ['roomie', 'rumi', 'companero de cuarto', 'companera de cuarto', 'companero de depa', 'companera de depa', 'mi companero', 'mi companera']);
        $conflicto = $this->tiene($texto, ['no paga', 'no quiere pagar', 'no me paga', 'no pone', 'pelea', 'peleamos', 'problema', 'ruido', 'bulla', 'sucio', 'no limpia',
            'no lava', 'se fue', 'se va', 'me debe', 'invita gente', 'trae gente', 'fiestas', 'que hago', 'no se lleva', 'me cae mal']);
        if ($conRoomie && $conflicto) {
            return $this->respuesta("Eso pasa más de lo que crees. Lo que suele funcionar:\n"
                . "1. Habla con tu roomie en persona y con calma; muchas veces es un descuido, no mala fe.\n"
                . "2. Pongan por escrito (un chat basta) cuánto paga cada uno de luz, agua e internet y la fecha.\n"
                . "3. Guarden los recibos en una carpeta o foto compartida.\n"
                . "4. Si sigue sin pagar, avísale al arrendador: el contrato es con él y puede mediar.\n"
                . "Si la convivencia ya no funciona, puedo buscarte otro cuarto 🙂", [], ['Cuartos con baño propio', 'Busco roomies', 'Hablar con un asesor']);
        }
        $pago = $this->tiene($texto, ['dolares', 'dolar', 'adelantado', 'por adelantado', 'adelanto', 'garantia', 'deposito', 'pagar antes de ver', 'sin ver el cuarto', 'yape antes']);
        $pregunta = $this->tiene($texto, ['es normal', 'normal', 'que hago', 'es seguro', 'me pide', 'me pidio', 'quiere que', 'cuanto', 'debo', 'esta bien', 'estafa']);
        if ($pago && $pregunta) {
            return $this->respuesta("En Cusco lo usual es pagar en soles: el primer mes por adelantado y un mes de garantía, que te devuelven al irte si dejas todo bien.\n"
                . "• Pedir 3 meses o más por adelantado no es lo común: puedes negociarlo.\n"
                . "• Si te pide dólares, que el precio quede escrito en soles para evitar líos con el tipo de cambio.\n"
                . "• Nunca pagues sin haber visitado el cuarto, y pide recibo o contrato por cada pago.\n"
                . "Si algo te parece raro, repórtalo desde la publicación y lo revisamos.", [], ['¿Cómo evito estafas?', 'Qué revisar en la visita', 'Hablar con un asesor']);
        }

        return null;
    }

    private function mejorConIA(string $texto, array $datos): bool
    {
        $palabras = count(preg_split('/\s+/', trim($texto)));
        $problema = $this->tiene($texto, ['que hago', 'que hacer', 'es normal', 'puedo ', 'deberia', 'si mi', 'si el', 'si la', 'pero ', 'problema', 'no paga',
            'no me ', 'por que', 'que pasa si', 'vale la pena', 'opinas', 'recomiendas', 'es cierto', 'es verdad', 'es seguro', 'es peligroso', 'me conviene que',
            'que significa', 'como hago', 'ayudame con', 'tengo un', 'tengo una', 'quiere que', 'me pide', 'me pidio', 'me dijo', 'mi mama', 'mi papa', 'mis padres']);
        $crisis = $this->tiene($texto, ['suicid', 'matarme', 'emergencia', 'policia', 'me asaltaron', 'me robaron', 'me estafaron']);
        if ($crisis) {
            return false;   // eso lo responden las reglas, con los números correctos
        }
        // dudas con respuesta fija (mascotas, registro, publicar…): las reglas responden al instante
        $faq = $this->buscarPregunta($texto);
        if ($faq && in_array($faq['clave'], ['mascotas', 'publicar', 'registro', 'contrasena', 'dos_pasos', 'precio_tampu', 'pedir_visita', 'postular', 'resena', 'reportar', 'favoritos'])) {
            return false;
        }
        if ($problema && $palabras >= 4) {
            return true;
        }
        $busquedaClara = $datos || $this->tiene($texto, ['cuarto', 'habitacion', 'trabajo', 'chamba', 'roomie', 'minidepa', 'departamento', 'residencia']);

        return $palabras >= 9 && ! $busquedaClara;
    }

    private function preguntarIA(Request $request)
    {
        if (! AsistenteIA::activo()) {
            return null;
        }
        $ia = app(AsistenteIA::class)->responder($this->original, $request->session()->get('asistente.historial', []));
        if (! $ia) {
            return null;
        }
        $accion = $ia['accion'];
        $limpio = fn ($v) => is_string($v) && $v !== '' && strtolower($v) !== 'null' ? $v : null;

        if ($accion && $accion['buscar'] === 'cuartos') {
            $datos = [];
            if (($d = $limpio($accion['distrito'] ?? null)) && isset(self::DISTRITOS[$d])) {
                $datos['distrito'] = $d;
            }
            if (($t = $limpio($accion['tipo'] ?? null)) && isset(Habitacion::TIPOS[$t])) {
                $datos['tipo'] = $t;
            }
            if (($u = $limpio($accion['cerca_de'] ?? null)) && ($u = Lugares::buscar($u))) {
                $datos['uni'] = $u;
            }
            if (is_numeric($accion['precio_max'] ?? null) && $accion['precio_max'] >= 100) {
                $datos['precio'] = (int) $accion['precio_max'];
            }
            $this->prefijo = rtrim($ia['texto']) . "\n";

            return $this->buscarHabitaciones($request, $datos);
        }
        if ($accion && $accion['buscar'] === 'trabajos') {
            $datos = [];
            if ($p = $limpio($accion['palabra'] ?? null)) {
                $datos['rubro'] = [mb_strtolower(Str::ascii($p))];
                $datos['rubro_texto'] = mb_strtolower($p);
            }
            $turno = ['mañana' => 'manana', 'manana' => 'manana', 'tarde' => 'tarde', 'noche' => 'noche'][mb_strtolower((string) ($accion['turno'] ?? ''))] ?? null;
            if ($turno) {
                $datos['turno'] = $turno;
            }
            if (! empty($accion['fines_de_semana'])) {
                $datos['dias'] = ['sabado', 'domingo'];
            }
            $this->prefijo = rtrim($ia['texto']) . "\n";

            return $this->buscarTrabajos($request, $this->original, $datos);
        }

        return $this->respuesta($ia['texto'], [], ['Buscar cuarto', 'Buscar trabajo', 'Hablar con un asesor']);
    }

    private function explicarTampu()
    {
        return $this->respuesta("TAMPU es una página web gratuita para estudiantes que llegan a Cusco desde provincia. Te ayuda con dos cosas:\n🏠 Encontrar cuarto: habitaciones, minidepas, roomies y casas de estudiantes de arrendadores con DNI verificado. Ves fotos, precio, reseñas y a cuántos minutos queda tu universidad, y pides la visita antes de pagar.\n💼 Encontrar trabajo: ofertas de empresas con RUC verificado que no chocan con tu horario de clases.\nEl nombre viene del quechua: un tampu era un lugar de descanso en el camino.",
                [['texto' => 'Ver cuartos', 'url' => route('habitaciones.index')], ['texto' => 'Ver trabajos', 'url' => route('trabajos.index')], ['texto' => 'Crear mi cuenta', 'url' => route('registro')]],
                ['¿Qué puedes hacer?', '¿Es gratis?', '¿Cómo evito estafas?']);
    }

    private function noEntendi(Request $request, string $texto)
    {
        // rescate: cualquier pregunta sobre la página que no se entendió por errores de tipeo ("que hacx esta pagina")
        if ($this->tiene($texto, ['pagina', 'esta web', 'la web', 'sitio', 'tampu', 'esta app', 'la app', 'esto', 'aplicacion'])
            && $this->tiene($texto, ['que', 'para', 'como', 'de que', 'explica', 'sirve'])) {
            return $this->explicarTampu();
        }

        // respaldo con inteligencia artificial (solo si hay clave configurada)
        if ($r = $this->preguntarIA($request)) {
            return $r;
        }

        $this->esFallo = true;
        $fallos = $request->session()->get('asistente.fallos', 0) + 1;
        $request->session()->put('asistente.fallos', $fallos);

        if ($fallos >= 2) {
            return $this->respuesta('Sigo sin entenderte, perdón 😓. Mejor te paso con una persona del equipo, o elige una de estas opciones:',
                $request->user() ? [['texto' => 'Hablar con un asesor', 'url' => route('asesor')]] : [['texto' => 'Escribir al equipo', 'url' => route('contacto')]],
                ['¿Qué es TAMPU?', 'Buscar cuarto', 'Buscar trabajo']);
        }
        if (str_contains($texto, '?') || $this->tiene($texto, ['que ', 'como ', 'por que', 'cuando', 'donde '])) {
            return $this->respuesta('Esa pregunta está fuera de lo que sé 😅. Yo sé de cuartos, trabajos y vida estudiantil en Cusco. Prueba con algo como "cuarto con baño propio cerca de la Andina" o "trabajo de mozo los sábados".',
                [], ['¿Qué es TAMPU?', 'Buscar cuarto', 'Buscar trabajo']);
        }

        return $this->respuesta('No te entendí del todo 🤔. Puedes escribirme cosas como "cuarto con baño propio cerca de la Andina", "busco roomies en Wanchaq", "trabajo de mozo los sábados" o "¿cuánto cuesta un cuarto en Santiago?".',
            [], self::SUGERENCIAS);
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
                if ($this->coincide($texto, $palabra)) {
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
        $texto = $this->prefijo . $texto;
        if (request()->hasSession()) {
            if (! $this->esFallo) {
                request()->session()->forget('asistente.fallos');
            }
            // últimos turnos de la conversación (para que la IA entienda el contexto)
            $historial = request()->session()->get('asistente.historial', []);
            if ($this->original !== '') {
                $historial[] = ['rol' => 'usuario', 'texto' => $this->original];
            }
            $historial[] = ['rol' => 'asistente', 'texto' => mb_substr($texto, 0, 400)];
            request()->session()->put('asistente.historial', array_slice($historial, -8));
        }

        return response()->json(compact('texto', 'enlaces', 'sugerencias'));
    }

    private function normalizar(string $texto): string
    {
        $texto = Str::lower(Str::ascii($texto));
        $texto = preg_replace('/([a-z])\1{2,}/', '$1', $texto);   // "holaaa" -> "hola", "nooo" -> "no"
        // abreviaturas de chat: "q", "k", "xq", "pq", "x favor", "tmb"
        $texto = preg_replace(['/(?<![a-z0-9])(q|k|ke|qe|ke)(?![a-z0-9])/', '/(?<![a-z0-9])(xq|pq|porq)(?![a-z0-9])/', '/(?<![a-z0-9])(tmb|tb)(?![a-z0-9])/', '/(?<![a-z0-9])x(?= favor| fa)/', '/(?<!\d)\s+x\s+(?!\d)/'],
            ['que', 'por que', 'tambien', 'por', ' por '], $texto);

        return preg_replace('/\s+/', ' ', $texto);
    }

    private function tiene(string $texto, array $palabras): bool
    {
        foreach ($palabras as $p) {
            if ($this->coincide($texto, $p)) {
                return true;
            }
        }

        return false;
    }

    // corrige errores de tipeo en las palabras importantes ("habitasion", "trabjo", "santiogo", "cuarot")
    const VOCABULARIO = ['cuarto', 'cuartos', 'habitacion', 'habitaciones', 'trabajo', 'trabajos', 'roomies', 'minidepa', 'departamento', 'residencia',
        'estudiantes', 'wanchaq', 'santiago', 'sebastian', 'jeronimo', 'saylla', 'unsaac', 'andina', 'continental', 'alquiler', 'barato', 'baratos',
        'economico', 'compartido', 'individual', 'amoblado', 'garantia', 'contrasena', 'postular', 'postulacion', 'favoritos', 'estafa', 'mensajes',
        'calendario', 'horario', 'disponible', 'agua', 'caliente', 'universidad', 'pagina', 'funciona', 'aplicacion', 'tampu', 'informacion', 'detalles'];
    const PROTEGIDAS = ['cuanto', 'cuantos', 'cuanta', 'cuantas', 'cuando', 'cuarta', 'cuatro', 'cuartel', 'trabaja', 'trabajar', 'trabajado', 'trabaje', 'estado',
        'estafo', 'mensaje', 'horarios', 'pagar', 'pague', 'pagos', 'tambo', 'tampoco', 'fundas', 'barata', 'baratas', 'andino', 'andinas', 'alquile', 'alquilo', 'alquila', 'santo', 'santos', 'agua', 'aguas', 'saya', 'sala'];

    // palabras de los nombres de universidades e institutos ("khipu", "senati", "pasteur"): no se corrigen
    private function nombresDeLugares(): array
    {
        static $palabras = null;
        if ($palabras === null) {
            $palabras = [];
            foreach (Lugares::UNIVERSIDADES as $clave => $u) {
                foreach (array_merge($u['palabras'], [Str::ascii(strtolower($clave)), Str::ascii(strtolower($u['completo']))]) as $frase) {
                    foreach (preg_split('/[^a-z]+/', strtolower(Str::ascii($frase))) as $w) {
                        $palabras[$w] = true;
                    }
                }
            }
        }

        return $palabras;
    }

    private function corregir(string $texto): string
    {
        return preg_replace_callback('/[a-z]{5,}/', function ($m) {
            $w = $m[0];
            if (in_array($w, self::VOCABULARIO) || in_array($w, self::PROTEGIDAS) || isset($this->nombresDeLugares()[$w])) {
                return $w;
            }
            $tope = strlen($w) >= 8 ? 2 : 1;
            foreach (self::VOCABULARIO as $v) {   // plurales y variantes ("postulaciones", "cuartito") se dejan como están
                if (str_starts_with($w, $v) || str_starts_with($v, $w)) {
                    return $w;
                }
            }
            foreach (self::VOCABULARIO as $v) {
                if (abs(strlen($v) - strlen($w)) > $tope) {
                    continue;
                }
                $intercambio = strlen($v) === strlen($w) && count_chars($v, 1) == count_chars($w, 1) && levenshtein($v, $w) <= 2;
                if (levenshtein($v, $w) <= $tope || $intercambio) {
                    return $v;
                }
            }

            return $w;
        }, $texto);
    }

    // la palabra debe empezar donde empieza una palabra del mensaje; las cortas, además, deben terminar ahí
    private function coincide(string $texto, string $p): bool
    {
        $t = trim($p);
        if ($t === '') {
            return false;
        }
        $inicio = ctype_alnum($t[0]) ? '(?<![a-z0-9])' : '';
        $fin = (str_ends_with($p, ' ') || in_array($t, self::ENTERAS)) ? '(?:s|es)?(?![a-z0-9])' : '';

        return (bool) preg_match('/' . $inicio . preg_quote($t, '/') . $fin . '/', $texto);
    }
}
