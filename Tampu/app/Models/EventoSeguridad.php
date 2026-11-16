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
    }
}
