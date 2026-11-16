# Subir TAMPU a la nube

La aplicación corre en **Render** (servidor con PHP) y la base de datos, las fotos y los documentos quedan en **Supabase**, igual que en Delilac. Los dos tienen plan gratis y la dirección queda limpia, por ejemplo `https://tampu.onrender.com`, sin marca de agua en la página.

¿Por qué no Cloudflare? Cloudflare Pages solo sirve páginas estáticas o JavaScript; no puede ejecutar PHP ni Laravel. Si más adelante compran un dominio (por ejemplo `tampu.pe`), sí pueden usar Cloudflare para el dominio y apuntarlo a Render (paso 6).

Lo que hay que saber del plan gratis de Render: si nadie entra en 15 minutos el servidor se duerme, y la primera visita después tarda unos 50 segundos en abrir. Para la sustentación conviene abrir la página un minuto antes.

---

## 1. Subir el código a GitHub

1. Descomprimir el proyecto y subir la carpeta a un repositorio (puede ser el que ya tienen, `Narueer/Tampu`).
2. La carpeta `vendor` y el archivo `.env` no se suben (están en `.gitignore`); Render instala las dependencias solo.

## 2. Crear el proyecto en Supabase

1. Entrar a https://supabase.com, crear un proyecto llamado `tampu` y anotar la **contraseña de la base de datos**.
2. Región: la más cercana que ofrezca (por ejemplo São Paulo o East US).

**Datos de conexión.** Arriba, botón **Connect** → pestaña **Connection string** → elegir **Session pooler** (no "Direct connection", porque Render no se conecta por IPv6). De ahí se copian:

| Variable | Dónde está |
|---|---|
| `DB_HOST` | host, algo como `aws-0-sa-east-1.pooler.supabase.com` |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | `postgres` |
| `DB_USERNAME` | `postgres.xxxxxxxx` (incluye la referencia del proyecto) |
| `DB_PASSWORD` | la contraseña del paso 1 |

**Almacenamiento de fotos.** Menú **Storage**:

1. **New bucket** → nombre `fotos` → marcar **Public bucket**.
2. **New bucket** → nombre `documentos` → dejarlo **privado** (ahí van los DNI y RUC).
3. En **Storage → Settings → S3 Connection**: activar la conexión S3, anotar la **Region** y crear un **Access key** (se obtienen `Access key ID` y `Secret access key`).
4. La URL del proyecto está en **Project Settings → API → Project URL** (`https://xxxxxxxx.supabase.co`).

No hace falta crear las tablas a mano: se crean solas al desplegar. Si de todas formas quieren verlas antes, pueden pegar `database/sql/tampu_postgres.sql` en el **SQL Editor** de Supabase.

## 3. Generar la clave de la aplicación

En la carpeta del proyecto, con XAMPP:

```
C:\xampp\php\php.exe artisan key:generate --show
```

Copiar el resultado completo (empieza con `base64:`).

## 4. Crear el servicio en Render

1. Entrar a https://render.com con la cuenta de GitHub.
2. **New → Blueprint** y elegir el repositorio. Render lee el archivo `render.yaml` y crea el servicio `tampu` con Docker.
   (Otra forma: **New → Web Service**, elegir el repositorio, Runtime **Docker**, plan **Free**.)
3. Llenar las variables que quedan vacías (en **Environment**). La lista completa con ejemplos está en `.env.nube.ejemplo`:

| Variable | Valor |
|---|---|
| `APP_KEY` | la clave del paso 3 |
| `APP_URL` | la dirección que da Render, por ejemplo `https://tampu.onrender.com` |
| `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD` | los del paso 2 |
| `SUPABASE_URL` | `https://xxxxxxxx.supabase.co` |
| `SUPABASE_REGION` | la región de S3 Connection |
| `SUPABASE_S3_KEY`, `SUPABASE_S3_SECRET` | el Access key del paso 2 |
| `CONTACTO_CORREO` | el correo del equipo que se mostrará en Contacto |

4. **Deploy**. La primera vez tarda unos minutos. Al arrancar, el script `docker/iniciar.sh` crea las tablas y carga los datos de prueba (solo si la base está vacía).

## 5. Probar

- Abrir la dirección de Render y entrar con `admin@tampu.pe` / `admin123`.
- Publicar una habitación con foto: la foto debe aparecer en Supabase → Storage → `fotos`.
- Enviar un mensaje desde Contacto: aparece en el panel del admin, pestaña **Contacto**.

**Antes de mostrarlo al público** conviene cambiar la contraseña del administrador desde **Mi perfil**, porque la de prueba está en la guía.

## 6. Opcional

**Correos de verdad** (recuperar contraseña y copia de los mensajes de contacto). Con una cuenta de Gmail del equipo: activar la verificación en dos pasos, crear una **contraseña de aplicación** y agregar en Render las variables `MAIL_*` que están comentadas en `.env.nube.ejemplo`. Sin esto, los correos solo quedan registrados en los logs de Render.

**Dominio propio con Cloudflare.** Si compran un dominio: en Render → Settings → Custom Domains agregar el dominio; en Cloudflare → DNS crear el registro CNAME que indica Render. Cambiar `APP_URL` al nuevo dominio.

**Railway en lugar de Render.** El mismo `Dockerfile` funciona en Railway: New Project → Deploy from GitHub y copiar las mismas variables. Railway no se duerme, pero el plan gratis es solo un crédito de prueba.

---

## Archivos que agregamos para la nube

| Archivo | Para qué sirve |
|---|---|
| `Dockerfile` | Arma el servidor: PHP 8.2, Apache y las extensiones para PostgreSQL |
| `docker/iniciar.sh` | Al arrancar: usa el puerto que da Render, crea las tablas y carga los datos |
| `render.yaml` | Configuración del servicio para Render |
| `.env.nube.ejemplo` | Lista de variables para la nube |
| `database/sql/tampu_postgres.sql` | La base en formato PostgreSQL, por si se quiere cargar a mano en Supabase |
| `config/filesystems.php` | Discos `fotos` y `documentos`: en local usan carpetas, en la nube Supabase Storage |
