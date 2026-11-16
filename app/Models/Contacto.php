<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contacto extends Model
{
    protected $table = 'contactos';

    protected $fillable = ['nombre', 'email', 'asunto', 'mensaje', 'leido'];

    const ASUNTOS = ['Consulta general', 'Problema con mi cuenta', 'Quiero publicar', 'Denunciar una estafa', 'Sugerencia'];

    protected function casts(): array
    {
        return ['leido' => 'boolean'];
    }
}
