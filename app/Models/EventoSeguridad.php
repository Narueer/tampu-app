<?php

namespace App\Models;

use App\Notifications\Aviso;
use Illuminate\Database\Eloquent\Model;

class EventoSeguridad extends Model
{
    protected $table = 'eventos_seguridad';

    const UPDATED_AT = null;

    protected $fillable = ['usuario_id', 'email', 'tipo', 'detalle', 'ip', 'navegador'];

    // nombre que se muestra y si es algo de cuidado
    const TIPOS = [
        'login' => ['Inicio de sesión', false],
        'login_fallido' => ['Contraseña incorrecta', true],
        'bloqueo' => ['Login bloqueado por intentos', true],
        'login_suspendido' => ['Intento con cuenta suspendida', true],
        'logout' => ['Cierre de sesión', false],
        'registro' => ['Cuenta nueva', false],
        'clave_cambiada' => ['Cambio de contraseña', false],
        'clave_enlace' => ['Pidió recuperar contraseña', false],
        'clave_recuperada' => ['Contraseña recuperada', false],
        'suspension' => ['Cuenta suspendida', true],
        'reactivacion' => ['Cuenta reactivada', false],
        'verificacion' => ['Verificación de documento', false],
        'acceso_denegado' => ['Acceso denegado (403)', true],
        'correo_confirmado' => ['Correo confirmado', false],
        'codigo_fallido' => ['Código de correo incorrecto', true],
        'institucional' => ['Correo institucional vinculado', false],
        'externo_rechazado' => ['Ingreso con Google/Microsoft rechazado', true],
        'dos_pasos_activado' => ['Activó la verificación en dos pasos', false],
        'dos_pasos_desactivado' => ['Desactivó la verificación en dos pasos', true],
        'dos_pasos_fallido' => ['Código de dos pasos incorrecto', true],
        'codigo_respaldo' => ['Entró con un código de respaldo', true],
        'respaldo' => ['Copia de seguridad creada', false],
        'respaldo_descargado' => ['Descargó una copia de seguridad', true],
        'respaldo_restaurado' => ['Restauró una copia de seguridad', true],
        'usuario_eliminado' => ['Cuenta eliminada por el admin', true],
        'documento_anulado' => ['Constancia anulada', true],
        'documento_reactivado' => ['Constancia reactivada', false],
        'documento_eliminado' => ['Constancia eliminada', true],
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function nombreTipo(): string
    {
        return self::TIPOS[$this->tipo][0] ?? $this->tipo;
    }

    public function esAlerta(): bool
    {
        return self::TIPOS[$this->tipo][1] ?? false;
    }

    // guarda el evento con la ip y el navegador de quien hace la peticion
    public static function registrar(string $tipo, ?string $detalle = null, ?Usuario $usuario = null, ?string $email = null): self
    {
        $usuario ??= auth()->user();

        return self::create([
            'usuario_id' => $usuario?->id,
            'email' => $email ?? $usuario?->email,
            'tipo' => $tipo,
            'detalle' => $detalle ? mb_substr($detalle, 0, 255) : null,
            'ip' => request()->ip(),
            'navegador' => mb_substr((string) request()->userAgent(), 0, 255),
        ]);
    }

    // avisa a los administradores de algo raro
    public static function alertarAdmins(string $texto): void
    {
        Usuario::where('rol', 'admin')->get()
            ->each(fn ($admin) => $admin->notify(new Aviso($texto, route('admin.seguridad'), 'shield-exclamation')));

        // también al correo del equipo, como mucho uno cada 10 minutos por el mismo motivo
        if (\Illuminate\Support\Facades\Cache::add('alerta-correo:' . md5($texto), 1, now()->addMinutes(10))) {
            \App\Support\CorreoAdmin::enviar('Alerta de seguridad', 'Seguridad', [
                'Qué pasó' => $texto,
                'IP' => request()->ip(),
                'Cuándo' => now('America/Lima')->format('d/m/Y H:i'),
            ], 'Revisa la bitácora de seguridad por si se repite.', route('admin.seguridad'), 'Ver la bitácora');
        }
    }
}
