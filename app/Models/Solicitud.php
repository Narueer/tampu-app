<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    protected $table = 'solicitudes';

    protected $fillable = ['habitacion_id', 'usuario_id', 'fecha_visita', 'mensaje', 'estado', 'confirmacion', 'confirmado_at'];

    protected function casts(): array
    {
        return ['fecha_visita' => 'date', 'confirmado_at' => 'datetime'];
    }

    // ya pasó la visita aceptada y el estudiante todavía no dijo si el cuarto coincide
    public function puedeConfirmar(): bool
    {
        return $this->estado === 'aceptada' && ! $this->confirmacion
            && ($this->fecha_visita->isPast() || $this->fecha_visita->isToday());
    }

    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class);
    }

    public function estudiante()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
