import sys; sys.path.insert(0, __import__('os').path.dirname(__file__))
from base import *
limpiar()
# cuarto en corte (dos paredes y piso), estilo maqueta
caja('piso', (0, 0, -0.06), (3.2, 3.0, 0.12), PAL['madera'], bisel=0.03)
for i in range(8):  # tablas del piso
    caja(f'tabla{i}', (-1.4 + i * 0.4, 0, 0.005), (0.38, 2.96, 0.02), (0.55, 0.36, 0.22) if i % 2 else (0.6, 0.4, 0.25), bisel=0.005)
caja('pared_fondo', (0, 1.45, 1.2), (3.2, 0.1, 2.4), PAL['adobe'], bisel=0.02)
caja('pared_izq', (-1.55, 0, 1.2), (0.1, 3.0, 2.4), PAL['adobe2'], bisel=0.02)
caja('zocalo_f', (0, 1.39, 0.08), (3.1, 0.03, 0.16), PAL['azul'], bisel=0.005)
caja('zocalo_i', (-1.49, 0, 0.08), (0.03, 2.9, 0.16), PAL['azul'], bisel=0.005)
# ventana con vista a los cerros
caja('ven_marco', (0.55, 1.4, 1.45), (1.1, 0.06, 0.9), PAL['azul'], bisel=0.01)
caja('ven_cielo', (0.55, 1.37, 1.45), (0.95, 0.02, 0.75), mat('cielo', (0.55, 0.75, 0.92), 0.4, emision=(0.45, 0.68, 0.92), fuerza=0.6))
import bmesh
for (dx, alto, ancho) in [(-0.22, 0.34, 0.55), (0.2, 0.46, 0.7)]:  # cerros vistos por la ventana (triángulos planos)
    me = bpy.data.meshes.new('cerro'); ob = bpy.data.objects.new('cerro', me); bpy.context.collection.objects.link(ob)
    bm = bmesh.new(); vs = [bm.verts.new(p) for p in [(-ancho / 2, 0, 0), (ancho / 2, 0, 0), (0, 0, alto)]]; bm.faces.new(vs); bm.to_mesh(me); bm.free()
    ob.location = (0.55 + dx, 1.355, 1.08); ob.data.materials.append(mat('cerro_v', PAL['verde2'])); ob.modifiers.new('g', 'SOLIDIFY').thickness = 0.005
caja('ven_cruz', (0.55, 1.35, 1.45), (0.03, 0.02, 0.75), PAL['blanco'], bisel=0)
caja('alfeizar', (0.55, 1.33, 0.99), (1.2, 0.16, 0.05), PAL['blanco'], bisel=0.01)
# cama con frazada andina a rayas
caja('cama_base', (-0.85, 0.35, 0.22), (1.15, 2.0, 0.3), PAL['madera'], bisel=0.03)
caja('colchon', (-0.85, 0.35, 0.44), (1.08, 1.92, 0.16), PAL['blanco'], bisel=0.05)
caja('cabecera', (-0.85, 1.36, 0.6), (1.15, 0.08, 0.8), PAL['madera'], bisel=0.03)
caja('almohada', (-0.85, 1.08, 0.58), (0.7, 0.3, 0.12), PAL['crema'], bisel=0.05)
colores = [PAL['terracota'], PAL['ocre'], PAL['verde'], (0.75, 0.2, 0.3), PAL['azul'], PAL['ocre'], PAL['terracota']]
for i, c in enumerate(colores):  # frazada (rayas como un tejido)
    caja(f'fraz{i}', (-0.85, -0.45 + i * 0.165, 0.54), (1.12, 0.165, 0.05), c, bisel=0.01)
# escritorio con laptop, libros y lámpara
caja('mesa', (0.75, 0.75, 0.74), (1.2, 0.6, 0.06), PAL['madera'], bisel=0.015)
for (x, y) in [(0.2, 0.5), (1.3, 0.5), (0.2, 1.0), (1.3, 1.0)]:
    caja(f'pata{x}{y}', (x, y, 0.36), (0.05, 0.05, 0.72), PAL['madera'], bisel=0.005)
caja('laptop_base', (0.7, 0.68, 0.785), (0.42, 0.3, 0.02), PAL['piedra2'], bisel=0.005)
caja('laptop_pantalla', (0.7, 0.84, 0.93), (0.42, 0.02, 0.28), PAL['tinta'], bisel=0.005, rot=(math.radians(-12), 0, 0))
caja('pantalla_luz', (0.7, 0.828, 0.93), (0.38, 0.005, 0.24), mat('pantalla', (0.35, 0.55, 0.5), 0.2, emision=(0.4, 0.7, 0.6)), bisel=0, rot=(math.radians(-12), 0, 0))
for i, c in enumerate([PAL['terracota'], PAL['azul'], PAL['ocre']]):
    caja(f'libro{i}', (1.18, 0.75, 0.8 + i * 0.05), (0.3 - i * 0.03, 0.2, 0.045), c, bisel=0.005, rot=(0, 0, math.radians(8 * i)))
cil('lampara_base', (0.28, 0.92, 0.785), 0.07, 0.02, PAL['tinta'])
cil('lampara_brazo', (0.28, 0.92, 0.95), 0.012, 0.32, PAL['tinta'])
bpy.ops.mesh.primitive_cone_add(radius1=0.1, radius2=0.04, depth=0.12, location=(0.34, 0.88, 1.1), rotation=(math.radians(30), 0, 0)); bpy.context.object.data.materials.append(mat('pantalla_l', PAL['ocre'], 0.4))
caja('silla_asiento', (0.75, 0.2, 0.45), (0.45, 0.42, 0.05), PAL['azul'], bisel=0.02)
caja('silla_resp', (0.75, -0.0, 0.72), (0.45, 0.05, 0.5), PAL['azul'], bisel=0.02)
for (x, y) in [(0.56, 0.02), (0.94, 0.02), (0.56, 0.38), (0.94, 0.38)]:
    caja(f'spata{x}{y}', (x, y, 0.22), (0.04, 0.04, 0.44), PAL['tinta'], bisel=0.004)
# mochila y planta
caja('mochila', (1.2, -0.55, 0.25), (0.38, 0.24, 0.5), PAL['terracota'], bisel=0.1)
caja('bolsillo', (1.2, -0.68, 0.18), (0.28, 0.04, 0.2), (0.6, 0.27, 0.13), bisel=0.04)
cil('maceta', (-1.25, -1.15, 0.15), 0.13, 0.3, PAL['terracota'])
for k in range(6):
    a = k * math.pi / 3
    bpy.ops.mesh.primitive_uv_sphere_add(radius=0.1, location=(-1.25 + 0.09 * math.cos(a), -1.15 + 0.09 * math.sin(a), 0.42 + 0.06 * (k % 2)))
    o = bpy.context.object; o.scale = (0.6, 0.6, 1.6); o.data.materials.append(mat('hoja', PAL['verde2']))
# cuadro con patrón de chakana en la pared
caja('cuadro', (-1.49, -0.2, 1.55), (0.03, 0.55, 0.55), PAL['ocre'], bisel=0.01)
caja('cuadro_in', (-1.47, -0.2, 1.55), (0.02, 0.45, 0.45), PAL['crema'], bisel=0)
for (dy, dz, w, h) in [(0, 0, 0.12, 0.36), (0, 0, 0.36, 0.12), (0, 0, 0.24, 0.24)]:
    caja(f'chak{w}{h}', (-1.455, -0.2 + dy, 1.55 + dz), (0.01, w, h), PAL['terracota'], bisel=0)
# alfombra
caja('alfombra', (0.1, -0.6, 0.02), (1.3, 0.8, 0.02), PAL['ocre'], bisel=0.01)
for i in range(3):
    caja(f'alf{i}', (0.1, -0.85 + i * 0.25, 0.031), (1.2, 0.05, 0.005), PAL['terracota'], bisel=0)
suelo_sombra(z=-0.12)
luces(3.0, 1.0, (55, 5, -30))
p = bpy.data.lights.new('interior', 'POINT'); p.energy = 120; p.color = (1, 0.85, 0.65); o = bpy.data.objects.new('interior', p); bpy.context.collection.objects.link(o); o.location = (0.5, -0.5, 2.2)
camara_iso((0.0, 0.1, 0.85), dist=14, alt_grados=30, az_grados=40, escala=4.9)
render(sys.argv[-2] if len(sys.argv) > 2 else '/home/claude/b3d/cuarto.png', 1100, 1000, int(sys.argv[-1]) if sys.argv[-1].isdigit() else 64)
