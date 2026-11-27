// graba la guía real de cada rol (Tampu explicando paso a paso) como video
const { chromium } = require('playwright');
const fs = require('fs');
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
const ROLES = {
  visitante: [null, '/'],
  estudiante: ['luis@tampu.pe:123456', '/estudiante'],
  arrendador: ['rosa@tampu.pe:123456', '/arrendador/habitaciones'],
  empleador: ['wayra@tampu.pe:123456', '/empleador/trabajos'],
  admin: ['admin@tampu.pe:admin123', '/admin'],
};
(async () => {
  const b = await chromium.launch({ args: ['--use-gl=angle', '--use-angle=swiftshader', '--enable-unsafe-swiftshader'] });
  for (const rol of (process.argv[2] || Object.keys(ROLES).join(',')).split(',')) {
    const [cuenta, url] = ROLES[rol];
    // sesión sin grabar, para iniciar sesión
    const base = await b.newContext({ viewport: { width: 1280, height: 720 } }); await rutas(base);
    if (cuenta) { const p = await base.newPage(); const [m, pw] = cuenta.split(':'); await p.goto('http://127.0.0.1:8099/login'); await p.fill('input[name=email]', m); await p.fill('input[name=password]', pw); await Promise.all([p.waitForNavigation(), p.click('form button[type=submit]')]); await p.close(); }
    const estado = await base.storageState(); await base.close();
    const c = await b.newContext({ viewport: { width: 1280, height: 720 }, storageState: estado, recordVideo: { dir: '/home/claude/vguia/crudo/' + rol, size: { width: 1280, height: 720 } } });
    await rutas(c);
    await c.addInitScript(() => { try { localStorage.setItem('guia.voz', '0'); } catch (e) {} });
    const p = await c.newPage();
    await p.goto('http://127.0.0.1:8099' + url, { waitUntil: 'networkidle' });
    await p.addStyleTag({ content: '.asistente-aviso{display:none!important} .guia-tarjeta{transform-origin:center}' });
    await p.waitForTimeout(1200);
    await p.evaluate(() => { if (!document.querySelector('.guia-tarjeta')) window.TampuGuia.abrir(); });
    await p.waitForTimeout(500);
    const n = await p.evaluate(() => document.querySelectorAll('.guia-puntos i').length);
    for (let i = 0; i < n; i++) {
      // tiempo de lectura: ~ 15 caracteres por segundo, mínimo 4 s
      const largo = await p.evaluate(() => (document.querySelector('.guia-titulo').textContent + document.querySelector('.guia-texto').textContent).length);
      await p.waitForTimeout(Math.max(4000, largo * 65));
      if (i < n - 1) await p.click('.guia-sig'); else await p.click('.guia-sig');
    }
    await p.waitForTimeout(1200);
    const v = p.video(); await p.close(); await c.close();
    fs.renameSync(await v.path(), '/home/claude/vguia/' + rol + '.webm');
    console.log(rol, n, 'pasos');
  }
  await b.close();
})();
