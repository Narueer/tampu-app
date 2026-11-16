<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrabajoHorario extends Model
{
    protected $table = 'trabajo_horarios';

    public $timestamps = false;

    protected $fillable = ['dia', 'turno'];
}
