import sys; sys.path.insert(0, __import__('os').path.dirname(__file__))
from base import *
# Ilustraciones de los 3 pasos ("Regístrate", "Marca tu horario", "Solicita una visita").
#   python pasos.py registro paso-registro.png 96     (también: horario, visita)
cual = sys.argv[-3]; salida = sys.argv[-2]; muestras = int(sys.argv[-1])
limpiar()

ANDESITA = (0.17, 0.17, 0.16)      # piedra volcánica oscura de los muros incas
metal = mat('metal', (0.78, 0.78, 0.76), 0.28, metal=1.0, tex=None)
laton = mat('laton', (0.86, 0.66, 0.30), 0.22, metal=1.0, tex=None)
negro = mat('negro_mate', (0.06, 0.06, 0.06), 0.6, tex=None)

def pegar(hijo, padre):
    hijo.parent = padre
    hijo.matrix_parent_inverse.identity()

def toro(nombre, loc, r_mayor, r_menor, material, rot=(0, 0, 0), arco=None):
    bpy.ops.mesh.primitive_torus_add(major_radius=r_mayor, minor_radius=r_menor, major_segments=48, minor_segments=12, location=loc, rotation=rot)
    o = bpy.context.object; o.name = nombre; o.data.materials.append(material); bpy.ops.object.shade_smooth()
    return o

# ---- pedestal: dos gradas de andesita negra, tejido de lana encima y franja de aguayo al frente ----
caja('grada0', (0, 0, -0.16), (2.3, 1.8, 0.12), ANDESITA, bisel=0.035, tex='piedra')
caja('grada1', (0, 0, -0.05), (2.02, 1.56, 0.1), (0.22, 0.22, 0.21), bisel=0.03, tex='piedra')
caja('tapete', (0, 0.0, 0.008), (1.86, 1.42, 0.014), (0.91, 0.87, 0.80), bisel=0.004, tex='tela')
caja('franja', (0, -0.787, -0.05), (1.9, 0.01, 0.065), (1, 1, 1), bisel=0, tex='aguayo')

if cual == 'registro':
    # ---- mochila de viaje de tela, con cierre, correas y hebillas ----
    tela = mat('tela_mochila', (0.62, 0.30, 0.17), 0.85, tex='tela')
    tela2 = mat('tela_bolsillo', (0.50, 0.24, 0.14), 0.85, tex='tela')
    cuerpo = suave(caja('mochila', (-0.35, 0.15, 0.56), (0.74, 0.46, 1.04), tela, bisel=0.16), 1)
    suave(caja('tapa', (-0.35, 0.08, 1.01), (0.72, 0.54, 0.2), tela2, bisel=0.08), 1)
    suave(caja('bolsillo', (-0.35, -0.11, 0.36), (0.56, 0.1, 0.4), tela2, bisel=0.06), 1)
    caja('cierre', (-0.35, -0.165, 0.5), (0.48, 0.008, 0.016), metal, bisel=0.002)
    caja('jalador', (-0.13, -0.172, 0.47), (0.03, 0.01, 0.07), metal, bisel=0.004)
    for x in (-0.58, -0.12):
        caja(f'correa{x}', (x, -0.12, 0.82), (0.07, 0.025, 0.34), (0.20, 0.17, 0.14), bisel=0.01, tex=None)
        caja(f'hebilla{x}', (x, -0.135, 0.68), (0.1, 0.02, 0.06), metal, bisel=0.008)
    toro('asa', (-0.35, 0.15, 1.12), 0.1, 0.018, mat('asa', (0.2, 0.17, 0.14), 0.7, tex=None), rot=(math.radians(90), 0, 0))
    caja('aguayo_mochila', (-0.35, -0.18, 0.21), (0.5, 0.012, 0.07), (1, 1, 1), bisel=0.004, tex='aguayo')
    # ---- carnet de estudiante con foto y sello de verificado ----
    giro = (math.radians(-8), 0, math.radians(-18))
    carnet = caja('carnet', (0.56, -0.17, 0.42), (0.78, 0.04, 0.52), (0.98, 0.97, 0.95), bisel=0.035, rot=giro, tex=None)
    caja('carnet_banda', (0.56, -0.193, 0.63), (0.74, 0.006, 0.08), (0.27, 0.33, 0.28), bisel=0, rot=giro, tex=None)
    caja('foto_fondo', (0.32, -0.22, 0.42), (0.2, 0.01, 0.25), (0.83, 0.86, 0.88), bisel=0.01, rot=giro, tex=None)
    bpy.ops.mesh.primitive_uv_sphere_add(radius=0.045, location=(0.318, -0.232, 0.46)); bpy.context.object.data.materials.append(mat('cara', (0.55, 0.42, 0.33), 0.6, tex=None)); bpy.ops.object.shade_smooth()
    for k in range(3):
        caja(f'lin{k}', (0.65, -0.235, 0.52 - k * 0.08), (0.32 - k * 0.06, 0.006, 0.03), (0.72, 0.72, 0.70), bisel=0.003, rot=giro, tex=None)
    sello = cil('sello', (0.86, -0.36, 0.7), 0.16, 0.05, mat('sello', (0.27, 0.33, 0.28), 0.35, tex=None), rot=(math.radians(82), 0, math.radians(-18)))
    c1 = caja('check1', (-0.04, -0.012, 0.03), (0.045, 0.12, 0.012), PAL['blanco'], bisel=0.004, rot=(0, 0, math.radians(40)), tex=None)
    c2 = caja('check2', (0.035, 0.028, 0.03), (0.045, 0.2, 0.012), PAL['blanco'], bisel=0.004, rot=(0, 0, math.radians(-30)), tex=None)
    pegar(c1, sello); pegar(c2, sello)
elif cual == 'horario':
    # ---- calendario de escritorio: base de madera, hojas apiladas y espiral de alambre ----
    caja('cal_base', (0, 0.12, 0.05), (1.5, 0.72, 0.1), PAL['madera'], bisel=0.03)
    papel = mat('papel', (0.97, 0.96, 0.93), 0.9, tex='tela')
    inclina = (math.radians(-10), 0, 0)
    for k in range(4):                                      # hojas apiladas (se ve el grosor)
        caja(f'hoja{k}', (0, 0.2 + k * 0.012, 0.62), (1.4, 0.008, 1.05), papel, bisel=0.004, rot=inclina)
    caja('cal_cabeza', (0, 0.12, 1.12), (1.42, 0.07, 0.2), mat('cabeza', (0.38, 0.46, 0.39), 0.6, tex=None), bisel=0.015, rot=inclina)
    for i in range(14):                                     # espiral de alambre
        toro(f'espiral{i}', (-0.62 + i * 0.095, 0.16, 1.22), 0.035, 0.006, metal, rot=(0, math.radians(90), 0))
    marcados = {(0, 0), (1, 0), (2, 0), (3, 0), (0, 1), (2, 1), (4, 2)}
    turno = mat('turno', (0.59, 0.36, 0.34), 0.7, tex=None); libre = mat('libre', (0.90, 0.88, 0.84), 0.8, tex=None)
    for f in range(3):
        for c in range(5):
            on = (c, f) in marcados
            z = 0.88 - f * 0.24; x = -0.5 + c * 0.25; y = 0.13 + (0.88 - z) * math.tan(math.radians(10))
            caja(f'celda{f}{c}', (x, y, z), (0.19, 0.012, 0.18), turno if on else libre, bisel=0.012, rot=inclina)
    # ---- despertador metálico con campanas ----
    giro = (math.radians(78), 0, math.radians(-25))
    caja_r = cil('reloj', (0.8, -0.45, 0.36), 0.29, 0.13, metal, verts=64, rot=giro)
    cara = cil('reloj_cara', (0.784, -0.51, 0.37), 0.25, 0.01, mat('esfera', (0.97, 0.96, 0.92), 0.4, tex=None), verts=64, rot=giro)
    vidrio = mat('vidrio_reloj', (0.9, 0.95, 1.0), 0.02, tex=None)
    vidrio.node_tree.nodes['Principled BSDF'].inputs['Transmission Weight'].default_value = 1.0
    for lado in (-1, 1):
        bpy.ops.mesh.primitive_uv_sphere_add(radius=0.1, location=(0.8 + lado * 0.19, -0.4 + lado * 0.08, 0.66)); o = bpy.context.object
        o.scale = (1, 1, 0.6); o.data.materials.append(laton); bpy.ops.object.shade_smooth()
        cil(f'pata{lado}', (0.8 + lado * 0.16, -0.48 + lado * 0.07, 0.06), 0.03, 0.12, metal)
    a1 = caja('aguja1', (0, 0.07, 0.014), (0.022, 0.15, 0.006), negro, bisel=0)
    a2 = caja('aguja2', (0.05, -0.02, 0.016), (0.11, 0.018, 0.006), negro, bisel=0, rot=(0, 0, math.radians(-25)))
    eje = cil('eje', (0, 0, 0.02), 0.018, 0.01, laton)
    for k in range(12):
        ang = k * math.pi / 6
        m = caja(f'marca{k}', (0.2 * math.cos(ang), 0.2 * math.sin(ang), 0.012), (0.018, 0.05 if k % 3 == 0 else 0.025, 0.004), negro, bisel=0, rot=(0, 0, ang + math.pi / 2))
        pegar(m, cara)
    pegar(a1, cara); pegar(a2, cara); pegar(eje, cara)
else:
    # ---- casita de adobe con techo de teja, puerta de tablones y llave de bronce ----
    caja('muro', (-0.25, 0.25, 0.43), (0.95, 0.8, 0.8), PAL['adobe'], bisel=0.03)
    caja('zocalo', (-0.25, 0.25, 0.08), (0.97, 0.82, 0.13), PAL['azul'], bisel=0.01)
    tejado('techo', (-0.25, 0.25, 0.83), 0.95, 0.8, 0.38, PAL['teja'], vuelo=0.08)
    caja('chimenea', (0.0, 0.42, 1.08), (0.12, 0.12, 0.26), PAL['adobe2'], bisel=0.015)
    caja('puerta_marco', (-0.25, -0.155, 0.34), (0.32, 0.03, 0.52), mat('marco', (0.25, 0.17, 0.11), 0.7, tex='madera'), bisel=0.008)
    for k in range(3):
        caja(f'tablon{k}', (-0.33 + k * 0.08, -0.172, 0.33), (0.075, 0.02, 0.46), PAL['madera'], bisel=0.006)
    bpy.ops.mesh.primitive_uv_sphere_add(radius=0.016, location=(-0.16, -0.19, 0.33)); bpy.context.object.data.materials.append(laton)
    caja('ven_marco', (0.1, -0.155, 0.52), (0.24, 0.03, 0.24), PAL['azul'], bisel=0.008)
    v = mat('vidrio_ven', (0.55, 0.68, 0.76), 0.05, tex=None)
    caja('ven_vidrio', (0.1, -0.168, 0.52), (0.19, 0.01, 0.19), v, bisel=0)
    caja('ven_cruz', (0.1, -0.175, 0.52), (0.015, 0.008, 0.19), PAL['blanco'], bisel=0, tex=None)
    caja('ven_cruz2', (0.1, -0.175, 0.52), (0.19, 0.008, 0.015), PAL['blanco'], bisel=0, tex=None)
    caja('alfeizar', (0.1, -0.18, 0.395), (0.28, 0.06, 0.025), PAL['blanco'], bisel=0.004, tex=None)
    # llave antigua de bronce: argolla, aro con adorno, caña y paletón con dientes
    giro = math.radians(-20)
    llave = bpy.data.objects.new('llave', None); bpy.context.collection.objects.link(llave)
    llave.location = (0.64, -0.36, 0.0); llave.rotation_euler = (0, 0, giro)
    piezas = [
        toro('llave_aro', (0, 0, 0.66), 0.17, 0.045, laton, rot=(math.radians(90), 0, 0)),
        toro('llave_adorno', (0, 0, 0.66), 0.075, 0.02, laton, rot=(math.radians(90), 0, 0)),
        cil('llave_cana', (0, 0, 0.28), 0.04, 0.6, laton, verts=24),
        cil('llave_collar', (0, 0, 0.47), 0.055, 0.04, laton, verts=24),
        caja('diente1', (0.07, 0, 0.07), (0.11, 0.05, 0.07), laton, bisel=0.01, tex=None),
        caja('diente2', (0.05, 0, 0.17), (0.07, 0.05, 0.05), laton, bisel=0.01, tex=None),
        toro('argolla', (-0.1, 0, 0.88), 0.09, 0.012, metal, rot=(math.radians(90), 0, math.radians(30))),
    ]
    for p in piezas:
        pegar(p, llave)

suelo_sombra(z=-0.22)
luces(2.6, 0.75, (50, 8, 32))
# luz de estudio: un panel grande y suave de frente y una luz de contorno detrás
a = bpy.data.lights.new('panel', 'AREA'); a.energy = 300; a.size = 3.0; a.color = (1.0, 0.96, 0.9)
o = bpy.data.objects.new('panel', a); bpy.context.collection.objects.link(o); o.location = (-2.2, -3.0, 3.0)
o.rotation_euler = (math.radians(55), 0, math.radians(-36))
r = bpy.data.lights.new('contorno', 'AREA'); r.energy = 160; r.size = 2.0; r.color = (0.9, 0.95, 1.0)
o = bpy.data.objects.new('contorno', r); bpy.context.collection.objects.link(o); o.location = (2.4, 3.0, 2.6)
o.rotation_euler = (math.radians(-55), 0, math.radians(140))
camara_iso((0.05, -0.05, 0.44), dist=12, alt_grados=24, az_grados=28, escala=3.45)
render(salida, 760, 680, muestras)
