// Guías del usuario en PDF (una por rol + una general), con capturas reales de la página y datos censurados.
//   node generar.js            -> public/guias/guia-*.pdf
// Necesita la página corriendo en 127.0.0.1:8099 y /tmp/nombres.json (nombres a censurar).
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const { rutas, escenario } = require('../videos-guia/comun');

const BASE = 'http://127.0.0.1:8099';
const RAIZ = path.resolve(__dirname, '../..');
const PUB = RAIZ + '/public/';
const CAPT = '/home/claude/guias/capturas/';
const SALIDA = PUB + 'guias/';
const NOMBRES = JSON.parse(fs.readFileSync(process.env.NOMBRES || '/tmp/nombres.json', 'utf8'));
const NM = '/tmp/bs/node_modules/';
fs.mkdirSync(CAPT, { recursive: true }); fs.mkdirSync(SALIDA, { recursive: true });

const CUENTAS = { estudiante: 'luis@tampu.pe:123456', arrendador: 'rosa@tampu.pe:123456', empleador: 'wayra@tampu.pe:123456', admin: 'admin@tampu.pe:admin123' };

// ---------------------------------------------------------------- capturas
// [nombre, cuenta, url, elemento (o null = pantalla), preparar(p)]
const CAPTURAS = [
  ['inicio', null, '/', null],
  ['filtros', null, '/habitaciones?distrito=Wanchaq&precio_max=450', null, async (p) => { await p.evaluate(() => window.scrollTo(0, 260)); }],
  ['detalle', null, '/habitaciones', null, async (p) => { await p.click('.tarjeta a.btn'); await p.waitForLoadState('networkidle'); }],
  ['alrededor', null, '/habitaciones', '.alrededor', async (p) => { await p.click('.tarjeta a.btn'); await p.waitForLoadState('networkidle'); }],
  ['distancias', null, '/habitaciones', '.distancias', async (p) => { await p.click('.tarjeta a.btn'); await p.waitForLoadState('networkidle'); }],
  ['presupuesto', null, '/me-alcanza', '#panelMio'],
  ['asistente', null, '/', '#asistente', async (p) => {
    await p.click('#btnAsistente'); await p.fill('#asistenteTexto', 'cuarto en Wanchaq hasta 400'); await p.keyboard.press('Enter'); await p.waitForTimeout(1800); }],
  ['registro', null, '/registro', 'main form, form', null],
  ['est-panel', 'estudiante', '/estudiante', null],
  ['est-horario', 'estudiante', '/estudiante', '#misVisitas', async (p) => {}],
  ['est-tabla-horario', 'estudiante', '/estudiante', 'table.horario'],
  ['est-trabajos', 'estudiante', '/trabajos?compatible=1', null, async (p) => { await p.evaluate(() => window.scrollTo(0, 280)); }],
  ['est-reservar', 'estudiante', '/habitaciones', '#reservar', async (p) => { await p.click('.tarjeta a.btn'); await p.waitForLoadState('networkidle'); }],
  ['est-mensajes', 'estudiante', '/mensajes', null, async (p) => { await p.click('.chat-item'); await p.waitForLoadState('networkidle'); }],
  ['est-calendario', 'estudiante', '/calendario', null],
  ['arr-panel', 'arrendador', '/arrendador/habitaciones', null],
  ['arr-form', 'arrendador', '/arrendador/habitaciones/create', null],
  ['arr-form-mapa', 'arrendador', '/arrendador/habitaciones/create', null, async (p) => { await p.evaluate(() => document.querySelector('[name=servicios\\[\\]]')?.scrollIntoView({ block: 'center' })); }],
  ['arr-solicitudes', 'arrendador', '/arrendador/solicitudes', null],
  ['emp-panel', 'empleador', '/empleador/trabajos', null],
  ['emp-form', 'empleador', '/empleador/trabajos/create', null],
  ['emp-horario', 'empleador', '/empleador/trabajos/create', 'table.horario'],
  ['emp-postulaciones', 'empleador', '/empleador/postulaciones', null],
  ['adm-panel', 'admin', '/admin', null],
  ['adm-usuarios', 'admin', '/admin/usuarios', null],
  ['adm-reportes', 'admin', '/admin/reportes', null],
  ['adm-seguridad', 'admin', '/admin/seguridad', null],
  ['perfil', 'estudiante', '/perfil', null],
];

async function capturar(b) {
  const sesiones = {};
  for (const [nombre, cuenta, url, elemento, preparar] of CAPTURAS) {
    if (process.env.SOLO_PDF && fs.existsSync(CAPT + nombre + '.jpg')) continue;
    if (!sesiones[cuenta || 'visita']) {
      const c = await b.newContext({ viewport: { width: 1280, height: 800 }, deviceScaleFactor: 1.5 });
      await rutas(c);
      if (cuenta) {
        const p = await c.newPage(); const [m, pw] = CUENTAS[cuenta].split(':');
        await p.goto(BASE + '/login'); await p.fill('input[name=email]', m); await p.fill('input[name=password]', pw);
        await Promise.all([p.waitForNavigation(), p.click('form button[type=submit]')]); await p.close();
      }
      await c.addInitScript(escenario, NOMBRES);
      await c.addInitScript(() => { try { localStorage.setItem('guia.voz', '0');
        for (const r of ['estudiante.panel', 'arrendador.habitaciones.index', 'empleador.trabajos.index', 'admin.panel', 'inicio'])
          for (const x of ['estudiante', 'arrendador', 'empleador', 'admin', 'visita']) localStorage.setItem('guia.' + r + '.' + x, 'visto'); } catch (e) {} });
      sesiones[cuenta || 'visita'] = c;
    }
    const p = await sesiones[cuenta || 'visita'].newPage();
    await p.goto(BASE + url, { waitUntil: 'networkidle' }).catch(() => {});
    await p.addStyleTag({ content: '#demoSub,#demoCursor{display:none!important}.revelar{opacity:1!important;transform:none!important}.btn-asistente{opacity:1}' });
    if (preparar) await preparar(p).catch((e) => console.log('  preparar', nombre, e.message.split('\n')[0]));
    await p.waitForTimeout(900);
    try {
      if (elemento && await p.locator(elemento).first().count()) {
        const el = p.locator(elemento).first(); await el.scrollIntoViewIfNeeded(); await p.waitForTimeout(500);
        await el.screenshot({ path: CAPT + nombre + '.jpg', type: 'jpeg', quality: 82 });
      } else {
        await p.screenshot({ path: CAPT + nombre + '.jpg', type: 'jpeg', quality: 82 });
      }
      console.log('captura', nombre);
    } catch (e) { console.log('  falló', nombre, e.message.split('\n')[0]); }
    await p.close();
  }
  for (const c of Object.values(sesiones)) await c.close();
}

// copia cada captura a un ancho máximo de 1400 px y calidad 72: el PDF pesa menos de la mitad sin que se note
function aligerar() {
  fs.mkdirSync(LITE, { recursive: true });
  for (const f of fs.readdirSync(CAPT).filter((x) => x.endsWith('.jpg'))) {
    require('child_process').execFileSync('python3', ['-c',
      'import sys;from PIL import Image;i=Image.open(sys.argv[1]).convert("RGB");w=1150\nif i.width>w:i=i.resize((w,round(i.height*w/i.width)),Image.LANCZOS)\ni.save(sys.argv[2],quality=70,optimize=True,progressive=True)',
      CAPT + f, LITE + f]);
  }
  // la ilustración de la portada: ya con su sombra y sobre el verde de la portada, en JPEG (el PNG con sombra pesaba 1 MB)
  for (const g of Object.values(GUIAS)) {
    const destino = LITE + 'portada-' + path.basename(g.img, path.extname(g.img)) + '.jpg';
    if (fs.existsSync(destino)) continue;
    require('child_process').execFileSync('python3', ['-c', [
      'import sys',
      'from PIL import Image, ImageFilter',
      'i=Image.open(sys.argv[1]).convert("RGBA")',
      'w=900; i=i.resize((w,round(i.height*w/i.width)),Image.LANCZOS)',
      'm=90; lienzo=Image.new("RGBA",(i.width+2*m,i.height+2*m),(52,72,62,255))',
      'sombra=Image.new("RGBA",lienzo.size,(0,0,0,0)); a=i.split()[3].point(lambda v:int(v*0.45))',
      'negro=Image.new("RGBA",i.size,(0,0,0,255)); negro.putalpha(a); sombra.paste(negro,(m,m+40),negro)',
      'sombra=sombra.filter(ImageFilter.GaussianBlur(28)); lienzo=Image.alpha_composite(lienzo,sombra)',
      'lienzo.alpha_composite(i,(m,m)); lienzo.convert("RGB").save(sys.argv[2],quality=82,optimize=True)',
    ].join('\n'), PUB + g.img, destino]);
  }
}

// ---------------------------------------------------------------- contenido
// para el PDF se usa una copia más liviana de cada captura (ver aligerar() abajo)
const LITE = CAPT.replace(/\/$/, '') + '-lite/';
const img = (n) => fs.existsSync(LITE + n + '.jpg') ? 'file://' + LITE + n + '.jpg' : (fs.existsSync(CAPT + n + '.jpg') ? 'file://' + CAPT + n + '.jpg' : '');
const pub = (r) => 'file://' + PUB + r;
const captura = (n, pie, clase = '') => img(n) ? `<figure class="captura ${clase}"><div class="barra"><i></i><i></i><i></i><span>tampu.onrender.com</span></div><img src="${img(n)}"><figcaption>${pie}</figcaption></figure>` : '';
const pasos = (lista) => `<ol class="pasos">${lista.map((t) => `<li>${t}</li>`).join('')}</ol>`;
const consejo = (t, tipo = 'consejo') => `<aside class="nota ${tipo}"><img src="${pub('img/asistente/tampu-bot.webp')}"><div><b>${{ consejo: 'Consejo de Tampu', ojo: '¡Ojo!', dato: 'Dato útil' }[tipo]}</b>${t}</div></aside>`;
const capitulo = (n, color, titulo, intro, cuerpo) => `<section class="capitulo" style="--c:${color}"><header><span class="num">${String(n).padStart(2, '0')}</span><div><h2>${titulo}</h2><p class="intro">${intro}</p></div></header>${cuerpo}</section>`;
const C = ['#465548', '#965c57', '#53677a', '#86652f', '#34483e', '#a66d53', '#5d6b5a', '#6b5f3a'];

const BLOQUES = {
  empezar: (n) => capitulo(n, C[0], 'Crea tu cuenta', 'Toma dos minutos, y es gratis para todos.',
    `<div class="dos">${pasos([
      'Entra a <b>tampu.onrender.com</b> y toca <b>Iniciar sesión → Crear cuenta</b>.',
      'Elige qué eres: <b>estudiante</b>, <b>arrendador</b> (alquilas cuartos) o <b>empleador</b> (ofreces trabajo).',
      'Pon tu nombre, tu correo y una contraseña. También puedes entrar con <b>Google</b> o <b>Microsoft</b>.',
      'Revisa tu correo: te llega un <b>código</b> para confirmar que es tuyo.',
      'Si eres arrendador o empleador, sube una foto de tu <b>DNI o RUC</b>. El administrador la revisa y te avisa.'])}
    ${captura('registro', 'El formulario de registro.')}</div>
    ${consejo('Si eres estudiante y entras con el correo de tu universidad (por ejemplo <b>@unsaac.edu.pe</b>), tu cuenta queda como <b>estudiante verificado</b> y los arrendadores confían más en ti.')}`),

  buscar: (n) => capitulo(n, C[1], 'Busca tu cuarto', 'Filtra por lo que de verdad te importa: dónde, cuánto y cómo.',
    `${captura('filtros', 'Habitaciones: a la izquierda los filtros, arriba los distritos y el mapa con los precios.')}
    ${pasos(['Elige el <b>distrito</b> o la universidad que tienes cerca.', 'Pon el <b>precio máximo</b> que puedes pagar al mes.',
      'Elige el <b>tipo</b>: cuarto individual, minidepartamento, cuarto compartido, casa de estudiantes o depa con roomies.',
      'Toca <b>Filtrar</b>. En el mapa, pasa el dedo o el mouse por un punto para ver el precio.'])}
    <p>Cada tarjeta te dice si el cuarto está <span class="chip verde">✔ Verificado</span>, si ya lo <span class="chip ocre">Visitaron y confirmaron</span> otros estudiantes y, si armaste tu presupuesto, si <span class="chip verde">entra en tu presupuesto</span> o cuánto te pasas.</p>
    <div class="dos">${captura('detalle', 'La ficha del cuarto: fotos, precio, qué incluye y el arrendador.')}${captura('distancias', 'A cuántos minutos queda cada universidad, a pie o en bus, y qué líneas te llevan.')}</div>
    ${captura('alrededor', 'Lo que hay cerca: paradero, comisaría y centro de salud.', 'angosta')}`),

  presupuesto: (n) => capitulo(n, C[2], '¿Me alcanza? Arma tu presupuesto', 'Antes de mudarte, haz cuentas. Te ahorrará más de un susto a fin de mes.',
    `<div class="dos">${pasos(['Abre <b>¿Me alcanza?</b> en el menú.', 'Pon cuánto <b>recibes</b> (al mes o a la semana) y cuánto quieres <b>ahorrar</b>.',
      'Revisa tus gastos: comida, pasajes, luz e internet, celular… Cámbialos o agrega los tuyos.',
      'TAMPU te dice <b>hasta cuánto puedes pagar de cuarto</b>.', 'Toca <b>Ver cuartos que entran</b> o imprímelo para enseñárselo a tu familia.'])}
    ${captura('presupuesto', 'Mi presupuesto: lo que recibes, tus gastos y tu tope para el cuarto.')}</div>
    ${consejo('Si inicias sesión, tu presupuesto se guarda en tu cuenta y lo ves en cualquier celular. Y en la lista de cuartos aparece «Entra en tu presupuesto» en los que puedes pagar.')}`),

  visita: (n) => capitulo(n, C[3], 'Pide la visita (y nunca pagues sin ver)', 'Esta es la regla de oro: primero conoces el cuarto, después pagas.',
    `<div class="dos">${captura('est-reservar', 'En la ficha del cuarto eliges el día y escribes un mensaje.')}
    ${pasos(['En el cuarto que te gusta, elige una <b>fecha</b> y escribe un mensaje corto (de dónde vienes, qué estudias).', 'Toca <b>Solicitar visita</b>.',
      'Cuando el arrendador acepta, te llega un aviso (y un correo) y ya ves su <b>celular</b> para coordinar.',
      'Descarga la <b>constancia de visita</b> en PDF si la necesitas.',
      'Después de ir, responde <b>«¿Era como en las fotos?»</b> en tu panel. Si dices que sí, el cuarto gana el sello <b>Visitado y confirmado</b>.'])}</div>
    ${consejo('Si alguien te pide un adelanto sin dejarte ver el cuarto, o te apura con «quedan pocos», desconfía. Usa el botón <b>Reportar</b> y el administrador lo revisa.', 'ojo')}
    <div class="lista-chequeo"><h3>Antes de pagar, revisa:</h3><ul>
      <li>Que el <b>agua caliente</b> funcione y tenga buena presión.</li><li>La señal del <b>wifi</b> y del celular.</li><li>Si entra <b>luz natural</b> y si hay humedad en las paredes.</li>
      <li>Las <b>cerraduras</b> y quién más tiene llave.</li><li>El <b>ruido</b> de la calle de noche.</li><li>Qué incluye el precio: <b>luz, agua, internet</b>.</li>
      <li>Pide <b>recibo</b> o un contrato simple con el precio y la garantía.</li><li>Cuánto demoras a tu <b>universidad</b>.</li></ul></div>`),

  trabajo: (n) => capitulo(n, C[4], 'Trabajo que respeta tus clases', 'Marca tu horario una vez y olvídate de las ofertas que chocan con tus clases.',
    `<div class="dos">${pasos(['En <b>Mi panel</b>, marca los turnos (mañana, tarde o noche) en los que tienes clases y toca <b>Guardar horario</b>.',
      'En <b>Trabajos</b>, activa <b>Solo compatibles con mi horario</b>.', 'Abre una oferta: mira el horario, el pago y los requisitos.',
      'Toca <b>Postular</b> y escribe por qué te interesa. Te avisamos cuando respondan.', 'Si te aceptan, el turno aparece en tu <b>calendario</b>.'])}
    ${captura('est-tabla-horario', 'Tu horario de clases: marca los turnos ocupados.')}</div>
    ${captura('est-trabajos', 'Trabajos compatibles: solo los que no chocan con tus clases.')}`),

  mensajes: (n) => capitulo(n, C[5], 'Mensajes y el asistente Tampu', 'Pregunta lo que quieras, sin dar tu número.',
    `<div class="dos">${captura('est-mensajes', 'Mensajes con respuestas rápidas: un toque y se envía.')}${captura('asistente', 'El asistente responde al instante con cuartos y trabajos reales.')}</div>
    ${pasos(['En cada cuarto o trabajo toca <b>Enviar mensaje</b>. Tu número queda privado.', 'Usa las <b>respuestas rápidas</b> para preguntar lo importante con un toque.',
      'Toca a <b>Tampu</b> (abajo a la derecha) y escríbele como a un amigo: «cuarto en Wanchaq hasta 400», «trabajo los sábados», «¿cuánto cuesta vivir en Santiago?».'])}
    ${consejo('Si le escribes «guía» al asistente, te manda esta misma guía en PDF.', 'dato')}`),

  cuenta: (n) => capitulo(n, C[6], 'Tu cuenta, tus avisos y tu seguridad', 'Unos ajustes rápidos que valen la pena.',
    `<div class="dos">${pasos(['En <b>Mi perfil</b> cambias tu foto, tu celular y tu contraseña.', 'Elige si quieres recibir los <b>avisos por correo</b>.',
      'Activa la <b>verificación en dos pasos</b>: así nadie entra aunque sepa tu contraseña.', 'Instala TAMPU en tu celular: en el navegador toca <b>Agregar a la pantalla de inicio</b>. Funciona como una app.'])}
    ${captura('perfil', 'Mi perfil: datos, avisos por correo y seguridad.')}</div>`),

  arrendador: (n) => capitulo(n, C[1], 'Publica tu cuarto', 'Gratis, y llega directo a estudiantes que buscan cerca de su universidad.',
    `${captura('arr-panel', 'Tu panel: cuartos publicados, solicitudes y reseñas.')}
    ${pasos(['Espera a que el administrador <b>verifique tu DNI o RUC</b> (te llega un aviso).', 'Toca <b>Nueva habitación</b>.',
      'Escribe un <b>título claro</b> («Cuarto con baño propio cerca de la UNSAAC») y una descripción honesta.',
      'Pon el <b>precio</b>, la <b>garantía</b>, el tamaño, el piso y qué incluye (agua y luz, wifi, agua caliente…).',
      'Marca la <b>ubicación en el mapa</b>: así los estudiantes ven a cuántos minutos queda su universidad y qué buses pasan.', 'Sube <b>fotos reales</b> con buena luz y toca <b>Publicar</b>.'])}
    <div class="dos">${captura('arr-form', 'Nueva habitación: los datos principales.')}${captura('arr-form-mapa', 'Ubicación en el mapa y servicios incluidos.')}</div>
    ${consejo('Las fotos reales y una descripción sincera te traen visitas que terminan en alquiler. Si el estudiante confirma que el cuarto era como en las fotos, ganas el sello <b>Visitado y confirmado</b>.')}`),

  arrendadorSolicitudes: (n) => capitulo(n, C[3], 'Responde las visitas', 'Acepta, coordina y recibe tu reseña.',
    `${captura('arr-solicitudes', 'Solicitudes de visita: aceptas o rechazas con un toque.')}
    ${pasos(['En <b>Solicitudes</b> ves quién quiere visitar, qué día y su mensaje.', 'Toca <b>Aceptar</b>: el estudiante ve tu celular y le llega un aviso por correo.',
      'Coordina por <b>Mensajes</b> con las respuestas rápidas («Sí, sigue disponible», «¿Qué día te queda bien?»).', 'Después de la visita, el estudiante puede dejarte una <b>reseña</b>.'])}`),

  empleador: (n) => capitulo(n, C[4], 'Publica una oferta de trabajo', 'Encuentra estudiantes que sí pueden cumplir tu horario.',
    `${captura('emp-panel', 'Tu panel: ofertas activas y postulaciones.')}
    ${pasos(['Espera la <b>verificación</b> de tu RUC o DNI.', 'Toca <b>Nueva oferta</b>.', 'Escribe el puesto, la empresa, la modalidad (por horas, medio tiempo, prácticas) y el <b>pago</b> (por hora, día o mes).',
      'Marca los <b>turnos</b> del trabajo: los estudiantes con clases a esa hora no la verán como compatible.', 'Publica y espera las postulaciones.'])}
    <div class="dos">${captura('emp-form', 'Nueva oferta: puesto, pago y modalidad.')}${captura('emp-horario', 'Los turnos del trabajo.')}</div>
    ${consejo('Un pago claro y un horario real atraen más postulaciones. Los estudiantes en Cusco suelen ganar entre S/ 8 y S/ 15 la hora.', 'dato')}`),

  empleadorPostulaciones: (n) => capitulo(n, C[2], 'Elige a tu equipo', 'Revisa, acepta y coordina.',
    `${captura('emp-postulaciones', 'Postulaciones: quién se interesó y su mensaje.')}
    ${pasos(['En <b>Postulaciones</b> ves quién se interesó en cada oferta.', 'Acepta o rechaza: el estudiante recibe el aviso al momento.',
      'Si lo aceptas, el turno aparece en su calendario y pueden hablar por <b>Mensajes</b>.'])}`),

  admin: (n) => capitulo(n, C[0], 'Cuida TAMPU', 'El administrador mantiene la confianza de todos.',
    `${captura('adm-panel', 'Panel del administrador: cómo va TAMPU de un vistazo.')}
    ${pasos(['En <b>Usuarios</b> revisa el DNI o RUC de las cuentas nuevas y márcalas como verificadas (o rechaza con motivo).',
      'Filtra por nombre, fechas, tipo de cuenta o estado.', 'En <b>Reportes</b> revisa los avisos de cuartos o fotos falsas: descarta o suspende la cuenta.',
      'En <b>Contacto</b> responde los mensajes del formulario.', 'En <b>Seguridad</b> revisa los inicios de sesión, los bloqueos y los intentos sospechosos.'])}
    <div class="dos">${captura('adm-usuarios', 'Usuarios: verificación y filtros.')}${captura('adm-seguridad', 'Bitácora de seguridad.')}</div>`),
};

const PREGUNTAS = [
  ['¿Usar TAMPU cuesta algo?', 'No. Buscar, postular y publicar es gratis para todos.'],
  ['¿Cómo sé que el arrendador es real?', 'Antes de publicar, el administrador revisa su DNI o RUC. Además, puedes ver las reseñas y el sello «Visitado y confirmado».'],
  ['¿Qué hago si me piden un adelanto sin ver el cuarto?', 'No lo pagues. Pide la visita desde TAMPU y, si insisten, usa el botón Reportar.'],
  ['¿Cuándo veo el celular del arrendador?', 'Cuando acepta tu solicitud de visita. Mientras tanto, conversen por Mensajes.'],
  ['Me olvidé la contraseña', 'En Iniciar sesión toca «¿Olvidaste tu contraseña?» y te llega un enlace a tu correo.'],
  ['¿Puedo usarlo en el celular?', 'Sí. Instálalo desde el navegador con «Agregar a la pantalla de inicio».'],
];

const GUIAS = {
  estudiante: { titulo: 'Guía para estudiantes', sub: 'Encuentra tu cuarto, arma tu presupuesto y consigue trabajo sin descuidar tus clases.', img: 'img/3d/cuarto.webp', color: C[0],
    hola: 'Llegar a Cusco desde tu provincia es emocionante… y también da un poco de miedo: ¿dónde vivir?, ¿cuánto cuesta?, ¿será seguro? Hicimos TAMPU para que no lo vivas solo. En esta guía te contamos, paso a paso y sin palabras raras, cómo usarlo.',
    bloques: ['empezar', 'buscar', 'presupuesto', 'visita', 'trabajo', 'mensajes', 'cuenta'] },
  arrendador: { titulo: 'Guía para arrendadores', sub: 'Publica tu cuarto gratis y alquílalo a estudiantes verificados.', img: 'img/3d/casa.webp', color: C[1],
    hola: 'Gracias por abrir tu casa a quien llega a estudiar. En TAMPU los estudiantes buscan lo mismo que tú: tranquilidad y confianza. Aquí te explicamos cómo publicar y cómo responder las visitas.',
    bloques: ['empezar', 'arrendador', 'arrendadorSolicitudes', 'mensajes', 'cuenta'] },
  empleador: { titulo: 'Guía para empleadores', sub: 'Encuentra estudiantes cuyo horario encaja con tu negocio.', img: 'img/3d/paso-horario.webp', color: C[4],
    hola: 'Muchos estudiantes necesitan trabajar para pagar sus estudios, pero sus clases cambian cada día. TAMPU cruza tu horario con el de ellos para que solo te postulen quienes de verdad pueden cumplir.',
    bloques: ['empezar', 'empleador', 'empleadorPostulaciones', 'mensajes', 'cuenta'] },
  admin: { titulo: 'Guía para el administrador', sub: 'Verificaciones, reportes y seguridad.', img: 'img/3d/casa.webp', color: C[3],
    hola: 'El administrador es quien hace que TAMPU sea un lugar de confianza. Esta guía resume tus herramientas.',
    bloques: ['admin', 'cuenta'] },
  general: { titulo: 'Guía del usuario', sub: 'Todo TAMPU en una guía: para estudiantes, arrendadores y empresas.', img: 'img/3d/cuarto.webp', color: C[2],
    hola: 'TAMPU es una casa de paso digital para estudiantes que llegan a Cusco: cuartos verificados cerca de su universidad y trabajos que respetan sus clases. Comparte esta guía con quien la necesite: está escrita para que cualquiera la entienda en pocos minutos.',
    bloques: ['empezar', 'buscar', 'presupuesto', 'visita', 'trabajo', 'mensajes', 'arrendador', 'arrendadorSolicitudes', 'empleador', 'empleadorPostulaciones', 'cuenta'] },
};

const CSS = `
@font-face{font-family:Fraunces;font-weight:100 900;src:url(file://${NM}@fontsource-variable/fraunces/files/fraunces-latin-full-normal.woff2)}
@font-face{font-family:Fraunces;font-style:italic;font-weight:100 900;src:url(file://${NM}@fontsource-variable/fraunces/files/fraunces-latin-full-italic.woff2)}
${[400, 500, 600, 700, 800].map((w) => `@font-face{font-family:Inter;font-weight:${w};src:url(file://${NM}@fontsource/inter/files/inter-latin-${w}-normal.woff2)}`).join('')}
@page{size:A4;margin:16mm 15mm 18mm}
@page:first{margin:0}
*{box-sizing:border-box}
/* marca de agua: el logo grande y casi transparente en el centro de cada hoja */
.agua{position:fixed;top:50%;left:50%;width:125mm;transform:translate(-50%,-50%);opacity:.045;z-index:-1}
body{font:400 10.6pt/1.55 Inter,sans-serif;color:#343936;margin:0}
h1,h2,h3{font-family:Fraunces,serif;letter-spacing:-.02em;margin:0}
b{font-weight:700;color:#343936}
.aguayo{height:12px;background:repeating-linear-gradient(90deg,#965c57 0 22px,#b58a4a 22px 30px,#465548 30px 44px,#343936 44px 48px,#53677a 48px 62px,#b58a4a 62px 68px,#965c57 68px 86px,#a66d53 86px 96px)}
/* portada */
.portada{height:297mm;background:#34483e;color:#fff;position:relative;overflow:hidden;page-break-after:always;display:flex;flex-direction:column}
.portada .muro{position:absolute;inset:0;opacity:.08;background:repeating-linear-gradient(0deg,transparent 0 46px,#fff 46px 48px),repeating-linear-gradient(90deg,transparent 0 120px,#fff 120px 122px)}
.portada .cuerpo{position:relative;padding:22mm 18mm 0;flex:1}
.portada .logo{display:flex;align-items:center;gap:10px;font:700 15pt Inter;letter-spacing:.02em}
.portada .logo img{width:44px;height:44px;border-radius:50%;background:#fff}
.portada .etq{display:inline-block;margin-top:20mm;background:VAR;color:#fff;padding:6px 14px;border-radius:99px;font:800 8.5pt Inter;letter-spacing:.16em;text-transform:uppercase}
.portada h1{font-size:46pt;line-height:1;margin-top:8mm;font-weight:800}
.portada h1 em{color:#d8cbb5;font-style:italic}
.portada p.sub{font-size:14pt;max-width:120mm;color:#dfe4dc;margin-top:6mm}
.portada .ilus{position:absolute;right:-30mm;bottom:16mm;width:142mm}
.portada .bot{position:absolute;left:18mm;bottom:30mm;display:flex;gap:12px;align-items:center;background:rgba(255,255,255,.1);border-radius:16px;padding:10px 16px 10px 10px;max-width:92mm}
.portada .bot img{width:52px}
.portada .bot span{font-size:10pt;color:#fff}
.portada .pie{position:relative}
/* carta */
.carta{display:grid;grid-template-columns:34mm 1fr;gap:8mm;align-items:start;background:#f5f3ed;border-radius:18px;padding:9mm;margin-bottom:8mm;border:2px solid #d8cbb5}
.carta img{width:34mm}
.carta h2{font-size:22pt;margin-bottom:3mm}
.carta h2 em{color:#965c57}
.carta p{font-size:11.5pt;margin:0 0 3mm}
.carta .firma{font:italic 600 12pt Fraunces;color:#965c57}
.indice{display:grid;grid-template-columns:1fr 1fr;gap:3mm 8mm;margin:0 0 4mm;padding:0;list-style:none;counter-reset:i}
.indice li{display:flex;gap:10px;align-items:center;font-weight:600;padding:3mm 4mm;border-radius:12px;background:#f5f3ed}
.indice li span{width:24px;height:24px;border-radius:7px;color:#fff;display:grid;place-items:center;font:800 9pt Inter;flex:none}
/* capítulos */
.capitulo{page-break-before:always}
.capitulo header{display:flex;gap:6mm;align-items:center;margin-bottom:6mm;padding-bottom:5mm;border-bottom:3px solid var(--c)}
.capitulo .num{font:800 34pt/1 Fraunces;color:#fff;background:var(--c);width:24mm;height:24mm;border-radius:18px;display:grid;place-items:center;flex:none;box-shadow:0 10px 20px -10px var(--c)}
.capitulo h2{font-size:23pt;font-weight:800;color:#343936}
.capitulo .intro{margin:2mm 0 0;font-size:12pt;color:#4b504b}
.pasos{list-style:none;counter-reset:p;padding:0;margin:0 0 6mm}
.pasos li{counter-increment:p;position:relative;padding:2.4mm 0 2.4mm 11mm;break-inside:avoid}
.pasos li::before{content:counter(p);position:absolute;left:0;top:2mm;width:7.5mm;height:7.5mm;border-radius:50%;background:var(--c);color:#fff;font:800 9.5pt/7.5mm Inter;text-align:center}
.dos{display:grid;grid-template-columns:1fr 1fr;gap:6mm;align-items:start;margin-bottom:5mm}
.captura{margin:0 0 6mm;break-inside:avoid;border-radius:12px;overflow:hidden;box-shadow:0 14px 30px -16px rgba(16,35,30,.45);border:1px solid #e6ddcf;background:#fff}
.captura .barra{display:flex;gap:5px;align-items:center;background:#efe9df;padding:5px 9px}
.captura .barra i{width:7px;height:7px;border-radius:50%;background:#d8cfc2}
.captura .barra i:first-child{background:#e46b5d}.captura .barra i:nth-child(2){background:#e7b84c}.captura .barra i:nth-child(3){background:#67b36b}
.captura .barra span{margin-left:8px;font-size:7pt;color:#66665e;background:#fff;border-radius:99px;padding:1px 10px}
.captura img{display:block;width:100%}
.captura figcaption{font-size:8.6pt;color:#5c5c55;padding:6px 10px;border-top:1px solid #f0e9de;background:#fffdf9}
.captura.angosta{max-width:70%;margin-left:auto;margin-right:auto}
.dos .captura img{max-height:110mm;object-fit:cover;object-position:top}
.nota{display:flex;gap:4mm;align-items:center;border-radius:14px;padding:4mm 5mm;margin:0 0 6mm;break-inside:avoid}
.nota img{width:14mm;flex:none}
.nota div > b:first-child{display:block;font:800 10pt Inter;margin-bottom:1mm}
.nota.consejo{background:#e8ece6;border-left:5px solid #465548}
.nota.ojo{background:#f4e7e4;border-left:5px solid #965c57}
.nota.dato{background:#f3ede0;border-left:5px solid #b58a4a}
.chip{display:inline-block;padding:0 7px;border-radius:99px;font-weight:700;font-size:9pt}
.chip.verde{background:#e4e9e2;color:#465548}.chip.ocre{background:#efe7d6;color:#6b5f3a}
.lista-chequeo{background:#fff;border:2px dashed var(--c);border-radius:16px;padding:5mm 7mm;break-inside:avoid}
.lista-chequeo h3{font-size:14pt;margin-bottom:2mm}
.lista-chequeo ul{list-style:none;padding:0;margin:0;columns:2;column-gap:8mm}
.lista-chequeo li{padding:1.4mm 0 1.4mm 8mm;position:relative;break-inside:avoid}
.lista-chequeo li::before{content:"";position:absolute;left:0;top:2.2mm;width:4mm;height:4mm;border:1.6px solid var(--c);border-radius:3px}
/* final */
.final{page-break-before:always}
.final h2{font-size:23pt;margin-bottom:5mm}
.faq{display:grid;grid-template-columns:1fr 1fr;gap:4mm}
.faq div{background:#f5f3ed;border-radius:14px;padding:4mm 5mm;break-inside:avoid}
.faq b{display:block;font:700 11pt Fraunces;margin-bottom:1mm}
.contacto{margin-top:8mm;background:#34483e;color:#fff;border-radius:18px;padding:7mm 8mm;display:grid;grid-template-columns:1fr auto;gap:6mm;align-items:center}
.contacto h3{font-size:17pt;color:#d8cbb5}
.contacto p{margin:1mm 0;color:#dfe4dc}
.contacto b{color:#fff}
.contacto img{width:30mm}
`;

function html(clave) {
  const g = GUIAS[clave];
  const titulos = g.bloques.map((b) => (BLOQUES[b](0).match(/<h2>(.*?)<\/h2>/) || [])[1]);
  const cuerpo = g.bloques.map((b, i) => BLOQUES[b](i + 1)).join('');
  const [t1, ...t2] = g.titulo.split(' ');
  return `<!doctype html><html lang="es"><head><meta charset="utf-8"><style>${CSS.replace('VAR', g.color)}</style></head><body>
  <img class="agua" src="${pub('img/logo-160.png')}">
  <div class="portada"><div class="muro"></div><div class="cuerpo">
    <div class="logo"><img src="${pub('img/logo-160.png')}">Tampu <span style="opacity:.6;font-weight:500;font-size:10pt;margin-left:6px">Cusco · estudiantes</span></div>
    <span class="etq">Guía en PDF · ${new Date().getFullYear()}</span>
    <h1>${t1} <em>${t2.join(' ')}</em></h1>
    <p class="sub">${g.sub}</p>
  </div>
  <img class="ilus" src="${fs.existsSync(LITE + 'portada-' + path.basename(g.img, path.extname(g.img)) + '.jpg') ? 'file://' + LITE + 'portada-' + path.basename(g.img, path.extname(g.img)) + '.jpg' : pub(g.img)}">
  <div class="bot"><img src="${pub('img/asistente/tampu-bot.webp')}"><span>¡Hola! Soy <b style="color:#d8cbb5">Tampu</b>. Te acompaño en cada paso.</span></div>
  <div class="pie aguayo"></div></div>
  <div class="carta"><img src="${pub('img/asistente/tampu-bot.webp')}"><div><h2>¡Bienvenido, <em>bienvenida</em>!</h2><p>${g.hola}</p>
    <p>Si en algún momento te pierdes, toca el botón <b>Guía</b> en la página o escríbele a <b>Tampu</b>, el asistente. Siempre hay alguien para ayudarte.</p>
    <p class="firma">— El equipo de TAMPU</p></div></div>
  <h3 style="font-size:14pt;margin-bottom:3mm">En esta guía</h3>
  <ol class="indice">${titulos.map((t, i) => `<li><span style="background:${C[i % C.length]}">${i + 1}</span>${t}</li>`).join('')}</ol>
  ${cuerpo}
  <section class="final"><h2>Preguntas que todos hacen</h2>
    <div class="faq">${PREGUNTAS.map(([p, r]) => `<div><b>${p}</b>${r}</div>`).join('')}</div>
    <div class="contacto"><div><h3>¿Necesitas ayuda?</h3><p>Escríbenos desde <b>Contacto</b> en la página o a <b>tampu.soporte@gmail.com</b>.</p>
      <p>Pregúntale a <b>Tampu</b>, el asistente, a cualquier hora.</p><p style="margin-top:3mm">Y recuerda: <b>nunca pagues un adelanto sin visitar el cuarto.</b></p></div>
      <img src="${pub('img/asistente/tampu-bot.webp')}"></div>
  </section></body></html>`;
}

(async () => {
  const b = await chromium.launch({ args: ['--use-gl=angle', '--use-angle=swiftshader', '--enable-unsafe-swiftshader'] });
  if (!process.env.SOLO_PDF) await capturar(b);
  aligerar();
  const archivos = { estudiante: 'guia-estudiante', arrendador: 'guia-arrendador', empleador: 'guia-empleador', admin: 'guia-administrador', general: 'guia-tampu' };
  for (const [clave, nombre] of Object.entries(archivos)) {
    const p = await b.newPage();
    const ruta = '/home/claude/guias/' + nombre + '.html';
    fs.writeFileSync(ruta, html(clave));
    await p.goto('file://' + ruta, { waitUntil: 'load' }); await p.waitForTimeout(600);
    await p.pdf({ path: SALIDA + nombre + '.pdf', format: 'A4', printBackground: true, displayHeaderFooter: true,
      headerTemplate: '<span></span>',
      footerTemplate: `<div style="width:100%;font:7.5px Inter,sans-serif;color:#77776f;padding:0 15mm;display:flex;justify-content:space-between"><span>TAMPU · ${GUIAS[clave].titulo}</span><span><span class="pageNumber"></span> / <span class="totalPages"></span></span></div>` });
    await p.close();
    console.log('PDF', nombre, Math.round(fs.statSync(SALIDA + nombre + '.pdf').size / 1024) + ' KB');
  }
  await b.close();
})();
