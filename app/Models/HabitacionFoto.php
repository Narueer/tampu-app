<?php

namespace App\Models;

use App\Support\Archivos;
use Illuminate\Database\Eloquent\Model;

class HabitacionFoto extends Model
{
    protected $table = 'habitacion_fotos';

    protected $fillable = ['ruta', 'descripcion', 'orden'];

    public function url(): string
    {
        return Archivos::urlFoto($this->ruta);
    }
}
