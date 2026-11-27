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

## Texturas (texturas.py)

Madera con vetas, yeso de adobe, piedra, tejas, tela, aguayo y pasto: todo generado con numpy
(ruido periódico, así se repiten sin costuras), cada una con su relieve (normal map).
También dibuja el paisaje de la ventana (Apus y techos de Cusco) y la pantalla de la laptop.
`base.py` le pone a cada color de la paleta su textura y mapea las piezas (la veta sigue el lado largo).
Necesita `pillow` y `numpy`. Las imágenes se guardan en `_texturas/` (se generan solas, no se suben a git).

## Maquetas que se giran (public/modelos)

```
blender -b -P exportar_glb.py -- casa.py casa.glb
blender -b -P exportar_glb.py -- cuarto.py cuarto.glb
```

## Cuarto que se arma con el scroll (portada)

```
blender -b -P cuarto_animado.py -- cuarto-animado.glb
```

Reutiliza `cuarto.py`, anima cada pieza (cae a su sitio con un rebote) y une todo en una sola
animación llamada `armar`. En la página, `public/js/scroll3d.js` no la reproduce sola: el scroll
elige el segundo exacto de la animación y mueve la cámara (de la mochila al cuarto completo).

## Música y videos guía

- `recursos/andina.py`: genera la música andina original (charango, quena y bombo) → `public/audio/andina.mp3`.
- `recursos/videos-guia/`: graba la guía de cada rol con Playwright y la une con su portada y la música (ffmpeg).
