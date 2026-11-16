<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resena extends Model
{
    protected $table = 'resenas';

    protected $fillable = ['habitacion_id', 'usuario_id', 'estrellas', 'comentario'];

    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class);
    }

    public function estudiante()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
