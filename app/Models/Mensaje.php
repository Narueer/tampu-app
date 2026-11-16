<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model
{
    protected $table = 'mensajes';

    protected $fillable = ['conversacion_id', 'usuario_id', 'texto', 'leido'];

    protected function casts(): array
    {
        return ['leido' => 'boolean'];
    }

    public function conversacion()
    {
        return $this->belongsTo(Conversacion::class);
    }

    public function autor()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
