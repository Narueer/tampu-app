const { chromium } = require('playwright');
const T = '/home/claude/z/Tampu/public/';
const F = '/tmp/bs/node_modules/';
const css = `@font-face{font-family:Fraunces;src:url(file://${F}@fontsource-variable/fraunces/files/fraunces-latin-full-normal.woff2)}
@font-face{font-family:Inter;src:url(file://${F}@fontsource/inter/files/inter-latin-500-normal.woff2)}
body{margin:0;width:1280px;height:720px;background:#34483e;font-family:Inter;color:#fff;display:flex;align-items:center;overflow:hidden;position:relative}
.tejido{position:absolute;left:0;right:0;bottom:0;height:16px;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='48' height='12' viewBox='0 0 48 12'%3E%3Crect width='48' height='12' fill='%2317352f'/%3E%3Cpath d='M0 6 6 0h6l6 6-6 6H6z' fill='%23b0502a'/%3E%3Cpath d='M6 6 9 3l3 3-3 3z' fill='%23ecd3a4'/%3E%3Cpath d='M24 6l6-6h6l6 6-6 6h-6z' fill='%23c8973f'/%3E%3Cpath d='M30 6l3-3 3 3-3 3z' fill='%2317352f'/%3E%3C/svg%3E") repeat-x;background-size:auto 16px}
.txt{padding-left:110px;width:620px}
.sobre{font-size:16px;letter-spacing:.2em;text-transform:uppercase;color:#d8cbb5}
h1{font-family:Fraunces;font-weight:600;font-size:64px;line-height:1.05;margin:14px 0 18px}
h1 em{font-style:normal;color:#d8cbb5}
p{font-size:21px;line-height:1.5;color:#dfe4dc;margin:0}
.logo{display:flex;align-items:center;gap:12px;margin-bottom:34px;font-family:Fraunces;font-size:28px}
.logo img{width:52px}
.ilu{position:absolute;right:70px;top:50%;transform:translateY(-50%);width:470px;filter:drop-shadow(0 30px 30px rgba(0,0,0,.4))}
.tampu{position:absolute;right:90px;bottom:60px;width:120px}`;
const ROLES = {
  visitante: ['Primeros pasos', 'Conoce <em>TAMPU</em>', 'Cómo buscar cuarto y trabajo en Cusco, en un minuto.', 'casa'],
  estudiante: ['Guía para estudiantes', 'Tu <em>panel</em>', 'Visitas, horario de clases y postulaciones.', 'cuarto'],
  arrendador: ['Guía para arrendadores', 'Publica tu <em>cuarto</em>', 'Cómo publicar y responder a las visitas.', 'casa'],
  empleador: ['Guía para empresas', 'Publica un <em>trabajo</em>', 'Ofertas que encajan con el horario de los estudiantes.', 'paso-horario'],
  admin: ['Guía para el administrador', 'Cuida <em>TAMPU</em>', 'Verificaciones, reportes, gráficos y seguridad.', 'casa'],
};
const fs = require('fs');
async function abrir(p, html) { fs.writeFileSync('/home/claude/vguia/t.html', html); await p.goto('file:///home/claude/vguia/t.html'); }
(async () => {
  const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: 1280, height: 720 } });
  for (const [rol, [sobre, titulo, texto, ilu]] of Object.entries(ROLES)) {
    await abrir(p, `<style>${css}</style><div class="txt"><div class="logo"><img src="file://${T}img/logo-160.png">Tampu</div><div class="sobre">${sobre}</div><h1>${titulo}</h1><p>${texto}</p></div><img class="ilu" src="file://${T}img/3d/${ilu}.webp"><div class="tejido"></div>`);
    await p.waitForTimeout(300); await p.screenshot({ path: `intro_${rol}.png` });
  }
  await abrir(p, `<style>${css} .txt{width:900px}</style><div class="txt"><div class="logo"><img src="file://${T}img/logo-160.png">Tampu</div><h1>¿Dudas? <em>Pregúntame.</em></h1><p>Toca a Tampu en la esquina de la pantalla o el botón de Guía, cuando quieras.</p></div><img class="tampu" src="file://${T}img/asistente/tampu-bot.webp"><div class="tejido"></div>`);
  await p.waitForTimeout(300); await p.screenshot({ path: 'final.png' });
  await b.close();
})();
