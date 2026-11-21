<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// el tipo de vivienda pasa de una lista fija a texto, para sumar
// "depa con roomies" y "casa de estudiantes" (la lista esta en Habitacion::TIPOS)
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            // en PostgreSQL el enum de Laravel es una restriccion CHECK
            DB::statement('ALTER TABLE habitaciones DROP CONSTRAINT IF EXISTS habitaciones_tipo_check');
            DB::statement('ALTER TABLE habitaciones ALTER COLUMN tipo TYPE varchar(30)');
        } else {
            Schema::table('habitaciones', function (Blueprint $table) {
                $table->string('tipo', 30)->change();
            });
        }
    }

    public function down(): void
    {
        // los tipos nuevos se pasan a "compartida" antes de volver a la lista anterior
        DB::table('habitaciones')->whereIn('tipo', ['roomies', 'residencia'])->update(['tipo' => 'compartida']);
        if (DB::getDriverName() !== 'pgsql') {
            Schema::table('habitaciones', function (Blueprint $table) {
                $table->enum('tipo', ['individual', 'compartida', 'minidepartamento'])->change();
            });
        }
    }
};
