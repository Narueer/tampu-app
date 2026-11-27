# Texturas hechas con código (numpy): madera, yeso de adobe, piedra, tejas, tela, aguayo, pasto.
# Todas se repiten sin costuras (el ruido se genera en frecuencias, que es periódico) y cada una
# trae su relieve (normal map). No usan fotos de nadie.
import numpy as np, os, colorsys
from PIL import Image, ImageDraw

TAM = 512
CARPETA = os.path.join(os.path.dirname(os.path.abspath(__file__)), '_texturas')

def ruido(tam=TAM, frecuencia=8, semilla=0, aspecto=(1, 1)):
    """ruido suave periódico: tam x tam, valores 0..1. aspecto estira las manchas (para vetas)"""
    r = np.random.default_rng(semilla)
    f = np.fft.fftfreq(tam) * tam
    fx, fy = np.meshgrid(f * aspecto[0], f * aspecto[1])
    filtro = np.exp(-(fx ** 2 + fy ** 2) / (2 * frecuencia ** 2))
    campo = np.fft.ifft2(np.fft.fft2(r.normal(size=(tam, tam))) * filtro).real
    campo -= campo.min(); campo /= campo.max() + 1e-9
    return campo

def octavas(tam=TAM, base=4, n=5, semilla=0, aspecto=(1, 1)):
    t = np.zeros((tam, tam)); peso = 0
    for i in range(n):
        a = 0.55 ** i
        t += a * ruido(tam, base * 2 ** i, semilla + i, aspecto); peso += a
    return t / peso

def normal_desde_altura(h, fuerza=2.0):
    dx = (np.roll(h, -1, 1) - np.roll(h, 1, 1)) * fuerza * h.shape[0] / 64
    dy = (np.roll(h, -1, 0) - np.roll(h, 1, 0)) * fuerza * h.shape[0] / 64
    n = np.dstack([-dx, dy, np.ones_like(h)])
    n /= np.linalg.norm(n, axis=2, keepdims=True)
    return ((n * 0.5 + 0.5) * 255).astype(np.uint8)

def _gris(tipo):
    """devuelve (detalle 0..1 que multiplica al color, altura para el relieve, fuerza del relieve)"""
    T = TAM
    y, x = np.mgrid[0:T, 0:T] / T
    if tipo == 'madera':
        # vetas largas a lo largo de u (x), deformadas con ruido
        warp = octavas(T, 2, 3, 11, aspecto=(4, 1)) * 1.6 + 0.25 * np.sin(x * np.pi * 2 * 2)
        anillos = np.sin((y * 9 + warp) * np.pi * 2) * 0.5 + 0.5
        fibras = ruido(T, 160, 12, aspecto=(25, 1))
        d = 0.8 + 0.17 * anillos ** 2.5 + 0.1 * (fibras - 0.5) + 0.08 * (ruido(T, 3, 14) - 0.5)
        nudos = ruido(T, 3, 13) ; d -= 0.08 * np.clip(nudos - 0.75, 0, 1) * 4
        return d, anillos * 0.4 + fibras * 0.6, 0.6
    if tipo == 'yeso':
        manchas = octavas(T, 3, 5, 21)
        grano = ruido(T, 160, 22)
        d = 0.9 + 0.12 * (manchas - 0.5) + 0.06 * (grano - 0.5)
        return d, manchas * 0.6 + grano * 0.4, 1.4
    if tipo == 'pintura':
        brocha = ruido(T, 60, 31, aspecto=(12, 1))
        d = 0.94 + 0.08 * (brocha - 0.5) + 0.04 * (ruido(T, 120, 32) - 0.5)
        return d, brocha, 0.5
    if tipo == 'piedra':
        g = octavas(T, 6, 6, 41)
        motas = (ruido(T, 200, 42) > 0.62) * 0.12
        d = 0.8 + 0.3 * (g - 0.5) - motas + 0.08 * (ruido(T, 20, 43) - 0.5)
        return d, g, 2.4
    if tipo == 'teja':
        # tejas de arcilla en filas: cada columna es una canaleta curva y cada fila se monta sobre la anterior
        n_col, n_fil = 8, 6
        cx = (x * n_col) % 1; fila = np.floor(y * n_fil); fy = (y * n_fil) % 1
        cx = (cx + 0.5 * (fila % 2)) % 1
        curva = np.sin(cx * np.pi)
        borde = np.clip(fy * 5, 0, 1)
        altura = curva * 0.7 + borde * 0.3
        tono = ruido(T, 6, 51) * 0.25 + 0.85
        d = (0.62 + 0.38 * curva) * (0.6 + 0.4 * borde) * tono + 0.05 * (ruido(T, 150, 52) - 0.5)
        return d, altura, 3.0
    if tipo == 'tela':
        f = 90
        trama = (np.sin(x * f * np.pi * 2) * np.sin(y * f * np.pi * 2)) * 0.5 + 0.5
        d = 0.88 + 0.1 * trama + 0.08 * (octavas(T, 4, 3, 61) - 0.5)
        return d, trama, 0.8
    if tipo == 'pasto':
        g = octavas(T, 8, 5, 71)
        hojas = ruido(T, 220, 72, aspecto=(1, 3))
        d = 0.72 + 0.35 * g + 0.2 * (hojas - 0.5)
        return d, hojas, 1.5
    if tipo == 'arcilla':
        g = octavas(T, 5, 4, 81)
        d = 0.88 + 0.16 * (g - 0.5) + 0.05 * (ruido(T, 180, 82) - 0.5)
        return d, g, 1.0
    raise ValueError(tipo)

def aguayo(tam=TAM, semilla=5):
    """tejido andino: franjas de colores con rombos y zigzag, con la trama del tejido encima"""
    rng = np.random.default_rng(semilla)
    img = Image.new('RGB', (tam, tam)); dib = ImageDraw.Draw(img)
    colores = [(178, 34, 52), (214, 72, 38), (233, 160, 36), (32, 104, 74), (28, 66, 120), (120, 30, 90), (20, 20, 24), (240, 230, 210)]
    y = 0; i = 0
    while y < tam:
        alto = int(rng.choice([8, 10, 14, 36, 48]))
        c = colores[i % len(colores)] if alto < 30 else (178, 34, 52)
        dib.rectangle([0, y, tam, y + alto], fill=c)
        if alto >= 30:  # franja ancha con rombos (como en los aguayos)
            c2 = colores[(i + 3) % len(colores)]; c3 = colores[(i + 5) % len(colores)]
            paso = alto
            for k in range(-1, tam // paso + 2):
                cx = k * paso + paso // 2; cy = y + alto // 2
                r = alto // 2 - 3
                dib.polygon([(cx, cy - r), (cx + r, cy), (cx, cy + r), (cx - r, cy)], fill=c2)
                r2 = r // 2
                dib.polygon([(cx, cy - r2), (cx + r2, cy), (cx, cy + r2), (cx - r2, cy)], fill=c3)
        elif alto == 14:  # zigzag fino
            c2 = colores[(i + 2) % len(colores)]
            pts = [(k * 8, y + (2 if k % 2 else alto - 3)) for k in range(tam // 8 + 2)]
            dib.line(pts, fill=c2, width=2)
        y += alto; i += 1
    a = np.asarray(img).astype(float) / 255
    # se repite de arriba abajo: se dobla la imagen en espejo para que no haya costura
    a = np.concatenate([a[: tam // 2], a[: tam // 2][::-1]], 0)
    yy, xx = np.mgrid[0:tam, 0:tam] / tam
    trama = (np.sin(xx * 128 * np.pi * 2) * 0.5 + 0.5) * 0.5 + (np.sin(yy * 128 * np.pi * 2 + 1) * 0.5 + 0.5) * 0.5
    a *= (0.82 + 0.18 * trama)[..., None]
    return a, trama

def lineal(c):
    return np.array(c)

def generar(tipo, color):
    """crea (si no existe) la textura de color y su relieve. color en sRGB 0..1. Devuelve (ruta_color, ruta_normal)"""
    os.makedirs(CARPETA, exist_ok=True)
    clave = tipo + ('' if tipo == 'aguayo' else '_' + ''.join('%02x' % int(round(c * 255)) for c in color[:3]))
    rc = os.path.join(CARPETA, clave + '.jpg'); rn = os.path.join(CARPETA, tipo + '_n.jpg')
    if os.path.exists(rc) and os.path.exists(rn):
        return rc, rn
    if tipo == 'aguayo':
        a, h = aguayo(); fuerza = 0.7
    else:
        d, h, fuerza = _gris(tipo)
        a = np.clip(np.array(color[:3])[None, None, :] * d[..., None], 0, 1)
        if tipo == 'madera':  # la veta oscura tira a rojizo
            a[..., 0] *= 1.02; a[..., 2] *= 0.94
    Image.fromarray((np.clip(a, 0, 1) * 255).astype(np.uint8)).save(rc, quality=86)
    if not os.path.exists(rn):
        Image.fromarray(normal_desde_altura(h, fuerza)).save(rn, quality=92)
    return rc, rn

if __name__ == '__main__':
    import sys
    lam = []
    for t, c in [('madera', (0.36, 0.22, 0.13)), ('yeso', (0.93, 0.89, 0.82)), ('piedra', (0.55, 0.52, 0.47)), ('teja', (0.62, 0.24, 0.13)),
                 ('tela', (0.97, 0.94, 0.88)), ('aguayo', None), ('pasto', (0.42, 0.55, 0.30)), ('pintura', (0.12, 0.30, 0.48)), ('arcilla', (0.71, 0.33, 0.16))]:
        rc, rn = generar(t, c or (1, 1, 1)); lam.append(rc)
    ims = [Image.open(r).resize((256, 256)) for r in lam]
    g = Image.new('RGB', (256 * len(ims), 256)); [g.paste(im, (i * 256, 0)) for i, im in enumerate(ims)]
    g.save(sys.argv[-1] if sys.argv[-1].endswith('.jpg') else '/tmp/texturas.jpg')

def paisaje(ruta=None):
    """lo que se ve por la ventana: cielo de la tarde, Apus con nieve y techos de teja de Cusco"""
    ruta = ruta or os.path.join(CARPETA, 'paisaje.jpg')
    if os.path.exists(ruta): return ruta
    os.makedirs(CARPETA, exist_ok=True)
    W, H = 768, 512
    yy = np.linspace(0, 1, H)[:, None, None]
    cielo = (1 - yy) * np.array([0.42, 0.66, 0.92]) + yy * np.array([0.93, 0.86, 0.74])
    img = Image.fromarray((np.repeat(cielo, W, 1) * 255).astype(np.uint8)); d = ImageDraw.Draw(img)
    rng = np.random.default_rng(9)
    def cordillera(base, alto, color, nieve=None, semilla=0, n=7):
        r = np.random.default_rng(semilla)
        xs = np.linspace(-60, W + 60, n); ys = base - r.uniform(0.4, 1, n) * alto
        pts = [(0, H)]
        for i in range(len(xs) - 1):
            for t in np.linspace(0, 1, 12):
                x = xs[i] + (xs[i + 1] - xs[i]) * t
                y = ys[i] + (ys[i + 1] - ys[i]) * t + r.normal(0, alto * 0.03)
                pts.append((x, y))
        pts.append((W, H)); d.polygon(pts, fill=color)
        if nieve:
            for i in range(1, len(xs) - 1):
                if ys[i] < base - alto * 0.75:
                    x, y = xs[i], ys[i]
                    d.polygon([(x, y), (x + 34, y + 30), (x + 14, y + 24), (x, y + 36), (x - 16, y + 26), (x - 32, y + 30)], fill=nieve)
    cordillera(250, 170, (150, 160, 175), nieve=(245, 246, 250), semilla=3, n=6)
    cordillera(320, 120, (96, 120, 88), semilla=5, n=8)
    cordillera(390, 80, (78, 102, 66), semilla=7, n=10)
    # casitas de adobe con techo de teja
    for k in range(26):
        x = rng.uniform(0, W); y = rng.uniform(380, 470); w = rng.uniform(26, 48); h = w * 0.55
        d.rectangle([x, y, x + w, y + h], fill=tuple(int(c) for c in rng.choice([[230, 218, 196], [214, 196, 170], [238, 230, 214]])))
        d.polygon([(x - 4, y), (x + w / 2, y - h * 0.55), (x + w + 4, y)], fill=(170, 70, 40))
        d.rectangle([x + w * 0.35, y + h * 0.4, x + w * 0.55, y + h], fill=(90, 60, 40))
    d.rectangle([0, 470, W, H], fill=(70, 92, 58))
    img.save(ruta, quality=88)
    return ruta

def pantalla(ruta=None):
    """la laptop tiene TAMPU abierto: barra, mapa con puntos y tarjetas de cuartos"""
    ruta = ruta or os.path.join(CARPETA, 'pantalla.jpg')
    if os.path.exists(ruta): return ruta
    os.makedirs(CARPETA, exist_ok=True)
    W, H = 640, 420
    img = Image.new('RGB', (W, H), (248, 243, 235)); d = ImageDraw.Draw(img)
    d.rectangle([0, 0, W, 46], fill=(23, 53, 47))
    d.ellipse([16, 10, 42, 36], fill=(176, 80, 42)); d.rectangle([54, 18, 130, 28], fill=(240, 228, 210))
    for i in range(4): d.rectangle([330 + i * 70, 19, 380 + i * 70, 27], fill=(150, 180, 170))
    # mapa
    d.rectangle([20, 64, 400, 400], fill=(214, 226, 200))
    for k in range(9): d.line([(20, 64 + k * 42), (400, 100 + k * 30)], fill=(240, 238, 228), width=6)
    for k in range(6): d.line([(40 + k * 70, 64), (20 + k * 75, 400)], fill=(240, 238, 228), width=5)
    rng = np.random.default_rng(2)
    for k in range(9):
        x, y = rng.uniform(50, 370), rng.uniform(90, 380)
        d.ellipse([x - 10, y - 10, x + 10, y + 10], fill=(176, 80, 42)); d.ellipse([x - 4, y - 4, x + 4, y + 4], fill=(255, 255, 255))
    # tarjetas
    for k in range(3):
        y = 64 + k * 114
        d.rounded_rectangle([418, y, 620, y + 100], 8, fill=(255, 255, 255))
        d.rectangle([426, y + 8, 500, y + 92], fill=[(160, 120, 90), (120, 140, 150), (180, 150, 110)][k])
        d.rectangle([510, y + 14, 600, y + 24], fill=(40, 40, 40)); d.rectangle([510, y + 34, 580, y + 42], fill=(150, 150, 150))
        d.rounded_rectangle([510, y + 64, 570, y + 84], 5, fill=(176, 80, 42))
    img.save(ruta, quality=90)
    return ruta
