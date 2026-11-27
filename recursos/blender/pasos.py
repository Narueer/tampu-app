import sys; sys.path.insert(0, __import__('os').path.dirname(__file__))
from base import *
cual = sys.argv[-3]; salida = sys.argv[-2]; muestras = int(sys.argv[-1])
limpiar()
cil('disco', (0, 0, -0.08), 1.25, 0.16, PAL['piedra2'], verts=64)
cil('disco_top', (0, 0, 0.005), 1.18, 0.02, PAL['crema'], verts=64)
if cual == 'registro':
    # mochila de viaje + carnet con check (crear cuenta y decir de dónde vienes)
    caja('mochila', (-0.35, 0.15, 0.55), (0.75, 0.5, 1.05), PAL['terracota'], bisel=0.18)
    caja('bolsillo', (-0.35, -0.12, 0.38), (0.55, 0.08, 0.38), (0.6, 0.27, 0.13), bisel=0.08)
    caja('tapa', (-0.35, 0.1, 1.0), (0.7, 0.52, 0.18), (0.6, 0.27, 0.13), bisel=0.08)
    for x in (-0.6, -0.1):
        caja(f'correa{x}', (x, -0.105, 0.75), (0.07, 0.03, 0.5), PAL['madera'], bisel=0.01)
    for i, c in enumerate([PAL['ocre'], PAL['verde'], PAL['ocre'], (0.75, 0.2, 0.3)]):  # franja de aguayo
        caja(f'aguayo{i}', (-0.35, -0.118, 0.72 - i * 0.06), (0.5, 0.02, 0.05), c, bisel=0.005)
    caja('carnet', (0.55, -0.15, 0.42), (0.75, 0.05, 0.5), PAL['blanco'], bisel=0.03, rot=(math.radians(-8), 0, math.radians(-18)))
    caja('foto', (0.33, -0.21, 0.46), (0.2, 0.02, 0.26), PAL['azul'], bisel=0.01, rot=(math.radians(-8), 0, math.radians(-18)))
    for k in range(3):
        caja(f'lin{k}', (0.65, -0.24 + 0.0 * k, 0.53 - k * 0.09), (0.32, 0.02, 0.035), PAL['piedra'], bisel=0.005, rot=(math.radians(-8), 0, math.radians(-18)))
    cil('sello', (0.85, -0.35, 0.72), 0.17, 0.06, PAL['verde'], rot=(math.radians(82), 0, math.radians(-18)))
    caja('check1', (0.82, -0.40, 0.70), (0.05, 0.02, 0.12), PAL['blanco'], bisel=0.005, rot=(math.radians(82), math.radians(-40), math.radians(-18)))
    caja('check2', (0.89, -0.40, 0.74), (0.05, 0.02, 0.2), PAL['blanco'], bisel=0.005, rot=(math.radians(82), math.radians(35), math.radians(-18)))
elif cual == 'horario':
    # calendario de mesa con turnos marcados + reloj (el horario de clases)
    caja('cal_base', (0, 0.1, 0.05), (1.5, 0.7, 0.1), PAL['madera'], bisel=0.03)
    caja('cal_hoja', (0, 0.2, 0.62), (1.4, 0.06, 1.05), PAL['blanco'], bisel=0.02, rot=(math.radians(-10), 0, 0))
    caja('cal_cabeza', (0, 0.1, 1.12), (1.42, 0.08, 0.2), PAL['terracota'], bisel=0.02, rot=(math.radians(-10), 0, 0))
    for i in range(5):
        cil(f'anillo{i}', (-0.5 + i * 0.25, 0.1, 1.24), 0.03, 0.08, PAL['tinta'], rot=(math.radians(80), 0, 0))
    marcados = {(0, 0), (1, 0), (2, 0), (3, 0), (0, 1), (2, 1), (4, 2)}
    for f in range(3):
        for c in range(5):
            on = (c, f) in marcados
            z = 0.88 - f * 0.25; x = -0.5 + c * 0.25; y = 0.13 + (0.88 - z) * math.tan(math.radians(10))
            caja(f'celda{f}{c}', (x, y, z), (0.18, 0.03, 0.17), PAL['terracota'] if on else PAL['crema'], bisel=0.02, rot=(math.radians(-10), 0, 0))
    cil('reloj', (0.78, -0.45, 0.32), 0.3, 0.12, PAL['ocre'], rot=(math.radians(75), 0, math.radians(-25)))
    cil('reloj_cara', (0.765, -0.505, 0.33), 0.24, 0.02, PAL['blanco'], rot=(math.radians(75), 0, math.radians(-25)))
    caja('aguja1', (0.76, -0.53, 0.39), (0.03, 0.01, 0.15), PAL['tinta'], bisel=0, rot=(math.radians(75), 0, math.radians(-25)))
    caja('aguja2', (0.81, -0.53, 0.34), (0.11, 0.01, 0.03), PAL['tinta'], bisel=0, rot=(math.radians(75), 0, math.radians(-25)))
else:
    # casita con una llave grande (pedir la visita y mudarte)
    caja('muro', (-0.25, 0.25, 0.42), (0.95, 0.8, 0.8), PAL['adobe'], bisel=0.03)
    caja('zocalo', (-0.25, 0.25, 0.08), (0.97, 0.82, 0.12), PAL['azul'], bisel=0.01)
    tejado('techo', (-0.25, 0.25, 0.82), 0.95, 0.8, 0.38, PAL['teja'], vuelo=0.08)
    caja('puerta', (-0.25, -0.16, 0.32), (0.26, 0.04, 0.46), PAL['madera'], bisel=0.01)
    caja('ven', (0.08, -0.16, 0.5), (0.2, 0.04, 0.2), PAL['azul'], bisel=0.01)
    oro = mat('oro', PAL['ocre'], 0.25, 0.9)
    cil('llave_aro', (0.62, -0.35, 0.62), 0.22, 0.07, oro, rot=(math.radians(90), 0, math.radians(-20)))
    cil('llave_hueco', (0.62, -0.39, 0.62), 0.1, 0.09, PAL['crema'], rot=(math.radians(90), 0, math.radians(-20)))
    caja('llave_barra', (0.62, -0.35, 0.2), (0.08, 0.06, 0.62), oro, bisel=0.01, rot=(0, 0, math.radians(-20)))
    caja('diente1', (0.70, -0.38, 0.0 + 0.06), (0.14, 0.06, 0.06), oro, bisel=0.01, rot=(0, 0, math.radians(-20)))
    caja('diente2', (0.70, -0.38, 0.18), (0.11, 0.06, 0.06), oro, bisel=0.01, rot=(0, 0, math.radians(-20)))
suelo_sombra(z=-0.16)
luces(3.2, 0.9, (52, 6, 30))
camara_iso((0.1, 0, 0.5), dist=12, alt_grados=24, az_grados=28, escala=3.0)
render(salida, 700, 640, muestras)
