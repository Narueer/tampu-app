<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// guarda y muestra las fotos, sea en local o en Supabase
class Archivos
{
    public static function guardarFoto(UploadedFile $archivo, string $carpeta): string
    {
        // putFile le pone un nombre aleatorio
        return Storage::disk('fotos')->putFile($carpeta, $archivo);
    }

    public static function urlFoto(?string $ruta): string
    {
        if (! $ruta) {
            return '';
        }

        // fotos de ejemplo que vienen con el proyecto
        if (str_starts_with($ruta, 'img/')) {
            return asset($ruta);
        }

        if (config('filesystems.disks.fotos.driver') === 'local') {
            return asset('uploads/' . $ruta);
        }

        return Storage::disk('fotos')->url($ruta);
    }

    public static function borrarFoto(?string $ruta): void
    {
        if ($ruta && ! str_starts_with($ruta, 'img/')) {
            Storage::disk('fotos')->delete($ruta);
        }
    }
}
