<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('email', 100)->unique();
            $table->char('dni', 8)->unique();
            $table->string('celular', 9)->nullable();
            $table->string('procedencia', 60)->nullable();
            $table->enum('rol', ['estudiante', 'arrendador', 'empleador', 'admin'])->default('estudiante');
            $table->enum('verificacion', ['no_aplica', 'pendiente', 'verificado', 'rechazado'])->default('no_aplica');
            $table->string('documento')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
