<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversacion extends Model
{
    protected $table = 'conversaciones';

    protected $fillable = ['usuario_a_id', 'usuario_b_id', 'habitacion_id', 'trabajo_id'];

    public function usuarioA()
    {
        return $this->belongsTo(Usuario::class, 'usuario_a_id');
    }

    public function usuarioB()
    {
        return $this->belongsTo(Usuario::class, 'usuario_b_id');
    }

    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class);
    }

    public function trabajo()
    {
        return $this->belongsTo(Trabajo::class);
    }

    public function mensajes()
    {
        return $this->hasMany(Mensaje::class);
    }

    public function ultimo()
    {
        return $this->hasOne(Mensaje::class)->latestOfMany();
    }

    public function otro(Usuario $yo): Usuario
    {
        return $this->usuario_a_id === $yo->id ? $this->usuarioB : $this->usuarioA;
    }

    public function participa(Usuario $u): bool
    {
        return in_array($u->id, [$this->usuario_a_id, $this->usuario_b_id]);
    }

    public function scopeDe($query, Usuario $u)
    {
        return $query->where(fn ($q) => $q->where('usuario_a_id', $u->id)->orWhere('usuario_b_id', $u->id));
    }

    public function tema(): string
    {
        return $this->habitacion?->titulo ?? $this->trabajo?->titulo ?? 'Asesoría TAMPU';
    }
}
