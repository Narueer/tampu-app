# Cuarto que "se arma" pieza por pieza (animación de Blender) para la portada:
# en la página, el scroll mueve la línea de tiempo de esta animación.
#   python cuarto_animado.py cuarto-animado.glb   (con el módulo bpy)   o   blender -b -P cuarto_animado.py -- cuarto-animado.glb
import sys, os, runpy, math
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import bpy, base
base.render = lambda *a, **k: base.uv_todo()
salida = sys.argv[-1] if sys.argv[-1].endswith('.glb') else 'cuarto-animado.glb'
sys.argv = ['cuarto.py', '/dev/null', '1']
runpy.run_path(os.path.join(os.path.dirname(os.path.abspath(__file__)), 'cuarto.py'), run_name='__main__')

FPS = 24
escena = bpy.context.scene
escena.render.fps = FPS

# grupos en el orden en que aparecen: (prefijos de nombre, cuadro de inicio, de dónde entran)
GRUPOS = [
    (('mochila', 'bolsillo'), -1, 'quieto'),   # la mochila ya está desde el inicio: "llegas con una maleta"
    (('piso', 'tabla'), 4, 'abajo'),
    (('pared_fondo', 'pared_izq', 'zocalo'), 14, 'arriba'),
    (('ven_', 'cerro', 'alfeizar', 'cuadro', 'chak'), 30, 'frente'),
    (('cama', 'cabecera', 'colchon', 'almohada'), 46, 'arriba'),
    (('fraz',), 58, 'lado'),
    (('mesa', 'pata', 'silla', 'spata'), 72, 'arriba'),
    (('laptop', 'pantalla_luz', 'libro', 'lampara', 'Cone', 'taza'), 86, 'arriba'),
    (('alfombra', 'maceta', 'Sphere'), 100, 'arriba'),
]
FIN = 130
DUR = 16  # cuadros que tarda cada pieza en caer a su sitio

def grupo_de(nombre):
    for i, (pref, _, _) in enumerate(GRUPOS):
        if any(nombre.startswith(p) for p in pref):
            return i
    return None

objetos = [o for o in bpy.data.objects if o.type == 'MESH' and o.name != 'suelo']
sin_grupo = [o.name for o in objetos if grupo_de(o.name) is None]
if sin_grupo:
    print('Sin grupo (aparecen al inicio):', sin_grupo)

orden = {}
for o in sorted(objetos, key=lambda o: o.name):
    g = grupo_de(o.name)
    g = 0 if g is None else g
    k = orden.get(g, 0); orden[g] = k + 1
    _, inicio, desde = GRUPOS[g]
    if desde == 'quieto':
        o.keyframe_insert('location', frame=0); o.keyframe_insert('scale', frame=0)
        o.keyframe_insert('location', frame=FIN); o.keyframe_insert('scale', frame=FIN)
        continue
    t0 = inicio + min(k, 8) * 1.5        # dentro del grupo caen una tras otra
    loc = o.location.copy(); esc = o.scale.copy()
    off = {'abajo': (0, 0, -1.2), 'arriba': (0, 0, 2.2), 'frente': (0, -1.6, 0.4), 'lado': (1.8, 0, 0.6)}[desde]
    # antes de aparecer: invisible (escala 0) y fuera de su sitio
    o.scale = (0, 0, 0); o.location = loc
    o.keyframe_insert('scale', frame=0); o.keyframe_insert('location', frame=0)
    o.keyframe_insert('scale', frame=t0)
    o.location = (loc.x + off[0], loc.y + off[1], loc.z + off[2]); o.keyframe_insert('location', frame=t0)
    o.scale = esc * 0.9; o.keyframe_insert('scale', frame=t0 + 2)
    # cae y rebota un poco
    o.location = loc; o.keyframe_insert('location', frame=t0 + DUR * 0.7)
    o.scale = (esc.x * 1.04, esc.y * 1.04, esc.z * 0.94); o.keyframe_insert('scale', frame=t0 + DUR * 0.7)
    o.location = (loc.x, loc.y, loc.z + 0.04); o.keyframe_insert('location', frame=t0 + DUR * 0.85)
    o.location = loc; o.keyframe_insert('location', frame=t0 + DUR)
    o.scale = esc; o.keyframe_insert('scale', frame=t0 + DUR)
    o.keyframe_insert('location', frame=FIN); o.keyframe_insert('scale', frame=FIN)
    if o.animation_data and o.animation_data.action:
        for fc in getattr(o.animation_data.action, 'fcurves', []):
            for kp in fc.keyframe_points:
                kp.interpolation = 'BEZIER'; kp.easing = 'EASE_OUT'

# la laptop se abre al final
pan = [o for o in objetos if o.name in ('laptop_pantalla', 'pantalla_luz')]  # (giran sobre su centro)
for o in pan:
    r = o.rotation_euler.copy()
    o.rotation_euler = (math.radians(80), r.y, r.z); o.keyframe_insert('rotation_euler', frame=0); o.keyframe_insert('rotation_euler', frame=108)
    o.rotation_euler = r; o.keyframe_insert('rotation_euler', frame=124); o.keyframe_insert('rotation_euler', frame=FIN)

escena.frame_start, escena.frame_end = 0, FIN
bpy.ops.object.select_all(action='DESELECT')
for o in objetos:
    o.select_set(True)
bpy.ops.export_scene.gltf(filepath=salida, export_format='GLB', use_selection=True, export_apply=True,
                          export_draco_mesh_compression_enable=True, export_draco_mesh_compression_level=7,
                          export_yup=True, export_cameras=False, export_lights=False,
                          export_animations=True, export_animation_mode='SCENE', export_frame_range=True,
                          export_force_sampling=True, export_optimize_animation_size=True,
                          export_image_format='JPEG', export_image_quality=82)
print('GLB animado listo', salida, 'duración', FIN / FPS, 's')

# el exportador deja una animación por pieza: se unen en una sola ("armar") para mover todo con el scroll
import json, struct
def unir_animaciones(ruta, nombre='armar'):
    b = open(ruta, 'rb').read()
    n = struct.unpack('<I', b[12:16])[0]
    j = json.loads(b[20:20 + n]); resto = b[20 + n:]
    anims = j.get('animations', [])
    if len(anims) <= 1:
        return
    canales, muestras = [], []
    for a in anims:
        base_i = len(muestras)
        muestras += a['samplers']
        for c in a['channels']:
            c = dict(c); c['sampler'] += base_i; canales.append(c)
    j['animations'] = [{'name': nombre, 'channels': canales, 'samplers': muestras}]
    js = json.dumps(j, separators=(',', ':')).encode()
    js += b' ' * ((4 - len(js) % 4) % 4)
    total = 12 + 8 + len(js) + len(resto)
    open(ruta, 'wb').write(b[:4] + struct.pack('<I', 2) + struct.pack('<I', total) + struct.pack('<I', len(js)) + b'JSON' + js + resto)
    print('animaciones unidas:', len(anims), '→ 1')
unir_animaciones(salida)
