<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    protected $table = 'solicitudes';

    protected $fillable = ['habitacion_id', 'usuario_id', 'fecha_visita', 'mensaje', 'estado'];

    protected function casts(): array
    {
        return ['fecha_visita' => 'date'];
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
