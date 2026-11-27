// Videos guía de TAMPU: la página usada de verdad (cursor, clics, texto que se escribe) con subtítulos.
// Los datos personales (nombres, correos, celulares, DNI y fotos de perfil) salen censurados.
//   node grabar.js [visitante,estudiante,arrendador,empleador,admin]
// Necesita la página corriendo en 127.0.0.1:8099 y nombres.json (lista de nombres a censurar).
const { chromium } = require('playwright');
const fs = require('fs');
const F = '/tmp/bs/node_modules/';
const BASE = 'http://127.0.0.1:8099';
const SALIDA = '/home/claude/vguia/';
const NOMBRES = JSON.parse(fs.readFileSync(process.env.NOMBRES || '/tmp/nombres.json', 'utf8'));

const FONTCSS = `@font-face{font-family:'Fraunces';font-weight:100 900;src:url(https://fonts.gstatic.com/l/fraunces.woff2) format('woff2')}` +
  [400, 500, 600, 700].map(w => `@font-face{font-family:'Inter';font-weight:${w};src:url(https://fonts.gstatic.com/l/inter-${w}.woff2) format('woff2')}`).join('') +
  [600, 700].map(w => `@font-face{font-family:'Montserrat';font-weight:${w};src:url(https://fonts.gstatic.com/l/montserrat-${w}.woff2) format('woff2')}`).join('');
async function rutas(c) {
  await c.route('**/cdn.jsdelivr.net/npm/**', (r) => { const u = r.request().url();
    if (u.includes('model-viewer')) return r.fulfill({ path: F + '@google/model-viewer/dist/model-viewer.min.js', contentType: 'application/javascript' });
    const m = u.match(/npm\/(bootstrap(?:-icons)?|leaflet)@[^/]+\/(.*)$/); if (!m) return r.abort(); return r.fulfill({ path: F + m[1] + '/' + m[2].split('?')[0] }); });
  await c.route(/gstatic\.com\/draco/, r => r.fulfill({ path: F + 'three/examples/jsm/libs/draco/gltf/' + r.request().url().split('/').pop() }));
  await c.route(/fonts\.googleapis\.com/, r => r.fulfill({ contentType: 'text/css', body: FONTCSS }));
  await c.route(/fonts\.gstatic\.com\/l\//, r => { const u = r.request().url(); let f;
    if (u.includes('fraunces')) f = F + '@fontsource-variable/fraunces/files/fraunces-latin-full-normal.woff2';
    else if (u.includes('inter-')) f = F + '@fontsource/inter/files/inter-latin-' + u.match(/inter-(\d+)/)[1] + '-normal.woff2';
    else f = F + '@fontsource/montserrat/files/montserrat-latin-' + u.match(/montserrat-(\d+)/)[1] + '-normal.woff2';
    return r.fulfill({ path: f, contentType: 'font/woff2' }); });
  await c.route(/tile\.openstreetmap\.org/, r => r.fulfill({ path: '/home/claude/vguia/tile.png', contentType: 'image/png' }));
}

// se ejecuta en cada página: subtítulo, cursor y censura de datos personales
function escenario(nombres) {
  const css = `
    #demoSub{position:fixed;left:50%;bottom:28px;transform:translateX(-50%);max-width:880px;z-index:2147483646;background:rgba(18,38,33,.92);color:#fff;
      font:500 21px/1.4 Inter,system-ui,sans-serif;padding:12px 22px;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.25);text-align:center;transition:opacity .35s}
    #demoSub b{color:#f0c46a;font-weight:700}
    #demoSub:empty{opacity:0}
    #demoCursor{position:fixed;left:640px;top:360px;width:26px;height:26px;z-index:2147483647;pointer-events:none;transition:left .7s cubic-bezier(.4,0,.2,1),top .7s cubic-bezier(.4,0,.2,1)}
    #demoCursor svg{filter:drop-shadow(0 2px 3px rgba(0,0,0,.35))}
    #demoCursor.clic::after{content:"";position:absolute;left:-14px;top:-14px;width:28px;height:28px;border-radius:50%;border:3px solid #b0502a;animation:onda .45s ease-out}
    @keyframes onda{from{transform:scale(.3);opacity:1}to{transform:scale(1.6);opacity:0}}
    .censura{filter:blur(6px);user-select:none}
    .bienvenida-avatar, img[src*="uploads/perfiles"], .chat-avatar, .avatar, .mensaje-avatar { filter: blur(7px) !important; }
    .asistente-aviso, .guia-invitacion, #btnGuia, #btnMusica { display:none !important; }`;
  const poner = () => {
    if (!document.head) return;
    if (!document.getElementById('demoCss')) { const s = document.createElement('style'); s.id = 'demoCss'; s.textContent = css; document.head.appendChild(s); }
    if (!document.getElementById('demoSub')) { const d = document.createElement('div'); d.id = 'demoSub'; document.body.appendChild(d); }
    if (!document.getElementById('demoCursor')) { const d = document.createElement('div'); d.id = 'demoCursor';
      d.innerHTML = '<svg viewBox="0 0 24 24" width="26" height="26"><path d="M3 2l7.5 19 2.6-7.4L21 11z" fill="#fff" stroke="#17352f" stroke-width="1.6" stroke-linejoin="round"/></svg>';
      const p = sessionStorage.getItem('demoCursor'); if (p) { const [x, y] = p.split(','); d.style.left = x + 'px'; d.style.top = y + 'px'; }
      document.body.appendChild(d); }
  };
  // nombres completos y por separado (nombre y apellidos), correos, celulares y DNI
  const partes = new Set();
  nombres.filter((n) => !/^(Casa del|Residencial|Administrador|Hostal|Inversiones)/i.test(n)).forEach((n) => { partes.add(n); n.split(/\s+/).forEach((p) => { if (p.length > 3 && !/^(TAMPU|Administrador|Casa|Hostal|Cusco|Centro|Andina|Wasi|Dra\.|Ing\.|Arq\.|María)$/i.test(p)) partes.add(p); }); });
  const escapar = (s) => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  const re = new RegExp('([\\w.+-]+@[\\w-]+\\.[\\w.]+)|(\\b9\\d{2}[\\s-]?\\d{3}[\\s-]?\\d{3}\\b)|(\\b\\d{8}\\b)|\\b(' + [...partes].sort((a, b) => b.length - a.length).map(escapar).join('|') + ')\\b', 'g');
  const censurar = (raiz) => {
    const w = document.createTreeWalker(raiz, NodeFilter.SHOW_TEXT, { acceptNode: (n) =>
      n.parentElement && !n.parentElement.closest('script,style,#demoSub,.censura,input,textarea') && re.test(n.nodeValue) ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT });
    const nodos = []; while (w.nextNode()) nodos.push(w.currentNode);
    nodos.forEach((n) => {
      re.lastIndex = 0;
      const span = document.createElement('span');
      span.innerHTML = n.nodeValue.replace(/[&<>]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c])).replace(re, (m) => '<span class="censura">' + m + '</span>');
      n.replaceWith(...span.childNodes);
    });
    // el saludo del panel no dice el nombre
    document.querySelectorAll('.bienvenida h1').forEach((h) => { if (/^Hola/.test(h.textContent)) h.textContent = '¡Hola! 👋'; });
    document.querySelectorAll('input[type=email], input[name=dni], input[name=celular]').forEach((i) => i.classList.add('censura'));
  };
  const arrancar = () => {
    poner(); censurar(document.body);
    new MutationObserver((ms) => ms.forEach((m) => m.addedNodes.forEach((n) => { if (n.nodeType === 1 && !n.closest?.('#demoSub,#demoCursor')) censurar(n); }))).observe(document.body, { childList: true, subtree: true });
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', arrancar); else arrancar();
  window.__sub = (t) => { poner(); document.getElementById('demoSub').innerHTML = t; };
  window.__mover = (x, y, clic) => {
    poner(); const c = document.getElementById('demoCursor'); c.style.left = x + 'px'; c.style.top = y + 'px';
    sessionStorage.setItem('demoCursor', x + ',' + y);
    if (clic) setTimeout(() => { c.classList.remove('clic'); void c.offsetWidth; c.classList.add('clic'); }, 700);
  };
}

// ---------------- acciones ----------------
const pausa = (p, ms) => p.waitForTimeout(ms);
async function sub(p, texto, ms = 0) { await p.evaluate((t) => window.__sub(t), texto); if (ms) await pausa(p, ms); }
async function ir(p, url, texto, ms = 3200) {
  await p.goto(BASE + url, { waitUntil: 'networkidle' }).catch(() => {});
  await pausa(p, 500);
  if (texto) await sub(p, texto, ms);
}
async function apuntar(p, sel, clic = false) {
  const el = p.locator(sel).first();
  if (!(await el.count())) { console.log('   (no está:', sel, ')'); return null; }
  await el.evaluate((e) => e.scrollIntoView({ block: 'center', behavior: 'smooth' }));
  await pausa(p, 700);
  const b = await el.boundingBox(); if (!b) return null;
  await p.evaluate(([x, y, c]) => window.__mover(x, y, c), [b.x + b.width / 2, b.y + b.height / 2, clic]);
  await pausa(p, 850);
  return el;
}
async function clic(p, sel, texto, ms = 2600) {
  if (texto) await sub(p, texto);
  const el = await apuntar(p, sel, true); if (!el) return;
  await Promise.all([p.waitForLoadState('networkidle').catch(() => {}), el.click().catch(() => {})]);
  await pausa(p, 600);
  if (texto) await sub(p, texto, ms);
}
async function escribir(p, sel, valor, texto) {
  if (texto) await sub(p, texto);
  const el = await apuntar(p, sel, true); if (!el) return;
  await el.click(); await el.fill('');
  await el.pressSequentially(String(valor), { delay: 95 });
  await pausa(p, 700);
}
async function elegir(p, sel, valor, texto) {
  if (texto) await sub(p, texto);
  const el = await apuntar(p, sel, true); if (!el) return;
  await el.selectOption({ label: valor }).catch(() => el.selectOption(valor));
  await pausa(p, 900);
}
async function mirar(p, sel, texto, ms = 3400) {
  if (texto) await sub(p, texto);
  await apuntar(p, sel);
  await pausa(p, ms);
}
async function bajar(p, px, ms = 1400) {
  await p.evaluate((y) => window.scrollBy({ top: y, behavior: 'smooth' }), px);
  await pausa(p, ms);
}

// ---------------- guiones por rol ----------------
const GUIONES = {
  visitante: { cuenta: null, pasos: async (p) => {
    await ir(p, '/', '<b>TAMPU</b>: cuartos verificados y trabajos que encajan con tus clases, para quien llega a Cusco.', 3600);
    await p.evaluate(() => document.getElementById('armarCuarto')?.scrollIntoView());
    await sub(p, 'Baja y mira cómo se <b>arma tu cuarto</b> paso a paso.');
    for (let i = 0; i < 9; i++) await bajar(p, 330, 900);
    await pausa(p, 1200);
    await ir(p, '/habitaciones', 'En <b>Habitaciones</b> filtras por distrito, precio y tipo de cuarto.', 2400);
    await elegir(p, '#distrito', 'Wanchaq', 'Elige el <b>distrito</b>…');
    await escribir(p, '#precioMax', 450, '…y cuánto puedes pagar como <b>máximo</b>.');
    await clic(p, '.filtros button[type=submit]', 'Toca <b>Filtrar</b>: solo ves lo que te sirve.', 2600);
    await clic(p, '.tarjeta a.btn', 'Cada cuarto tiene fotos, ubicación, reseñas y el sello de los que ya lo visitaron.', 3000);
    await mirar(p, '.alrededor', 'Mira qué hay cerca: <b>paradero</b>, comisaría y centro de salud.', 3200);
    await mirar(p, '.lineas-bus', 'Y qué <b>buses</b> te llevan a tu universidad.', 3400);
    await ir(p, '/me-alcanza', '¿No sabes si te alcanza? Arma tu <b>presupuesto</b> en un minuto.', 2400);
    await escribir(p, '#pIngreso', 1100, 'Pon cuánto recibes al mes…');
    await clic(p, '.presu-sugeridos button', 'Agrega los gastos que sueles olvidar…', 1200);
    await mirar(p, '#pGrande', '…y TAMPU te dice <b>hasta cuánto puedes pagar de cuarto</b>.', 3600);
    await clic(p, '#pVerCuartos', 'Con un toque ves <b>solo los cuartos que entran</b> en tu presupuesto.', 3400);
    await sub(p, 'Crea tu cuenta gratis y empieza. <b>¡Bienvenido a Cusco!</b>', 3000);
  } },
  estudiante: { cuenta: 'luis@tampu.pe:123456', pasos: async (p) => {
    await ir(p, '/estudiante', 'Este es <b>tu panel</b>: tus visitas, favoritos y tu horario de clases.', 3400);
    await mirar(p, 'table.horario', 'Marca los turnos en los que <b>tienes clases</b>…', 1600);
    for (const sel of ['table.horario input[type=checkbox] >> nth=0', 'table.horario input[type=checkbox] >> nth=4']) {
      const el = await apuntar(p, sel, true); if (el) { await el.click().catch(() => {}); await pausa(p, 500); await el.click().catch(() => {}); }
    }
    await sub(p, '…y TAMPU te muestra solo los trabajos que <b>no chocan</b> con ellas.', 2000);
    await ir(p, '/trabajos?compatible=1', 'Trabajos <b>compatibles con tu horario</b>: por horas, fines de semana o prácticas.', 3200);
    await clic(p, 'a.ver-mas, a.tarjeta-trabajo', 'Abre una oferta, revisa el horario y el pago, y <b>postula</b> con un mensaje corto.', 3400);
    await ir(p, '/habitaciones', 'Cuando ves un cuarto que te gusta, pide la <b>visita</b> antes de pagar nada.', 2400);
    await clic(p, '.tarjeta a.btn', null, 1000);
    await mirar(p, '#reservar', 'Elige el día y escríbele al arrendador. Cuando acepte, verás su celular.', 3600);
    await ir(p, '/mensajes', 'En <b>Mensajes</b> conversas sin dar tu número…', 1800);
    await clic(p, '.chat-item', '…y con las <b>respuestas rápidas</b> preguntas lo importante con un toque.', 1200);
    await mirar(p, '.chat-rapidos', null, 2600);
    await clic(p, '#btnAsistente', '¿Dudas? Pregúntale a <b>Tampu</b>, el asistente.', 800);
    await escribir(p, '#asistenteTexto', 'cuarto en Wanchaq hasta 400');
    await p.keyboard.press('Enter');
    await sub(p, 'Te responde al instante con cuartos y trabajos reales.', 4200);
  } },
  arrendador: { cuenta: 'rosa@tampu.pe:123456', pasos: async (p) => {
    await ir(p, '/arrendador/habitaciones', 'Tu panel de <b>arrendador</b>: tus cuartos publicados y sus visitas.', 3400);
    await clic(p, 'a[href$="/arrendador/habitaciones/create"], a[href*="habitaciones/crear"]', 'Publicar un cuarto es <b>gratis</b>.', 1400);
    await escribir(p, '#titulo', 'Cuarto con baño propio cerca de la UNSAAC', 'Pon un título claro…');
    await mirar(p, 'input[name=precio], #precio', '…el precio, qué incluye (agua, luz, wifi) y las fotos.', 2800);
    await mirar(p, '#mapaForm, .mapa, #mapa', 'Marca la ubicación: los estudiantes ven a cuántos minutos queda su universidad.', 3200);
    await ir(p, '/arrendador/solicitudes', 'En <b>Solicitudes</b> aceptas o rechazas las visitas.', 3600);
    await mirar(p, 'table, .tarjetas, .vacio', 'Si aceptas, el estudiante ve tu celular y recibe un aviso por correo.', 3400);
    await ir(p, '/mensajes', 'Y conversas con ellos desde <b>Mensajes</b>, con respuestas rápidas.', 2000);
    await clic(p, '.chat-item', null, 1400);
    await mirar(p, '.chat-rapidos', null, 2400);
  } },
  empleador: { cuenta: 'wayra@tampu.pe:123456', pasos: async (p) => {
    await ir(p, '/empleador/trabajos', 'Tu panel de <b>empleador</b>: tus ofertas y cuántos postularon.', 3400);
    await clic(p, 'a[href$="/empleador/trabajos/create"], a[href*="trabajos/crear"]', 'Publica una oferta <b>gratis</b>.', 1400);
    await escribir(p, '#titulo', 'Mozo/a fines de semana', 'Escribe el puesto…');
    await mirar(p, 'table.horario, .horario', '…y marca los <b>turnos</b>: los estudiantes con clases a esa hora no la verán como compatible.', 3600);
    await ir(p, '/empleador/postulaciones', 'En <b>Postulaciones</b> ves quién se interesó.', 3200);
    await mirar(p, 'table, .tarjetas, .vacio', 'Acepta o rechaza; el estudiante recibe el aviso al momento.', 3600);
  } },
  admin: { cuenta: 'admin@tampu.pe:admin123', pasos: async (p) => {
    await ir(p, '/admin', 'Panel del <b>administrador</b>: cómo va TAMPU de un vistazo.', 3600);
    await bajar(p, 420, 2200);
    await ir(p, '/admin/usuarios', 'En <b>Usuarios</b> revisas cuentas y documentos.', 2000);
    await escribir(p, 'input[name=buscar]', 'Cusco', 'Busca por nombre, correo, DNI o ciudad…');
    await p.keyboard.press('Enter'); await p.waitForLoadState('networkidle').catch(() => {});
    await sub(p, '…y filtra por fechas, estado o tipo de cuenta.', 3000);
    await ir(p, '/admin/reportes', 'En <b>Reportes</b> llegan los avisos de cuartos o fotos falsas.', 3400);
    await ir(p, '/admin/seguridad', 'Y en <b>Seguridad</b> ves los inicios de sesión y los intentos sospechosos.', 3800);
  } },
};

(async () => {
  const b = await chromium.launch({ args: ['--use-gl=angle', '--use-angle=swiftshader', '--enable-unsafe-swiftshader'] });
  for (const rol of (process.argv[2] || Object.keys(GUIONES).join(',')).split(',')) {
    const g = GUIONES[rol];
    const base = await b.newContext({ viewport: { width: 1280, height: 720 } }); await rutas(base);
    if (g.cuenta) {
      const p = await base.newPage(); const [m, pw] = g.cuenta.split(':');
      await p.goto(BASE + '/login'); await p.fill('input[name=email]', m); await p.fill('input[name=password]', pw);
      await Promise.all([p.waitForNavigation(), p.click('form button[type=submit]')]); await p.close();
    }
    const estado = await base.storageState(); await base.close();
    fs.mkdirSync(SALIDA + 'crudo/' + rol, { recursive: true });
    const c = await b.newContext({ viewport: { width: 1280, height: 720 }, storageState: estado, recordVideo: { dir: SALIDA + 'crudo/' + rol, size: { width: 1280, height: 720 } } });
    await rutas(c);
    await c.addInitScript(escenario, NOMBRES);
    await c.addInitScript(() => { try { localStorage.setItem('guia.voz', '0'); Object.keys(localStorage).forEach(() => {}); } catch (e) {} });
    // la guía automática no aparece en los videos
    await c.addInitScript(() => { try { for (const r of ['estudiante.panel', 'arrendador.habitaciones.index', 'empleador.trabajos.index', 'admin.panel', 'inicio'])
      for (const x of ['estudiante', 'arrendador', 'empleador', 'admin', 'visita']) localStorage.setItem('guia.' + r + '.' + x, 'visto'); } catch (e) {} });
    const p = await c.newPage();
    await g.pasos(p);
    await pausa(p, 1000);
    const v = p.video(); await p.close(); await c.close();
    fs.renameSync(await v.path(), SALIDA + rol + '.webm');
    console.log(rol, 'grabado');
  }
  await b.close();
})();
