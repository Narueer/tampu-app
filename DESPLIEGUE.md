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

### Entrar con el correo de la universidad (Google)

Sirve si el correo de la universidad se abre en Gmail. Con esto aparece el botón **Entrar con Google** y los estudiantes que usan su correo `@continental.edu.pe` quedan como **estudiante verificado**.

1. Entrar a https://console.cloud.google.com con cualquier cuenta de Google y crear un proyecto llamado `tampu`.
2. **APIs y servicios → Pantalla de consentimiento de OAuth**: tipo **Externo**, nombre `TAMPU`, el correo del equipo y guardar. En **Público** tocar **Publicar app** (los permisos que pedimos, correo y nombre, no necesitan revisión de Google).
3. **APIs y servicios → Credenciales → Crear credenciales → ID de cliente de OAuth**:
   - Tipo: **Aplicación web**.
   - URI de redireccionamiento autorizados: `https://tampu-app.onrender.com/externo/google/vuelta` (y, para probar en la PC, `http://127.0.0.1:8000/externo/google/vuelta`).
4. Copiar el **ID de cliente** y el **Secreto** y ponerlos en Render como `GOOGLE_CLIENT_ID` y `GOOGLE_CLIENT_SECRET`. No se escriben en el código ni se mandan por chat.

### Entrar con Microsoft

Solo si el correo de la universidad es de Microsoft 365 (se abre en Outlook). En https://portal.azure.com → **Registros de aplicaciones → Nuevo registro**: nombre `TAMPU`, cuentas de "cualquier directorio organizativo" y URI de redirección **Web** `https://tampu-app.onrender.com/externo/microsoft/vuelta`. Luego, en **Certificados y secretos**, crear un secreto. En Render van `MICROSOFT_CLIENT_ID` (el "Id. de aplicación") y `MICROSOFT_CLIENT_SECRET` (el "Valor" del secreto). Solo entran cuentas del dominio de la universidad. Algunas universidades piden que el área de TI apruebe la aplicación; si sale ese aviso, se usa Google o el correo y contraseña.

### Verificación en dos pasos

No necesita configurar nada: cada usuario la activa en **Mi perfil** con Google Authenticator o Microsoft Authenticator. El secreto se guarda cifrado con la `APP_KEY`, así que **si se cambia la `APP_KEY`, hay que desactivar y volver a activar** la verificación (o entrar con un código de respaldo).

**Correos de verdad** (recuperar contraseña, código para confirmar el correo y copia de los mensajes de contacto). Cuando el correo está configurado, TAMPU pide confirmar el correo con un código antes de publicar, postular o escribir. Con una cuenta de Gmail del equipo: activar la verificación en dos pasos, crear una **contraseña de aplicación** y agregar en Render las variables `MAIL_*` que están comentadas en `.env.nube.ejemplo`. Sin esto, los correos solo quedan registrados en los logs de Render.

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
