<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('habitaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('titulo', 120);
            $table->text('descripcion');
            $table->string('distrito', 40);
            $table->string('direccion', 150);
            $table->string('cerca_de', 80)->nullable();
            $table->decimal('precio', 7, 2);
            $table->enum('tipo', ['individual', 'compartida', 'minidepartamento']);
            $table->string('servicios', 200)->nullable();
            $table->string('foto')->nullable();
            $table->enum('estado', ['disponible', 'ocupada'])->default('disponible');
            $table->timestamps();
        });

        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habitacion_id')->constrained('habitaciones')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->date('fecha_visita');
            $table->string('mensaje', 300)->nullable();
            $table->enum('estado', ['pendiente', 'aceptada', 'rechazada'])->default('pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
        Schema::dropIfExists('habitaciones');
    }
};
