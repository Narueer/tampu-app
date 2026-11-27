{{--
    Guía paso a paso con Tampu (la mascota). Cada página tiene sus pasos: [selector, título, texto].
    Si el selector no existe en la página, ese paso se salta solo.
    La primera vez que alguien entra a su panel la guía empieza sola; después se repite con el botón "?".
--}}
@php
    $rol = auth()->user()?->rol;
    $nombre = auth()->check() ? explode(' ', auth()->user()->nombre)[0] : null;
    $ruta = request()->route()?->getName();

    $guias = [
        'inicio' => [
            [null, '¡Hola! Soy Tampu', 'Te acompaño en tu primer recorrido. Esta página te ayuda a encontrar cuarto y trabajo en Cusco, todo verificado. Son solo seis pasos.'],
            ['.buscador-flotante', 'Busca tu cuarto', 'Elige el distrito, cuánto puedes pagar al mes y el tipo de cuarto. Luego toca Buscar.'],
            ['.pasos', 'Cómo funciona', 'Te registras, marcas tu horario de clases y pides una visita. Nunca pagues sin haber visto el cuarto.'],
            ['.tarjeta-uni', 'Cerca de donde estudias', 'Toca tu universidad o instituto y te muestro los cuartos que están cerca.'],
            ['#mapa', 'El mapa', 'Cada punto es un cuarto. Los íconos te dicen si es individual, compartido o minidepa. Abajo a la izquierda está la leyenda.'],
            ['.ilustracion-panel', 'Una maqueta para mirar', 'Gira el cuarto con el mouse o el dedo, y toca los números: son consejos para cuando vayas a la visita.'],
            ['#btnAsistente', 'Pregúntame lo que quieras', 'Si tienes dudas, escríbeme aquí. Te respondo al instante, a cualquier hora.'],
        ],
        'habitaciones.index' => [
            ['.filtros', 'Filtra a tu medida', 'Distrito, precio máximo, tipo de cuarto y cerca de qué universidad. Toca Filtrar para ver los resultados.'],
            ['.chips-filtro', 'Cambia de distrito rápido', 'Con estos botones saltas de un distrito a otro sin volver a llenar los filtros.'],
            ['#mapa', 'Míralos en el mapa', 'Pasa el mouse sobre un punto para ver el precio, y tócalo para abrir el cuarto.'],
        ],
        'calculadora' => [
            ['.presu-ingreso', 'Lo que recibes', 'Pon cuánto te mandan o ganas, al mes o a la semana.'],
            ['#pItems', 'Tus gastos', 'Cambia los montos, elige cada cuánto los pagas o agrega los tuyos. Toca el ícono para cambiarlo.'],
            ['.presu-grande', 'Tu tope para el cuarto', 'Esto es lo máximo que puedes pagar de alquiler. En la lista de cuartos verás cuáles entran.'],
            ['.presu-acciones', 'Guárdalo o imprímelo', 'Se guarda solo. Con «Imprimir» sale una hoja para mostrársela a tu familia.'],
            ['#tabDistritos', 'Compara distritos', 'Aquí ves cuánto te quedaría viviendo en cada distrito de Cusco.'],
        ],
        'habitaciones.show' => [
            ['.galeria', 'Las fotos', 'Toca cualquier foto para verla en grande.'],
            ['#reservar', 'Pide tu visita', 'Elige un día y escríbele al arrendador. Cuando acepte, verás su celular. Nunca adelantes dinero sin visitar.'],
            ['.distancias', '¿Qué tan lejos queda?', 'Aquí ves cuántos minutos te toma llegar a pie o en bus a cada universidad.'],
        ],
        'trabajos.index' => [
            ['.barra-filtros', 'Busca un trabajo', 'Filtra por distrito o modalidad. Si eres estudiante, marca "Solo compatibles" y verás solo los que no chocan con tus clases.'],
            ['#mapa', 'Dónde quedan', 'Cada maletín es una oferta. Pasa el mouse para ver cuánto pagan.'],
        ],
        'estudiante.panel' => [
            [null, '¡Bienvenido, ' . $nombre . '!', 'Este es tu panel. Te muestro lo importante en cuatro pasos.'],
            ['.kpis', 'Tu resumen', 'Visitas que pediste, cuántas te aceptaron, tus postulaciones y tus favoritos.'],
            ['.horario', 'Tu horario de clases', 'Marca los turnos en los que tienes clase y toca Guardar. Así en Trabajos solo verás ofertas que encajan contigo.'],
            ['#misVisitas', 'Tus visitas', 'Cuando el arrendador acepta, aquí aparece su celular y puedes descargar tu constancia.'],
            ['#btnAsistente', 'Si te pierdes', 'Pregúntame lo que necesites o pide hablar con un asesor.'],
        ],
        'arrendador.habitaciones.index' => [
            [null, '¡Hola, ' . $nombre . '!', 'Aquí administras tus cuartos y las visitas que te piden. Te muestro cómo.'],
            ['.bienvenida-acciones', 'Publica un cuarto', 'Toca "Nueva habitación", sube buenas fotos y marca el punto en el mapa. Las publicaciones con fotos reciben más mensajes.'],
            ['.kpis', 'Cómo te va', 'Tus cuartos, los que están disponibles, las solicitudes que llegaron y tu promedio de reseñas.'],
            ['.tabla', 'Tus habitaciones', 'Desde aquí editas el precio, cambias el estado a "alquilado" o eliminas una publicación.'],
        ],
        'arrendador.solicitudes' => [
            ['.tabla', 'Solicitudes de visita', 'Acepta o rechaza cada visita. Cuando aceptas, el estudiante ve tu celular para coordinar.'],
        ],
        'empleador.trabajos.index' => [
            [null, '¡Hola, ' . $nombre . '!', 'Aquí publicas ofertas para estudiantes y ves quién postuló.'],
            ['.bienvenida-acciones', 'Publica una oferta', 'Indica los turnos del trabajo: así solo te postulan estudiantes que tienen esas horas libres.'],
            ['.kpis', 'Tu resumen', 'Ofertas publicadas, activas, postulaciones recibidas y mensajes por leer.'],
            ['.tabla', 'Tus ofertas', 'Edita, cierra o elimina una oferta cuando ya cubriste el puesto.'],
        ],
        'admin.panel' => [
            [null, 'Panel del administrador', 'Desde aquí cuidas que TAMPU sea seguro. Te muestro las partes principales.'],
            ['.kpis', 'Los números de hoy', 'Estudiantes, cuentas verificadas, publicaciones y los reportes que faltan revisar.'],
            ['.graficos', 'Cómo va la plataforma', 'Cuartos y precio promedio por distrito, y cómo van las visitas, las postulaciones y los usuarios.'],
            ['.col-lg-8 .caja', 'Verificaciones', 'Revisa el DNI o RUC de cada arrendador o empresa y toca Verificar o Rechazar.'],
            ['.subnav', 'Más herramientas', 'En Usuarios, Reportes, Contacto y Seguridad puedes buscar y filtrar por fechas.'],
        ],
    ];
    $pasos = $guias[$ruta] ?? null;
    // en los paneles la guía empieza sola la primera vez; en las páginas públicas se ofrece
    $automatica = in_array($ruta, ['estudiante.panel', 'arrendador.habitaciones.index', 'empleador.trabajos.index', 'admin.panel'], true);
    $datosGuia = $pasos ? ['clave' => 'guia.' . $ruta . '.' . ($rol ?? 'visita'), 'automatica' => $automatica, 'pasos' => $pasos, 'avatar' => asset('img/asistente/tampu-bot.webp')] : null;
@endphp

<div class="controles-flotantes" aria-label="Guía y música">
    @if ($pasos)
        <button type="button" id="btnGuia" class="control-flotante" aria-label="Ver la guía de esta página" title="Guía de esta página">
            <i class="bi bi-signpost-split"></i><span>Guía</span>
        </button>
    @endif
    <button type="button" id="btnMusica" class="control-flotante" aria-pressed="false" aria-label="Música de fondo" title="Música andina (puedes apagarla cuando quieras)"
            data-src="{{ asset('audio/andina.mp3') }}">
        <i class="bi bi-music-note-beamed"></i><span class="ondas" aria-hidden="true"><i></i><i></i><i></i></span>
    </button>
</div>

@if ($pasos)
    <script id="datosGuia" type="application/json">{!! json_encode($datosGuia, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endif
