# exporta una escena (casa.py o cuarto.py) a .glb para verla en 3D en la página
import sys, runpy, bpy
escena, salida = sys.argv[-2], sys.argv[-1]
import base
base.render = lambda *a, **k: base.uv_todo()   # no renderizar: solo armar la escena y mapear las texturas
sys.argv = [escena, '/dev/null', '1']
runpy.run_path(escena, run_name='__main__')
bpy.ops.object.select_all(action='DESELECT')
for o in bpy.data.objects:
    if o.type == 'MESH' and o.name != 'suelo':
        o.select_set(True)
bpy.ops.export_scene.gltf(filepath=salida, export_format='GLB', use_selection=True, export_apply=True,
                          export_draco_mesh_compression_enable=(sys.argv[-3] != "sin-draco") if len(sys.argv) > 3 else True, export_draco_mesh_compression_level=7,
                          export_yup=True, export_cameras=False, export_lights=False,
                          export_image_format='JPEG', export_image_quality=82)
print('GLB listo', salida)
