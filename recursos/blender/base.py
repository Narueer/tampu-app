# utilidades para las ilustraciones 3D de TAMPU (Blender por bpy)
import bpy, math, random, os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from mathutils import Vector

PAL = {
    'adobe': (0.93, 0.89, 0.82), 'adobe2': (0.88, 0.82, 0.72), 'teja': (0.62, 0.24, 0.13), 'teja2': (0.50, 0.18, 0.10),
    'madera': (0.36, 0.22, 0.13), 'azul': (0.12, 0.30, 0.48), 'verde': (0.09, 0.21, 0.18), 'verde2': (0.30, 0.45, 0.28),
    'piedra': (0.55, 0.52, 0.47), 'piedra2': (0.45, 0.42, 0.38), 'ocre': (0.80, 0.56, 0.20), 'crema': (0.97, 0.94, 0.88),
    'terracota': (0.71, 0.33, 0.16), 'tinta': (0.10, 0.13, 0.12), 'blanco': (0.96, 0.95, 0.93), 'vidrio': (0.55, 0.70, 0.78),
    'pasto': (0.42, 0.55, 0.30), 'cerro': (0.48, 0.52, 0.38), 'cielo': (0.85, 0.90, 0.95), 'rosa': (0.78, 0.45, 0.42),
}

def limpiar():
    bpy.ops.wm.read_factory_settings(use_empty=True)

# qué textura lleva cada color de la paleta (ver texturas.py); se puede forzar con tex=
TEXTURA = {'madera': 'madera', 'adobe': 'yeso', 'adobe2': 'yeso', 'piedra': 'piedra', 'piedra2': 'piedra',
           'teja': 'teja', 'teja2': 'teja', 'pasto': 'pasto', 'cerro': 'pasto', 'azul': 'pintura', 'crema': 'tela',
           'terracota': 'arcilla'}
# cuántas veces se repite la textura por metro
ESCALA_TEX = {'madera': 2.4, 'yeso': 0.7, 'piedra': 1.5, 'teja': 1.4, 'tela': 1.5, 'aguayo': 1.6, 'pasto': 0.8, 'pintura': 1.0, 'arcilla': 1.5}
_texturizados = {}

def tipo_textura(color):
    for k, v in PAL.items():
        if tuple(color[:3]) == tuple(v):
            return TEXTURA.get(k)
    return None

def _imagen(ruta, datos=False):
    im = bpy.data.images.get(os.path.basename(ruta)) or bpy.data.images.load(ruta)
    if datos: im.colorspace_settings.name = 'Non-Color'
    return im

def mat(nombre, color, rugosidad=0.75, metal=0.0, emision=None, fuerza=3, tex='auto'):
    m = bpy.data.materials.get(nombre)
    if m: return m
    m = bpy.data.materials.new(nombre); m.use_nodes = True
    b = m.node_tree.nodes['Principled BSDF']
    b.inputs['Base Color'].default_value = (*[c ** 2.2 for c in color], 1)   # colores en sRGB -> lineal
    tipo = tipo_textura(color) if tex == 'auto' else tex
    if tipo and not emision:
        import texturas
        rc, rn = texturas.generar(tipo, color)
        nt = m.node_tree
        ic = nt.nodes.new('ShaderNodeTexImage'); ic.image = _imagen(rc)
        nt.links.new(ic.outputs['Color'], b.inputs['Base Color'])
        inn = nt.nodes.new('ShaderNodeTexImage'); inn.image = _imagen(rn, True)
        nm = nt.nodes.new('ShaderNodeNormalMap'); nm.inputs['Strength'].default_value = 1.0
        nt.links.new(inn.outputs['Color'], nm.inputs['Color']); nt.links.new(nm.outputs['Normal'], b.inputs['Normal'])
        _texturizados[m.name] = ESCALA_TEX.get(tipo, 1.0)
    b.inputs['Roughness'].default_value = rugosidad
    b.inputs['Metallic'].default_value = metal
    if emision:
        b.inputs['Emission Color'].default_value = (*emision, 1); b.inputs['Emission Strength'].default_value = fuerza
    return m

def uv_caja(o, escala):
    # proyección de caja en coordenadas del mundo: cada cara toma los dos ejes que no mira,
    # y la "u" va por el lado más largo de la pieza (así la veta de la madera sigue a la tabla)
    import bmesh
    me = o.data; bm = bmesh.new(); bm.from_mesh(me)
    uv = bm.loops.layers.uv.verify()
    mw = o.matrix_world; dims = o.dimensions
    for f in bm.faces:
        n = (mw.to_3x3() @ f.normal); eje = max(range(3), key=lambda i: abs(n[i]))
        otros = [i for i in range(3) if i != eje]
        if dims[otros[1]] > dims[otros[0]]: otros = otros[::-1]
        for l in f.loops:
            p = mw @ l.vert.co
            l[uv].uv = (p[otros[0]] * escala, p[otros[1]] * escala)
    bm.to_mesh(me); bm.free()

def uv_todo():
    for o in bpy.data.objects:
        if o.type != 'MESH' or not o.data.materials: continue
        esc = [_texturizados.get(m.name) for m in o.data.materials if m]
        esc = [e for e in esc if e]
        if esc and esc[0] != 'plano': uv_caja(o, esc[0])

def caja(nombre, loc, dim, color, bisel=0.02, rot=(0, 0, 0), tex='auto'):
    bpy.ops.mesh.primitive_cube_add(location=loc, rotation=rot)
    o = bpy.context.object; o.name = nombre
    o.scale = (dim[0] / 2, dim[1] / 2, dim[2] / 2)
    bpy.ops.object.transform_apply(scale=True)
    if bisel:
        md = o.modifiers.new('bisel', 'BEVEL'); md.width = bisel; md.segments = 3
    o.data.materials.append(color if isinstance(color, bpy.types.Material) else mat(nombre + '_m', color, tex=tex))
    bpy.ops.object.shade_smooth()
    return o

def cil(nombre, loc, r, h, color, verts=32, rot=(0, 0, 0)):
    bpy.ops.mesh.primitive_cylinder_add(vertices=verts, radius=r, depth=h, location=loc, rotation=rot)
    o = bpy.context.object; o.name = nombre
    o.data.materials.append(color if isinstance(color, bpy.types.Material) else mat(nombre + '_m', color))
    bpy.ops.object.shade_smooth()
    return o

def tejado(nombre, centro, ancho, fondo, alto, color, vuelo=0.12):
    # tejado a dos aguas con tejas (ondas) hecho con un prisma
    import bmesh
    me = bpy.data.meshes.new(nombre); o = bpy.data.objects.new(nombre, me); bpy.context.collection.objects.link(o)
    bm = bmesh.new()
    a, f = ancho / 2 + vuelo, fondo / 2 + vuelo
    v = [bm.verts.new(p) for p in [(-a, -f, 0), (a, -f, 0), (a, f, 0), (-a, f, 0), (-a, 0, alto), (a, 0, alto)]]
    for cara in [(0, 1, 5, 4), (2, 3, 4, 5), (0, 4, 3), (1, 2, 5), (0, 3, 2, 1)]:
        bm.faces.new([v[i] for i in cara])
    bm.to_mesh(me); bm.free()
    o.location = centro
    sol = o.modifiers.new('grosor', 'SOLIDIFY'); sol.thickness = 0.05
    bev = o.modifiers.new('bisel', 'BEVEL'); bev.width = 0.015; bev.segments = 2
    o.data.materials.append(color if isinstance(color, bpy.types.Material) else mat(nombre + '_m', color))
    return o

def muro_inca(nombre, x0, y, z0, largo, alto, prof, color, filas=3, semilla=3):
    # muro de piedras de distinto tamaño (como los muros incas), una fila sobre otra
    random.seed(semilla)
    m = color if isinstance(color, bpy.types.Material) else mat(nombre + '_m', color)
    m2 = mat(nombre + '_m2', PAL['piedra2'])
    z = z0; h = alto / filas; n = 0
    for fila in range(filas):
        x = x0
        while x < x0 + largo - 0.01:
            w = min(random.uniform(0.25, 0.55), x0 + largo - x)
            hh = h * random.uniform(0.92, 1.0)
            o = caja(f'{nombre}{n}', (x + w / 2, y, z + hh / 2), (w - 0.02, prof * random.uniform(0.92, 1), hh - 0.02), m if random.random() > 0.35 else m2, bisel=0.05)
            x += w; n += 1
        z += h

def camara_iso(objetivo=(0, 0, 0.6), dist=9, alt_grados=30, az_grados=45, escala=5.5, ortho=True):
    cam = bpy.data.cameras.new('cam'); o = bpy.data.objects.new('cam', cam); bpy.context.collection.objects.link(o)
    a, e = math.radians(az_grados), math.radians(alt_grados)
    o.location = (objetivo[0] + dist * math.cos(e) * math.sin(a), objetivo[1] - dist * math.cos(e) * math.cos(a), objetivo[2] + dist * math.sin(e))
    d = Vector(objetivo) - o.location; o.rotation_euler = d.to_track_quat('-Z', 'Y').to_euler()
    if ortho: cam.type = 'ORTHO'; cam.ortho_scale = escala
    else: cam.lens = 50
    bpy.context.scene.camera = o
    return o

def luces(sol_energia=3.2, cielo=0.9, angulo=(50, 0, 35)):
    s = bpy.data.lights.new('sol', 'SUN'); s.energy = sol_energia; s.angle = math.radians(6); s.color = (1, 0.95, 0.86)
    o = bpy.data.objects.new('sol', s); bpy.context.collection.objects.link(o)
    o.rotation_euler = [math.radians(x) for x in angulo]
    w = bpy.data.worlds.new('mundo'); bpy.context.scene.world = w; w.use_nodes = True
    bg = w.node_tree.nodes['Background']; bg.inputs['Color'].default_value = (0.92, 0.94, 0.97, 1); bg.inputs['Strength'].default_value = cielo

def suelo_sombra(tam=30, z=0):
    # suelo invisible que solo recibe sombra (para fondo transparente)
    bpy.ops.mesh.primitive_plane_add(size=tam, location=(0, 0, z)); o = bpy.context.object; o.name = 'suelo'
    o.is_shadow_catcher = True
    return o

def render(salida, ancho=1200, alto=900, muestras=96):
    uv_todo()
    sc = bpy.context.scene
    sc.render.engine = 'CYCLES'; sc.cycles.device = 'CPU'; sc.cycles.samples = muestras; sc.cycles.use_denoising = True
    sc.render.resolution_x = ancho; sc.render.resolution_y = alto; sc.render.film_transparent = True
    sc.render.image_settings.file_format = 'PNG'; sc.render.image_settings.color_mode = 'RGBA'
    sc.view_settings.view_transform = 'AgX'; sc.view_settings.look = 'AgX - Medium High Contrast'
    sc.render.filepath = salida
    bpy.ops.render.render(write_still=True)

def mat_imagen(nombre, ruta, emision=0.0, rugosidad=0.5):
    """material con una imagen (paisaje de la ventana, pantalla); si emision > 0 brilla"""
    m = bpy.data.materials.get(nombre)
    if m: return m
    m = bpy.data.materials.new(nombre); m.use_nodes = True; nt = m.node_tree
    b = nt.nodes['Principled BSDF']; b.inputs['Roughness'].default_value = rugosidad
    im = nt.nodes.new('ShaderNodeTexImage'); im.image = _imagen(ruta)
    nt.links.new(im.outputs['Color'], b.inputs['Base Color'])
    if emision:
        nt.links.new(im.outputs['Color'], b.inputs['Emission Color']); b.inputs['Emission Strength'].default_value = emision
    _texturizados[m.name] = 'plano'
    return m

def plano_imagen(nombre, centro, ancho, alto, material, normal_y=-1):
    """rectángulo vertical (mirando hacia -y) con la imagen ocupando toda la cara"""
    import bmesh
    me = bpy.data.meshes.new(nombre); o = bpy.data.objects.new(nombre, me); bpy.context.collection.objects.link(o)
    bm = bmesh.new(); a, h = ancho / 2, alto / 2
    vs = [bm.verts.new(p) for p in [(-a, 0, -h), (a, 0, -h), (a, 0, h), (-a, 0, h)]]
    f = bm.faces.new(vs); uv = bm.loops.layers.uv.verify()
    for l, c in zip(f.loops, [(0, 0), (1, 0), (1, 1), (0, 1)]): l[uv].uv = c
    bm.normal_update(); bm.to_mesh(me); bm.free()
    o.location = centro; o.data.materials.append(material)
    return o

def malla_rejilla(nombre, nx, ny, funcion, material):
    """malla de nx x ny puntos; funcion(i/nx, j/ny) -> (x, y, z). Sirve para telas (frazada, cortinas)"""
    import bmesh
    me = bpy.data.meshes.new(nombre); o = bpy.data.objects.new(nombre, me); bpy.context.collection.objects.link(o)
    bm = bmesh.new()
    vs = [[bm.verts.new(funcion(i / (nx - 1), j / (ny - 1))) for j in range(ny)] for i in range(nx)]
    for i in range(nx - 1):
        for j in range(ny - 1):
            bm.faces.new([vs[i][j], vs[i + 1][j], vs[i + 1][j + 1], vs[i][j + 1]])
    bm.normal_update(); bm.to_mesh(me); bm.free()
    o.data.materials.append(material)
    for p in me.polygons: p.use_smooth = True
    return o

def suave(o, niveles=2):
    md = o.modifiers.new('suave', 'SUBSURF'); md.levels = niveles; md.render_levels = niveles
    return o

def organico(o, fuerza=0.1, tam=0.4, semilla=1):
    """deforma la malla con ruido (copas de árbol, cerros) para que no parezca de polígonos"""
    t = bpy.data.textures.get(f'nubes{semilla}') or bpy.data.textures.new(f'nubes{semilla}', 'CLOUDS')
    t.noise_scale = tam; t.noise_depth = 3
    d = o.modifiers.new('relieve', 'DISPLACE'); d.texture = t; d.strength = fuerza; d.mid_level = 0.5
    d.texture_coords = 'GLOBAL'
    return o
