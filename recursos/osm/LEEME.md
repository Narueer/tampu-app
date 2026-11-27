# Datos de OpenStreetMap (resources/data/cusco-osm.json)

Paraderos, 37 líneas de transporte urbano (RTU/RTI), comisarías y centros de salud de Cusco.
Licencia ODbL: hay que mencionar "© colaboradores de OpenStreetMap" (ya sale en la página del cuarto).

Para actualizarlos, en https://overpass-turbo.eu ejecuta (zona de Cusco):

```
[out:json][timeout:90];
( node["highway"="bus_stop"](-13.60,-72.02,-13.48,-71.84);
  nwr["amenity"="police"](-13.60,-72.02,-13.48,-71.84);
  nwr["amenity"~"^(hospital|clinic|doctors)$"](-13.60,-72.02,-13.48,-71.84); );
out center tags;
relation["route"="bus"]["ref"~"^RT[UI]-"](-13.60,-72.02,-13.48,-71.84);
out geom;
```

y convierte el resultado al formato de cusco-osm.json (lineas: ref, nombre, tramos [[lat,lng],...]; lugares: tipo, nombre, lat, lng).
