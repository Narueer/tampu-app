# Ilustraciones 3D de TAMPU (Blender)

Las imágenes de `public/img/3d/` se generaron con estos scripts de Python para Blender
(todo modelado con código: casa de adobe con base de piedra inca, cuarto de estudiante y los 3 pasos).

Requisitos: Blender 4 o 5 (o el módulo `bpy`).

```
blender -b -P casa.py -- casa.png 64
blender -b -P cuarto.py -- cuarto.png 64
blender -b -P pasos.py -- registro paso-registro.png 64     # también: horario, visita
```

El último número son las muestras del render (más = más limpio y más lento).
Después se recortan y se guardan como .webp para que pesen poco.
