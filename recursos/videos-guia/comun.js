// piezas comunes de los videos y las guías en PDF: rutas de prueba (fuentes y librerías locales) y censura de datos
const F = '/tmp/bs/node_modules/';
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
    #demoSub{position:fixed;left:50%;bottom:28px;transform:translateX(-50%);max-width:880px;z-index:2147483646;background:rgba(52,72,62,.94);color:#fff;
      font:500 21px/1.4 Inter,system-ui,sans-serif;padding:12px 22px;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.25);text-align:center;transition:opacity .35s}
    #demoSub b{color:#d8cbb5;font-weight:700}
    #demoSub:empty{opacity:0}
    #demoCursor{position:fixed;left:640px;top:360px;width:26px;height:26px;z-index:2147483647;pointer-events:none;transition:left .7s cubic-bezier(.4,0,.2,1),top .7s cubic-bezier(.4,0,.2,1)}
    #demoCursor svg{filter:drop-shadow(0 2px 3px rgba(0,0,0,.35))}
    #demoCursor.clic::after{content:"";position:absolute;left:-14px;top:-14px;width:28px;height:28px;border-radius:50%;border:3px solid #d8cbb5;animation:onda .45s ease-out}
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

module.exports = { rutas, escenario };
