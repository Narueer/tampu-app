# TAMPU

Plataforma web para estudiantes que llegan de provincia a Cusco: habitaciones y trabajos de medio tiempo verificados.

Proyecto del curso Programación Web (NRC 39578) - Universidad Continental.
Equipo: Wernher Alvares Rodriguez, César Augusto Juro Huillca, David Achahuanco Bayona y Olenka Valeria Aedo Vargas.

## Tecnologías

Laravel 12 (PHP 8.2), MySQL (XAMPP), Blade, HTML, CSS y JavaScript.
Además: Leaflet + OpenStreetMap (mapa), FullCalendar (calendario), Chart.js (gráficos) y DomPDF (constancias en PDF).
Los mapas, el calendario y los gráficos se cargan desde CDN, así que necesitan internet.

Funciona con MySQL (XAMPP) en local y con PostgreSQL (Supabase) en la nube. Para subirlo a internet ver **DESPLIEGUE.md**.

## Cómo ejecutarlo

1. Encender Apache y MySQL en XAMPP.
2. En phpMyAdmin importar `database/sql/tampu.sql` (crea la base `tampu` con datos de prueba).
3. Abrir una terminal en la carpeta del proyecto y ejecutar:

   ```
   C:\xampp\php\php.exe artisan serve
   ```

4. Entrar a http://127.0.0.1:8000

Si se prefiere crear la base con Laravel: crear la base `tampu` vacía y ejecutar `php artisan migrate --seed`.

## Usuarios de prueba

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | admin@tampu.pe | admin123 |
| Arrendador | rosa@tampu.pe | 123456 |
| Empleador | wayra@tampu.pe | 123456 |
| Estudiante (con horario) | luis@tampu.pe | 123456 |
| Estudiante (puede dejar reseña) | ana@tampu.pe | 123456 |
| Estudiante | rocio@tampu.pe, kevin@tampu.pe | 123456 |
| Arrendador sin verificar | pedro@tampu.pe | 123456 |
| Arrendador con publicación reportada | mario@tampu.pe | 123456 |

## Funciones (entrega final)

Primera parte: registro e inicio de sesión por rol, verificación de arrendadores y empleadores por el administrador,
CRUD de habitaciones con foto, búsqueda con filtros, solicitud de visitas, CRUD de ofertas de trabajo,
horario de clases y filtro de trabajos compatibles, panel del administrador.

Segunda parte:
- Postulaciones a trabajos (la empresa acepta o rechaza).
- Reseñas con estrellas (solo quien ya visitó la habitación).
- Reportes de publicaciones y suspensión de cuentas.
- Favoritos.
- Mapa de habitaciones y selección de ubicación al publicar.
- Gráficos en el panel del administrador.
- Calendario: clases, turnos de trabajo y visitas.
- Asistente virtual (por palabras clave, sin API externa).
- Notificaciones y mensajes entre usuarios.
- Perfil con foto y cambio de contraseña.
- Recuperar contraseña y bloqueo tras 5 intentos fallidos.
- Constancia en PDF de visita o postulación aceptada.
- Página de contacto: los mensajes llegan al panel del administrador.
- Diseño adaptable a celular, tablet y computadora.

Recuperar contraseña: en local no hay servidor de correo, así que el correo se guarda en `storage/logs/laravel.log`
y el enlace también aparece en pantalla. En la nube se puede configurar Gmail (ver DESPLIEGUE.md).

El correo de contacto que se muestra en la página se cambia en el archivo `.env`, en `CONTACTO_CORREO`.

## Créditos y recursos externos

| Recurso | Uso | Licencia |
|---|---|---|
| Laravel 12 | Framework MVC | MIT |
| barryvdh/laravel-dompdf | Constancias en PDF | MIT |
| Leaflet 1.9.4 | Mapas | BSD-2 |
| OpenStreetMap | Mapa base | ODbL |
| FullCalendar 6.1.15 | Calendario | MIT |
| Chart.js 4.4.4 | Gráficos | MIT |
| league/flysystem-aws-s3-v3 | Subir archivos a Supabase Storage | MIT |
| Bootstrap 5.3.3 (jsDelivr) | Carruseles, ventana modal y grilla | MIT |
| Bootstrap Icons 1.11.3 (jsDelivr) | Íconos | MIT |
| qrcodejs 1.0.0 (cdnjs) | Código QR de la verificación en dos pasos | MIT |
| Poppins (Google Fonts) | Tipografía | SIL Open Font License 1.1 |
| Unsplash | Fotos de ejemplo de habitaciones (`public/img/habitaciones`) | Licencia Unsplash |
| [Plaza de Armas](https://commons.wikimedia.org/wiki/File:Cusco_-_Plaza_de_Armas_-_panoramio.jpg), Pavel Špindler (`img/cusco/plaza.jpg`) | Portada | CC BY 3.0 |
| [Catedral del Cusco](https://commons.wikimedia.org/wiki/File:Katedraal_langs_Plaza_de_Armas,_Cusco,_a.jpg), Bernard Brönn (`img/cusco/catedral.jpg`) | Cabeceras | CC BY-SA 4.0 |
| [Plaza San Blas](https://commons.wikimedia.org/wiki/File:Plaza_San_Blas,_Cusco,_Peru_2019-10-09.jpg), Alexey Komarov (`img/cusco/san-blas.jpg`) | Portada y Trabajos | CC BY-SA 4.0 |
| [Cuesta de San Blas](https://commons.wikimedia.org/wiki/File:Cuesta_de_San_Blas_5.jpg), Pedro Szekely (`img/cusco/cuesta-san-blas.jpg`) | Login y collage | CC BY-SA 2.0 |

| Fotos de la galería (`img/habitaciones/galeria`) | Dormitorios, baños y cocinas de ejemplo | CC0, CC BY 4.0 y CC BY-SA 4.0 (autores en `CREDITOS.md` de esa carpeta) |
| OpenStreetMap / Nominatim | Ubicación de universidades, calles y lugares de los datos de prueba | ODbL |

Las fotos de Wikimedia se redujeron de tamaño para la web; no se les cambió nada más.

## Problemas comunes

- "could not find driver": en `C:\xampp\php\php.ini` quitar el `;` de `extension=pdo_mysql` y `extension=fileinfo`.
- "Access denied for user root": revisar `DB_PASSWORD` en el archivo `.env`.
