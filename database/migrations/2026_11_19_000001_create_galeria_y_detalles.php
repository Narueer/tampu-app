<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// varias fotos por habitacion, mas datos del cuarto y ubicacion de los trabajos
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('habitacion_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habitacion_id')->constrained('habitaciones')->cascadeOnDelete();
            $table->string('ruta');
            $table->string('descripcion', 80)->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::table('habitaciones', function (Blueprint $table) {
            $table->unsignedSmallInteger('metros')->nullable();
            $table->unsignedTinyInteger('piso')->nullable();
            $table->string('bano', 12)->nullable();
            $table->decimal('garantia', 7, 2)->nullable();
        });

        Schema::table('trabajos', function (Blueprint $table) {
            $table->string('direccion', 150)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->text('requisitos')->nullable();
            $table->unsignedTinyInteger('vacantes')->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('trabajos', function (Blueprint $table) {
            $table->dropColumn(['direccion', 'lat', 'lng', 'requisitos', 'vacantes']);
        });
        Schema::table('habitaciones', function (Blueprint $table) {
            $table->dropColumn(['metros', 'piso', 'bano', 'garantia']);
        });
        Schema::dropIfExists('habitacion_fotos');
    }
};
