# utilidades para las ilustraciones 3D de TAMPU (Blender por bpy)
import bpy, math, random
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

def mat(nombre, color, rugosidad=0.75, metal=0.0, emision=None, fuerza=3):
    m = bpy.data.materials.get(nombre)
    if m: return m
    m = bpy.data.materials.new(nombre); m.use_nodes = True
    b = m.node_tree.nodes['Principled BSDF']
    b.inputs['Base Color'].default_value = (*[c ** 2.2 for c in color], 1)   # colores en sRGB -> lineal
    b.inputs['Roughness'].default_value = rugosidad
    b.inputs['Metallic'].default_value = metal
    if emision:
        b.inputs['Emission Color'].default_value = (*emision, 1); b.inputs['Emission Strength'].default_value = fuerza
    return m

def caja(nombre, loc, dim, color, bisel=0.02, rot=(0, 0, 0)):
    bpy.ops.mesh.primitive_cube_add(location=loc, rotation=rot)
    o = bpy.context.object; o.name = nombre
    o.scale = (dim[0] / 2, dim[1] / 2, dim[2] / 2)
    bpy.ops.object.transform_apply(scale=True)
    if bisel:
        md = o.modifiers.new('bisel', 'BEVEL'); md.width = bisel; md.segments = 3
    o.data.materials.append(color if isinstance(color, bpy.types.Material) else mat(nombre + '_m', color))
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
    sc = bpy.context.scene
    sc.render.engine = 'CYCLES'; sc.cycles.device = 'CPU'; sc.cycles.samples = muestras; sc.cycles.use_denoising = True
    sc.render.resolution_x = ancho; sc.render.resolution_y = alto; sc.render.film_transparent = True
    sc.render.image_settings.file_format = 'PNG'; sc.render.image_settings.color_mode = 'RGBA'
    sc.view_settings.view_transform = 'AgX'; sc.view_settings.look = 'AgX - Medium High Contrast'
    sc.render.filepath = salida
    bpy.ops.render.render(write_still=True)
