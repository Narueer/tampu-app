import sys; sys.path.insert(0, __import__('os').path.dirname(__file__))
from base import *
import texturas
limpiar()
# Cuarto de estudiante en corte (dos paredes y piso), con texturas hechas con código (texturas.py)

# ---- piso de tablas y paredes de adobe enlucido ----
caja('piso', (0, 0, -0.06), (3.2, 3.0, 0.12), PAL['madera'], bisel=0.03)
random.seed(2)
for i in range(8):
    tono = random.choice([(0.62, 0.42, 0.26), (0.58, 0.38, 0.23), (0.66, 0.46, 0.29)])
    caja(f'tabla{i}', (-1.4 + i * 0.4, 0, 0.005), (0.392, 2.96, 0.02), tono, bisel=0.004, tex='madera')
caja('pared_fondo', (0, 1.45, 1.2), (3.2, 0.1, 2.4), PAL['adobe'], bisel=0.02)
caja('pared_izq', (-1.55, 0, 1.2), (0.1, 3.0, 2.4), PAL['adobe2'], bisel=0.02)
caja('zocalo_f', (0, 1.39, 0.08), (3.1, 0.03, 0.16), PAL['azul'], bisel=0.005)
caja('zocalo_i', (-1.49, 0, 0.08), (0.03, 2.9, 0.16), PAL['azul'], bisel=0.005)
caja('zocalo_enchufe', (1.25, 1.395, 0.42), (0.12, 0.02, 0.08), PAL['blanco'], bisel=0.004)
caja('zocalo_interruptor', (-1.495, 1.1, 1.1), (0.02, 0.08, 0.12), PAL['blanco'], bisel=0.004)

# ---- ventana con vista a los Apus y techos de Cusco ----
caja('ven_marco', (0.55, 1.4, 1.45), (1.1, 0.06, 0.9), PAL['azul'], bisel=0.01)
plano_imagen('ven_cielo', (0.55, 1.365, 1.45), 0.96, 0.76, mat_imagen('paisaje', texturas.paisaje(), emision=1.2))
caja('ven_cruz', (0.55, 1.355, 1.45), (0.03, 0.02, 0.76), PAL['blanco'], bisel=0.003)
caja('ven_cruz2', (0.55, 1.355, 1.45), (0.96, 0.02, 0.03), PAL['blanco'], bisel=0.003)
caja('alfeizar', (0.55, 1.33, 0.99), (1.2, 0.16, 0.05), PAL['blanco'], bisel=0.01)
cil('ven_barra', (0.55, 1.3, 2.0), 0.012, 1.7, PAL['tinta'], rot=(0, math.radians(90), 0))
tela_cortina = mat('cortina', (0.86, 0.56, 0.36), 0.9, tex='tela')
for lado, x0 in (('i', -0.3), ('d', 1.12)):
    ancho = 0.28 if lado == 'i' else 0.3
    malla_rejilla(f'ven_cortina_{lado}', 24, 8,
                  lambda u, v, x0=x0, ancho=ancho: (x0 + (u - 0.5) * ancho, 1.3 - 0.03 * math.sin(u * math.pi * 6) * (0.6 + 0.4 * (1 - v)), 1.98 - v * 1.15), tela_cortina)

# ---- cama: base de madera, colchón y almohada suaves, frazada de aguayo que cae por los lados ----
caja('cama_base', (-0.85, 0.35, 0.2), (1.15, 2.0, 0.26), PAL['madera'], bisel=0.03)
for (dx, dy) in [(-0.52, -0.6), (0.52, -0.6)]:
    caja(f'cama_pata{dx}', (-0.85 + dx, 0.35 + dy, 0.04), (0.08, 0.08, 0.1), PAL['madera'], bisel=0.01)
suave(caja('colchon', (-0.85, 0.35, 0.42), (1.06, 1.9, 0.2), (0.95, 0.94, 0.9), bisel=0.05, tex='tela'), 1)
caja('cabecera', (-0.85, 1.36, 0.62), (1.15, 0.08, 0.85), PAL['madera'], bisel=0.03)
for k in range(3):
    caja(f'cabecera_list{k}', (-1.2 + k * 0.35, 1.31, 0.78), (0.2, 0.02, 0.36), (0.42, 0.27, 0.16), bisel=0.01, tex='madera')
alm = suave(caja('almohada', (-0.85, 1.06, 0.58), (0.72, 0.34, 0.14), (0.98, 0.96, 0.92), bisel=0.05, tex='tela'), 1)
alm.rotation_euler = (math.radians(-8), 0, math.radians(2))

def frazada(u, v):
    # u: ancho (x), v: largo (y). Arriba sobre el colchón; en los bordes cae con una curva
    x = -0.85 + (u - 0.5) * 1.5
    y = -0.6 + v * 1.45
    z = 0.535 + 0.012 * math.sin(v * 9) * math.sin(u * 7)       # arrugas suaves
    sobra = abs(x + 0.85) - 0.55
    if sobra > 0:
        x = -0.85 + math.copysign(0.55 + 0.05 * (1 - math.exp(-sobra * 12)), x + 0.85)
        z -= sobra * 1.15
    if y < -0.58:
        pass
    return (x, y, z)
malla_rejilla('fraz_manta', 40, 30, frazada, mat('aguayo', (1, 1, 1), 0.85, tex='aguayo'))
# borde de sábana doblada sobre la frazada
suave(caja('fraz_sabana', (-0.85, 0.86, 0.55), (1.12, 0.14, 0.035), (0.97, 0.96, 0.93), bisel=0, tex='tela'), 1)
# extremo de la frazada que cae a los pies
malla_rejilla('fraz_pies', 30, 6, lambda u, v: (-0.85 + (u - 0.5) * 1.2, -0.6 - 0.04 * v, 0.535 - v * 0.26), mat('aguayo', (1, 1, 1)))

# ---- escritorio con laptop (TAMPU abierto), libros, taza y lámpara ----
caja('mesa', (0.75, 0.75, 0.74), (1.2, 0.6, 0.05), PAL['madera'], bisel=0.012)
for (x, y) in [(0.2, 0.5), (1.3, 0.5), (0.2, 1.0), (1.3, 1.0)]:
    caja(f'pata{x}{y}', (x, y, 0.36), (0.045, 0.045, 0.72), PAL['madera'], bisel=0.006)
caja('mesa_cajon', (1.05, 0.5, 0.66), (0.5, 0.06, 0.12), PAL['madera'], bisel=0.008)
caja('mesa_tirador', (1.05, 0.465, 0.66), (0.1, 0.012, 0.015), (0.75, 0.7, 0.6), bisel=0.003, tex=None)
alu = mat('aluminio', (0.72, 0.73, 0.75), 0.35, metal=0.8, tex=None)
caja('laptop_base', (0.7, 0.68, 0.775), (0.42, 0.3, 0.016), alu, bisel=0.004)
caja('laptop_teclado', (0.7, 0.66, 0.784), (0.36, 0.15, 0.003), mat('teclado', (0.15, 0.16, 0.17), 0.6, tex=None), bisel=0)
caja('laptop_pantalla', (0.7, 0.84, 0.925), (0.42, 0.014, 0.28), alu, bisel=0.004, rot=(math.radians(-12), 0, 0))
pant = plano_imagen('pantalla_luz', (0.7, 0.831, 0.925), 0.38, 0.245, mat_imagen('pantalla', texturas.pantalla(), emision=1.6, rugosidad=0.2))
pant.rotation_euler = (math.radians(-12), 0, 0)
for i, c in enumerate([PAL['terracota'], PAL['azul'], PAL['ocre']]):
    caja(f'libro{i}', (1.18, 0.75, 0.79 + i * 0.045), (0.3 - i * 0.03, 0.21, 0.04), c, bisel=0.004, rot=(0, 0, math.radians(8 * i)), tex=None)
    caja(f'libro_hojas{i}', (1.18 + 0.006, 0.75 - 0.006, 0.79 + i * 0.045), (0.28 - i * 0.03, 0.2, 0.032), (0.96, 0.94, 0.88), bisel=0.002, rot=(0, 0, math.radians(8 * i)), tex=None)
taza = cil('taza', (0.32, 0.62, 0.815), 0.04, 0.09, (0.86, 0.36, 0.2))
bpy.ops.mesh.primitive_torus_add(major_radius=0.025, minor_radius=0.007, location=(0.365, 0.62, 0.815), rotation=(math.radians(90), 0, 0))
bpy.context.object.name = 'taza_asa'; bpy.context.object.data.materials.append(mat('taza_asa', (0.86, 0.36, 0.2), 0.4, tex=None))
cil('taza_cafe', (0.32, 0.62, 0.856), 0.034, 0.004, (0.25, 0.13, 0.06))
cil('lampara_base', (0.28, 0.95, 0.775), 0.07, 0.02, PAL['tinta'])
cil('lampara_brazo', (0.28, 0.95, 0.95), 0.01, 0.34, PAL['tinta'])
bpy.ops.mesh.primitive_cone_add(radius1=0.1, radius2=0.035, depth=0.13, location=(0.34, 0.9, 1.1), rotation=(math.radians(32), 0, 0)); bpy.context.object.data.materials.append(mat('pantalla_l', PAL['ocre'], 0.5, tex=None))
bpy.ops.mesh.primitive_uv_sphere_add(radius=0.03, location=(0.355, 0.875, 1.06)); bpy.context.object.name = 'lampara_foco'
bpy.context.object.data.materials.append(mat('foco', (1, 0.9, 0.7), emision=(1, 0.82, 0.55), fuerza=6))
# silla de madera pintada con cojín de aguayo
caja('silla_asiento', (0.75, 0.2, 0.45), (0.45, 0.42, 0.045), PAL['azul'], bisel=0.015)
caja('silla_resp', (0.75, -0.0, 0.74), (0.45, 0.045, 0.32), PAL['azul'], bisel=0.015)
suave(caja('silla_cojin', (0.75, 0.22, 0.49), (0.38, 0.36, 0.05), (1, 1, 1), bisel=0.02, tex='aguayo'), 1)
for (x, y) in [(0.56, 0.02), (0.94, 0.02), (0.56, 0.38), (0.94, 0.38)]:
    caja(f'spata{x}{y}', (x, y, 0.22), (0.04, 0.04, 0.44), PAL['azul'], bisel=0.004)
for x in (0.56, 0.94):
    caja(f'spata_resp{x}', (x, 0.0, 0.6), (0.04, 0.04, 0.3), PAL['azul'], bisel=0.004)

# ---- mochila (tela, con forma suave) ----
suave(caja('mochila', (1.2, -0.55, 0.25), (0.38, 0.24, 0.5), (0.71, 0.33, 0.16), bisel=0.07, tex='tela'), 1)
suave(caja('bolsillo', (1.2, -0.68, 0.17), (0.28, 0.06, 0.2), (0.6, 0.27, 0.13), bisel=0.025, tex='tela'), 1)
caja('mochila_asa', (1.2, -0.55, 0.52), (0.14, 0.03, 0.05), (0.2, 0.2, 0.2), bisel=0.01, tex=None)

# ---- planta en maceta de arcilla ----
cil('maceta', (-1.25, -1.15, 0.15), 0.13, 0.3, PAL['terracota'])
cil('maceta_tierra', (-1.25, -1.15, 0.295), 0.12, 0.01, (0.25, 0.17, 0.1))
random.seed(5)
hoja = mat('hoja', PAL['verde2'], 0.55, tex=None); hoja2 = mat('hoja2', (0.24, 0.40, 0.22), 0.55, tex=None)
for k in range(14):
    a = k * 2.4; r = 0.03 + 0.06 * (k % 3) / 2
    bpy.ops.mesh.primitive_uv_sphere_add(radius=0.1, segments=16, ring_count=8,
                                         location=(-1.25 + r * math.cos(a), -1.15 + r * math.sin(a), 0.42 + 0.03 * (k % 4)))
    o = bpy.context.object; o.scale = (0.32, 0.7, 1.9)
    o.rotation_euler = (math.radians(random.uniform(10, 35)), 0, a + math.pi / 2)
    o.data.materials.append(hoja if k % 2 else hoja2); bpy.ops.object.shade_smooth()

# ---- cuadro con chakana y repisa con libros ----
caja('cuadro', (-1.49, -0.2, 1.55), (0.03, 0.55, 0.55), PAL['madera'], bisel=0.01)
caja('cuadro_in', (-1.47, -0.2, 1.55), (0.02, 0.47, 0.47), PAL['crema'], bisel=0, tex='tela')
for (w, h) in [(0.12, 0.36), (0.36, 0.12), (0.24, 0.24)]:
    caja(f'chak{w}{h}', (-1.455, -0.2, 1.55), (0.01, w, h), PAL['terracota'], bisel=0, tex=None)
caja('cuadro_repisa', (-1.4, 0.75, 1.32), (0.2, 0.7, 0.03), PAL['madera'], bisel=0.005)
for k, c in enumerate([(0.6, 0.2, 0.2), (0.15, 0.35, 0.5), (0.85, 0.65, 0.25), (0.25, 0.45, 0.3), (0.5, 0.3, 0.55), (0.9, 0.88, 0.82)]):
    h = 0.2 + 0.05 * ((k * 7) % 3)
    caja(f'cuadro_lib{k}', (-1.4, 0.48 + k * 0.065, 1.335 + h / 2), (0.15, 0.05, h), c, bisel=0.004, rot=(math.radians(6) if k == 5 else 0, 0, 0), tex=None)
cil('cuadro_vela', (-1.4, 1.0, 1.37), 0.03, 0.07, PAL['ocre'])

# ---- alfombra tejida ----
caja('alfombra', (0.1, -0.6, 0.012), (1.3, 0.8, 0.012), (1, 1, 1), bisel=0.004, tex='aguayo')

suelo_sombra(z=-0.12)
luces(2.6, 0.9, (55, 5, -30))
# luz cálida que entra por la ventana y la de la lámpara
v = bpy.data.lights.new('ventana', 'AREA'); v.energy = 120; v.size = 1.0; v.color = (1, 0.9, 0.75)
o = bpy.data.objects.new('ventana', v); bpy.context.collection.objects.link(o); o.location = (0.55, 1.1, 1.45); o.rotation_euler = (math.radians(-75), 0, 0)
p = bpy.data.lights.new('interior', 'POINT'); p.energy = 70; p.color = (1, 0.85, 0.65); o = bpy.data.objects.new('interior', p); bpy.context.collection.objects.link(o); o.location = (0.5, -0.5, 2.2)
l = bpy.data.lights.new('lampara', 'POINT'); l.energy = 12; l.color = (1, 0.8, 0.5); l.shadow_soft_size = 0.05
o = bpy.data.objects.new('lampara_luz', l); bpy.context.collection.objects.link(o); o.location = (0.36, 0.86, 1.03)
camara_iso((0.0, 0.1, 0.85), dist=14, alt_grados=30, az_grados=40, escala=4.9)
render(sys.argv[-2] if len(sys.argv) > 2 else '/home/claude/b3d/cuarto.png', 1100, 1000, int(sys.argv[-1]) if sys.argv[-1].isdigit() else 64)
