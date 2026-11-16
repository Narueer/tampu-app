<?php

namespace App\Models;

use App\Support\Archivos;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre', 'email', 'dni', 'celular', 'procedencia', 'foto',
        'rol', 'verificacion', 'suspendido', 'documento', 'password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'suspendido' => 'boolean'];
    }

    public function habitaciones()
    {
        return $this->hasMany(Habitacion::class);
    }

    public function trabajos()
    {
        return $this->hasMany(Trabajo::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class);
    }

    public function postulaciones()
    {
        return $this->hasMany(Postulacion::class);
    }

    public function favoritos()
    {
        return $this->belongsToMany(Habitacion::class, 'favoritos')->withTimestamps();
    }

    public function esVerificado(): bool
    {
        return $this->verificacion === 'verificado' && ! $this->suspendido;
    }

    public function fotoUrl(): string
    {
        return Archivos::urlFoto($this->foto);
    }

    public function iniciales(): string
    {
        $partes = explode(' ', trim($this->nombre));

        return mb_strtoupper(mb_substr($partes[0], 0, 1) . mb_substr($partes[1] ?? '', 0, 1));
    }

    public function mensajesSinLeer(): int
    {
        return Mensaje::where('usuario_id', '!=', $this->id)
            ->where('leido', false)
            ->whereHas('conversacion', fn ($q) => $q->where('usuario_a_id', $this->id)->orWhere('usuario_b_id', $this->id))
            ->count();
    }
}
