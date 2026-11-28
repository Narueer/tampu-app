<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Copias de seguridad de la base de datos.
 * Se guardan comprimidas en la carpeta privada "documentos" (en la nube, el bucket privado de Supabase),
 * así que aunque la base se dañe o alguien borre datos, se pueden recuperar.
 */
class Respaldo
{
    const CARPETA = 'respaldos';
    const GUARDAR = 14;           // se conservan las últimas 14 copias
    // tablas que se pueden volver a generar solas
    const OMITIR = ['cache', 'cache_locks', 'sessions', 'jobs', 'job_batches', 'failed_jobs', 'migrations', 'password_reset_tokens'];

    public static function tablas(): array
    {
        $tablas = Schema::getTableListing(Schema::getCurrentSchemaName(), false);

        return array_values(array_diff(array_unique($tablas), self::OMITIR));
    }

    // crea una copia y devuelve la ruta del archivo
    public static function crear(string $motivo = 'automática'): string
    {
        $copia = ['tampu' => 1, 'creado' => now()->toIso8601String(), 'motivo' => $motivo, 'motor' => DB::getDriverName(), 'tablas' => []];
        foreach (self::tablas() as $tabla) {
            $orden = Schema::hasColumn($tabla, 'id') ? 'id' : null;
            $consulta = DB::table($tabla);
            $copia['tablas'][$tabla] = ($orden ? $consulta->orderBy($orden) : $consulta)->get()->map(fn ($f) => (array) $f)->all();
        }

        $ruta = self::CARPETA . '/tampu-' . now('America/Lima')->format('Y-m-d-His') . '.json.gz';
        Storage::disk('documentos')->put($ruta, gzencode(json_encode($copia, JSON_UNESCAPED_UNICODE), 9));
        self::limpiar();

        return $ruta;
    }

    public static function lista(): array
    {
        $disco = Storage::disk('documentos');

        return collect($disco->files(self::CARPETA))
            ->filter(fn ($f) => str_ends_with($f, '.json.gz'))
            ->sortDesc()->values()
            ->map(fn ($f) => ['ruta' => $f, 'nombre' => basename($f), 'peso' => $disco->size($f)])
            ->all();
    }

    private static function limpiar(): void
    {
        $viejas = array_slice(self::lista(), self::GUARDAR);
        foreach ($viejas as $f) {
            Storage::disk('documentos')->delete($f['ruta']);
        }
    }

    // vuelve a cargar una copia: borra lo actual de esas tablas y pone lo de la copia
    public static function restaurar(string $ruta): array
    {
        $copia = json_decode(gzdecode(Storage::disk('documentos')->get($ruta)), true);
        abort_unless(is_array($copia) && ($copia['tampu'] ?? null) === 1, 422, 'El archivo no es una copia de TAMPU.');

        $existentes = self::tablas();
        $tablas = array_values(array_intersect(self::ordenPorDependencias($existentes), array_keys($copia['tablas'])));
        $resumen = [];

        DB::transaction(function () use ($tablas, $copia, &$resumen) {
            // primero se vacían las tablas hijas, después las padres
            foreach (array_reverse($tablas) as $tabla) {
                DB::table($tabla)->delete();
            }
            foreach ($tablas as $tabla) {
                $filas = $copia['tablas'][$tabla];
                foreach (array_chunk($filas, 200) as $bloque) {
                    DB::table($tabla)->insert($bloque);
                }
                $resumen[$tabla] = count($filas);
            }
        });

        // en Postgres los contadores de id tienen que seguir después del último
        if (DB::getDriverName() === 'pgsql') {
            foreach ($tablas as $tabla) {
                if (Schema::hasColumn($tabla, 'id')) {
                    DB::statement("SELECT setval(pg_get_serial_sequence('\"$tabla\"', 'id'), COALESCE((SELECT MAX(id) FROM \"$tabla\"), 1))");
                }
            }
        }

        return $resumen;
    }

    // padres antes que hijos (usuarios antes que habitaciones, habitaciones antes que fotos…)
    private static function ordenPorDependencias(array $tablas): array
    {
        $depende = [];
        foreach ($tablas as $t) {
            $depende[$t] = collect(Schema::getForeignKeys($t))->pluck('foreign_table')->filter(fn ($p) => $p !== $t && in_array($p, $tablas))->unique()->values()->all();
        }
        $orden = [];
        while (count($orden) < count($tablas)) {
            $avance = false;
            foreach ($tablas as $t) {
                if (! in_array($t, $orden) && ! array_diff($depende[$t], $orden)) {
                    $orden[] = $t;
                    $avance = true;
                }
            }
            if (! $avance) {   // ciclo raro: se agrega el resto como venga
                return array_values(array_unique(array_merge($orden, $tablas)));
            }
        }

        return $orden;
    }
}
