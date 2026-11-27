import sys; sys.path.insert(0, __import__('os').path.dirname(__file__))
from base import *
limpiar()
# diorama: losa redondeada con pasto y calle empedrada
caja('losa', (0, 0.2, -0.12), (5.2, 4.4, 0.24), PAL['piedra2'], bisel=0.12)
caja('pasto', (0, 0.95, 0.02), (5.0, 2.9, 0.06), PAL['pasto'], bisel=0.05)
random.seed(4)
n = 0
for fila in range(5):  # empedrado en filas, sin piedras encimadas
    y = -1.8 + fila * 0.22; x = -2.5 + (0.12 if fila % 2 else 0)
    while x < 2.4:
        w = random.uniform(0.2, 0.3)
        caja(f'emp{n}', (x + w / 2, y, 0.03), (w - 0.035, 0.18, 0.07), PAL['piedra'] if n % 3 else PAL['piedra2'], bisel=0.025); x += w; n += 1
# base de piedra inca bajo la casa
muro_inca('base', -1.45, -0.62, 0.0, 2.9, 0.5, 0.2, PAL['piedra'], filas=2, semilla=7)
caja('plataforma', (0, 0.35, 0.25), (2.9, 1.9, 0.5), PAL['piedra2'], bisel=0.04)
# casa de adobe
caja('muro', (0, 0.45, 1.3), (2.6, 1.5, 1.6), PAL['adobe'], bisel=0.03)
caja('zocalo', (0, 0.45, 0.58), (2.64, 1.54, 0.16), PAL['azul'], bisel=0.01)
tejado('techo', (0, 0.45, 2.1), 2.6, 1.5, 0.6, PAL['teja'])
caja('chimenea', (0.8, 0.75, 2.45), (0.22, 0.22, 0.5), PAL['adobe2'], bisel=0.02)
# puerta y ventanas del primer piso
caja('puerta_marco', (-0.6, -0.31, 0.98), (0.56, 0.06, 0.86), PAL['azul'], bisel=0.01)
caja('puerta', (-0.6, -0.34, 0.95), (0.42, 0.03, 0.74), PAL['madera'], bisel=0.005)
for x in (0.3, 0.9):
    caja(f'ven{x}', (x, -0.31, 1.05), (0.42, 0.05, 0.44), PAL['azul'], bisel=0.01)
    caja(f'vid{x}', (x, -0.34, 1.05), (0.32, 0.02, 0.34), mat('vidrio', PAL['vidrio'], 0.12))
    caja(f'cruz{x}', (x, -0.355, 1.05), (0.025, 0.01, 0.34), PAL['blanco'], bisel=0)
# segundo piso: balcón de madera
for x in (-0.55, 0.15, 0.85):
    caja(f'ven2{x}', (x, -0.31, 1.78), (0.4, 0.05, 0.5), PAL['azul'], bisel=0.01)
    caja(f'vid2{x}', (x, -0.34, 1.78), (0.3, 0.02, 0.4), mat('vidrio', PAL['vidrio'], 0.12))
caja('balcon_piso', (0.15, -0.5, 1.5), (2.2, 0.4, 0.07), PAL['madera'], bisel=0.01)
caja('baranda', (0.15, -0.69, 1.7), (2.2, 0.04, 0.05), PAL['azul'], bisel=0.005)
for i in range(13):
    caja(f'bal{i}', (-0.9 + i * 0.175, -0.69, 1.6), (0.03, 0.03, 0.2), PAL['azul'], bisel=0.004)
caja('balcon_techo', (0.15, -0.55, 2.12), (2.3, 0.5, 0.05), PAL['teja2'], bisel=0.01, rot=(math.radians(-14), 0, 0))
for x in (-0.92, 1.22):
    caja(f'poste{x}', (x, -0.69, 1.82), (0.05, 0.05, 0.62), PAL['madera'], bisel=0.005)
# macetas con flores en la vereda y en el balcón
def maceta(x, y, z, s=1):
    cil(f'mac{x}{y}', (x, y, z + 0.09 * s), 0.09 * s, 0.18 * s, PAL['terracota'])
    bpy.ops.mesh.primitive_ico_sphere_add(radius=0.12 * s, subdivisions=2, location=(x, y, z + 0.26 * s)); o = bpy.context.object; o.data.materials.append(mat('hojas', PAL['verde2'])); bpy.ops.object.shade_smooth()
    for k in range(5):
        bpy.ops.mesh.primitive_uv_sphere_add(radius=0.03 * s, location=(x + 0.08 * s * math.cos(k * 1.3), y + 0.08 * s * math.sin(k * 1.3), z + 0.33 * s)); bpy.context.object.data.materials.append(mat('flor', PAL['rosa']))
for x in (-1.15, 1.25): maceta(x, -0.85, 0.06)
for x in (-0.5, 0.5): maceta(x, -0.52, 1.53, 0.7)
# farol colonial
cil('farol_poste', (1.75, -0.95, 0.75), 0.03, 1.5, PAL['tinta'])
caja('farol', (1.75, -0.95, 1.55), (0.16, 0.16, 0.22), mat('farol', (1, 0.85, 0.55), 0.3, emision=(1, 0.8, 0.5)), bisel=0.02)
# eucalipto
cil('tronco', (-2.05, 0.1, 0.5), 0.06, 1.0, PAL['madera'])
for (dx, dz, r) in [(0, 1.15, 0.42), (0.18, 1.45, 0.3), (-0.15, 1.4, 0.28)]:
    bpy.ops.mesh.primitive_ico_sphere_add(radius=r, subdivisions=2, location=(-2.05 + dx, 0.1, dz)); bpy.context.object.data.materials.append(mat('copa', PAL['verde2']))
# cerros con nieve (Apus) al fondo
for (x, y, r, h) in [(-1.3, 2.0, 0.95, 1.7), (0.4, 2.15, 1.15, 2.3), (1.85, 1.95, 0.75, 1.3)]:
    bpy.ops.mesh.primitive_cone_add(vertices=6, radius1=r, depth=h, location=(x, y, h / 2)); bpy.context.object.data.materials.append(mat(f'cerro{x}', PAL['cerro'], 0.95))
    bpy.ops.mesh.primitive_cone_add(vertices=6, radius1=r * 0.32, depth=h * 0.32, location=(x, y, h - h * 0.16 + 0.01)); bpy.context.object.data.materials.append(mat('nieve', PAL['blanco'], 0.6))
suelo_sombra(z=-0.24)
luces(3.6, 0.8, (50, 6, 30))
camara_iso((0.3, 0.3, 1.0), dist=14, alt_grados=26, az_grados=30, escala=7.3)
render(sys.argv[-2] if len(sys.argv) > 2 else '/home/claude/b3d/casa.png', 1100, 1000, int(sys.argv[-1]) if sys.argv[-1].isdigit() else 64)
