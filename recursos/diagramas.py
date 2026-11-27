# genera los diagramas de arquitectura y de base de datos de TAMPU como SVG
import html
FONT = "font-family='Inter, Segoe UI, Arial, sans-serif'"
SERIF = "font-family='Fraunces, Georgia, serif'"
C = dict(tinta='#17352f', terra='#b0502a', ocre='#c8973f', papel='#fffdf9', adobe='#efe6d8', borde='#ddd3c4', suave='#6b6358', azul='#2f6b8a', verde='#2e7d5b')

def caja(x, y, w, h, titulo, lineas=(), color=C['tinta'], fondo=C['papel'], tam=15, icono=''):
    s = f"<g><rect x='{x}' y='{y}' width='{w}' height='{h}' rx='10' fill='{fondo}' stroke='{color}' stroke-width='1.6'/>"
    s += f"<rect x='{x}' y='{y}' width='{w}' height='6' rx='3' fill='{color}'/>"
    s += f"<text x='{x+16}' y='{y+30}' {SERIF} font-size='{tam}' font-weight='700' fill='{C['tinta']}'>{html.escape(icono + titulo)}</text>"
    for i, l in enumerate(lineas):
        s += f"<text x='{x+16}' y='{y+52+i*19}' {FONT} font-size='12.5' fill='{C['suave']}'>{html.escape(l)}</text>"
    return s + "</g>"

def flecha(x1, y1, x2, y2, texto='', color=C['suave'], doble=False, dx=0, dy=-6):
    s = f"<line x1='{x1}' y1='{y1}' x2='{x2}' y2='{y2}' stroke='{color}' stroke-width='1.6' marker-end='url(#punta)' {'marker-start=\"url(#punta)\"' if doble else ''}/>"
    if texto:
        mx, my = (x1 + x2) / 2 + dx, (y1 + y2) / 2 + dy
        s += f"<text x='{mx}' y='{my}' {FONT} font-size='11.5' fill='{C['suave']}' text-anchor='middle'>{html.escape(texto)}</text>"
    return s

def svg(w, h, cuerpo, titulo):
    return f"""<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 {w} {h}' width='{w}' height='{h}' role='img' aria-label='{html.escape(titulo)}'>
<defs><marker id='punta' viewBox='0 0 10 10' refX='9' refY='5' markerWidth='7' markerHeight='7' orient='auto-start-reverse'><path d='M0 0L10 5L0 10z' fill='{C['suave']}'/></marker></defs>
<rect width='{w}' height='{h}' fill='{C['adobe']}'/>
<text x='32' y='44' {SERIF} font-size='24' font-weight='700' fill='{C['tinta']}'>{html.escape(titulo)}</text>
{cuerpo}</svg>"""

# ---------- arquitectura ----------
b = ""
b += caja(32, 80, 230, 120, "Usuarios", ["Estudiantes · arrendadores", "Empresas · administrador", "Navegador o app instalada (PWA)"], C['terra'])
# servidor
b += f"<rect x='310' y='70' width='520' height='470' rx='14' fill='none' stroke='{C['tinta']}' stroke-dasharray='6 5' stroke-width='1.4'/>"
b += f"<text x='326' y='94' {FONT} font-size='12' font-weight='700' fill='{C['tinta']}' letter-spacing='1.5'>RENDER · CONTENEDOR DOCKER · PHP 8.2 + LARAVEL 12</text>"
b += caja(330, 110, 480, 70, "Rutas y middleware", ["auth · rol · correo verificado · 2 pasos · límite de intentos · bitácora"], C['tinta'])
b += caja(330, 200, 230, 110, "Controladores", ["Habitaciones · Trabajos", "Paneles por rol · Admin", "Chat · Asistente · Calculadora"], C['tinta'])
b += caja(580, 200, 230, 110, "Servicios", ["AsistenteIA (reglas + IA)", "Lugares (universidades)", "Entorno (buses, seguridad)"], C['tinta'])
b += caja(330, 330, 230, 90, "Modelos (Eloquent)", ["Usuario · Habitacion · Trabajo", "Solicitud · Postulacion · …"], C['tinta'])
b += caja(580, 330, 230, 90, "Vistas (Blade)", ["HTML + CSS propio + JS", "Leaflet · model-viewer"], C['tinta'])
b += caja(330, 440, 480, 80, "Avisos", ["Campanita (tabla notifications) y correo con plantilla TAMPU"], C['tinta'])
b += flecha(262, 140, 328, 140, "HTTPS", dy=-8)
# externos
b += caja(880, 80, 270, 100, "Supabase · PostgreSQL", ["16 tablas · índices de búsqueda", "Local: MariaDB (XAMPP)"], C['azul'])
b += caja(880, 200, 270, 100, "IA: Groq (Llama 3.3)", ["Respaldo: Google Gemini", "Máx. 2,5 s; si no, responden reglas"], C['verde'])
b += caja(880, 320, 270, 100, "OpenStreetMap", ["Mapas (Leaflet) y ubicaciones", "37 líneas de bus, paraderos, salud"], C['ocre'])
b += caja(880, 440, 270, 80, "Correo (SMTP)", ["Avisos de visitas y postulaciones"], C['suave'])
for y, t in [(130, "SQL"), (250, "API"), (370, "mapas"), (480, "SMTP")]:
    b += flecha(832, y, 878, y)
    b += f"<text x='855' y='{y-8}' {FONT} font-size='11' fill='{C['suave']}' text-anchor='middle'>{t}</text>"
b += caja(32, 240, 230, 150, "Herramientas", ["GitHub (código)", "Blender (maquetas 3D)", "Playwright + ffmpeg (videos)", "cron-job.org (mantiene activo)", "Search Console (Google)"], C['suave'], fondo='#f7f2ea')
b += caja(32, 410, 230, 110, "Seguridad", ["Contraseñas con bcrypt", "Verificación de DNI/RUC", "2 pasos (TOTP) · CSRF", "Bitácora y bloqueos"], C['terra'], fondo='#f7f2ea')
open('arquitectura.svg', 'w').write(svg(1182, 560, b, "Arquitectura de TAMPU"))

# ---------- base de datos ----------
A = 270
def h(n): return 44 + 19 * n
T = {}
def tabla(k, x, y, lineas, color, ancho=A):
    T[k] = (x, y, ancho, h(len(lineas)), lineas, color)
tabla('reportes', 30, 80, ["usuario_id, habitacion_id", "o trabajo_id, motivo, estado"], '#b23b4b')
tabla('usuarios', 456, 80, ["id, nombre, email, dni, celular", "rol: estudiante, arrendador,", "empleador o admin", "verificacion, suspendido", "avisos_correo, dos_pasos_*"], C['terra'])
tabla('horarios', 882, 80, ["usuario_id, dia, turno", "(clases del estudiante)"], C['azul'])
tabla('eventos_seguridad', 30, 200, ["usuario_id, tipo, ip, detalle"], '#b23b4b')
tabla('habitaciones', 30, 360, ["id, usuario_id", "titulo, tipo, precio, distrito", "lat, lng, cerca_de, servicios", "estado, metros, baño, garantía"], C['tinta'])
tabla('conversaciones', 456, 360, ["usuario_a_id, usuario_b_id", "habitacion_id o trabajo_id"], C['ocre'])
tabla('trabajos', 882, 360, ["id, usuario_id", "titulo, empresa, modalidad, pago", "lat, lng, vacantes, estado"], C['azul'])
tabla('mensajes', 456, 560, ["conversacion_id, usuario_id", "texto, leido"], C['ocre'])
y = 580
for k, l, col in [('habitacion_fotos', ["habitacion_id, ruta, orden"], C['tinta']), ('favoritos', ["usuario_id, habitacion_id"], C['tinta']),
                  ('solicitudes', ["habitacion_id, usuario_id", "fecha_visita, estado", "confirmacion (visitado y confirmado)"], C['verde']),
                  ('resenas', ["habitacion_id, usuario_id", "estrellas (1-5), comentario"], C['verde'])]:
    tabla(k, 90, y, l, col, 300); y += h(len(l)) + 18
y = 580
for k, l in [('trabajo_horarios', ["trabajo_id, dia, turno"]), ('postulaciones', ["trabajo_id, usuario_id", "mensaje, estado"])]:
    tabla(k, 822, y, l, C['azul'], 300); y += h(len(l)) + 18

b = ""
def uno(x, y): return f"<circle cx='{x}' cy='{y}' r='4' fill='{C['tinta']}'/>"
def muchos(x, y, dir):   # pata de gallo hacia la tabla hija
    if dir == 'abajo': return f"<path d='M{x-7} {y-9}L{x} {y}L{x+7} {y-9}M{x} {y-9}V{y}' fill='none' stroke='{C['terra']}' stroke-width='1.8'/>"
    if dir == 'der': return f"<path d='M{x-9} {y-7}L{x} {y}L{x-9} {y+7}M{x-9} {y}H{x}' fill='none' stroke='{C['terra']}' stroke-width='1.8'/>"
    return f"<path d='M{x+9} {y-7}L{x} {y}L{x+9} {y+7}M{x+9} {y}H{x}' fill='none' stroke='{C['terra']}' stroke-width='1.8'/>"
def linea(pts):
    return "<polyline points='" + " ".join(f"{x},{y}" for x, y in pts) + f"' fill='none' stroke='{C['suave']}' stroke-width='1.5'/>"
def base(k): x, y, w, hh, *_ = T[k]; return x, y, w, hh
# usuarios -> fila de abajo (líneas rectas que no cruzan cajas)
ux, uy, uw, uh = base('usuarios')
for k in ['habitaciones', 'conversaciones', 'trabajos']:
    x, y, w, hh = base(k); p1 = (ux + uw * {'habitaciones': .2, 'conversaciones': .5, 'trabajos': .8}[k], uy + uh); p2 = (x + w / 2, y)
    b += linea([p1, (p1[0], 320), (p2[0], 320), p2]) + uno(*p1) + muchos(*p2, 'abajo')
for k, lado in [('reportes', 'izq'), ('horarios', 'der'), ('eventos_seguridad', 'izq')]:
    x, y, w, hh = base(k)
    if lado == 'izq':
        yy = y + hh / 2 if k == 'reportes' else min(uy + uh - 14, y + hh / 2)
        p1 = (ux, yy if k == 'reportes' else uy + uh - 18); p2 = (x + w, y + hh / 2)
        b += linea([p1, ((p1[0] + p2[0]) / 2, p1[1]), ((p1[0] + p2[0]) / 2, p2[1]), p2]) + uno(*p1) + muchos(*p2, 'izq')
    else:
        p1 = (ux + uw, y + hh / 2); p2 = (x, y + hh / 2)
        b += linea([p1, p2]) + uno(*p1) + muchos(*p2, 'der')
# conversaciones -> mensajes
x, y, w, hh = base('conversaciones'); mx, my, mw, mh = base('mensajes')
b += linea([(x + w / 2, y + hh), (mx + mw / 2, my)]) + uno(x + w / 2, y + hh) + muchos(mx + mw / 2, my, 'abajo')
# columna vertical ("espina") para los hijos de habitaciones y de trabajos
hx, hy, hw, hh2 = base('habitaciones')
hijos = ['habitacion_fotos', 'favoritos', 'solicitudes', 'resenas']
espina = 58; ult = base(hijos[-1])
b += linea([(espina, hy + hh2), (espina, ult[1] + ult[3] / 2)]) + uno(espina, hy + hh2)
for k in hijos:
    x, y, w, hh = base(k); b += linea([(espina, y + hh / 2), (x, y + hh / 2)]) + muchos(x, y + hh / 2, 'der')
tx, ty, tw, th = base('trabajos')
hijos = ['trabajo_horarios', 'postulaciones']; espina = 1150; ult = base(hijos[-1])
b += linea([(espina, ty + th / 2), (tx + tw, ty + th / 2)]) + linea([(espina, ty + th / 2), (espina, ult[1] + ult[3] / 2)]) + uno(tx + tw, ty + th / 2)
for k in hijos:
    x, y, w, hh = base(k); b += linea([(espina, y + hh / 2), (x + w, y + hh / 2)]) + muchos(x + w, y + hh / 2, 'izq')
for k, (x, y, w, hh, l, col) in T.items():
    b += caja(x, y, w, hh, k, l, col, tam=15)
b += f"<g {FONT} font-size='12' fill='{C['suave']}'><circle cx='40' cy='{1000-6}' r='4' fill='{C['tinta']}'/><text x='52' y='1000'>uno</text>"
b += f"<path d='M110 {1000-13}L117 {1000-4}L124 {1000-13}M117 {1000-13}V{1000-4}' fill='none' stroke='{C['terra']}' stroke-width='1.8'/><text x='132' y='1000'>muchos  ·  Además: contactos (formulario de contacto) y notifications (avisos de la campanita).</text></g>"
open('base-de-datos.svg', 'w').write(svg(1182, 1030, b, "Base de datos de TAMPU (PostgreSQL)"))
print('ok')
