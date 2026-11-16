<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Postulacion extends Model
{
    protected $table = 'postulaciones';

    protected $fillable = ['trabajo_id', 'usuario_id', 'mensaje', 'estado'];

    public function trabajo()
    {
        return $this->belongsTo(Trabajo::class);
    }

    public function estudiante()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
