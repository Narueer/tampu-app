<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('procedencia');
            $table->boolean('suspendido')->default(false)->after('verificacion');
        });

        Schema::table('habitaciones', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable()->after('cerca_de');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
        });

        Schema::create('postulaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trabajo_id')->constrained('trabajos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('mensaje', 300)->nullable();
            $table->enum('estado', ['pendiente', 'aceptada', 'rechazada'])->default('pendiente');
            $table->timestamps();
            $table->unique(['trabajo_id', 'usuario_id']);
        });

        Schema::create('resenas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habitacion_id')->constrained('habitaciones')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->unsignedTinyInteger('estrellas');
            $table->string('comentario', 500);
            $table->timestamps();
            $table->unique(['habitacion_id', 'usuario_id']);
        });

        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('habitacion_id')->nullable()->constrained('habitaciones')->cascadeOnDelete();
            $table->foreignId('trabajo_id')->nullable()->constrained('trabajos')->cascadeOnDelete();
            $table->string('motivo', 60);
            $table->string('detalle', 500)->nullable();
            $table->enum('estado', ['pendiente', 'descartado', 'sancionado'])->default('pendiente');
            $table->timestamps();
        });

        Schema::create('favoritos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('habitacion_id')->constrained('habitaciones')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['usuario_id', 'habitacion_id']);
        });

        Schema::create('conversaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_a_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('usuario_b_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('habitacion_id')->nullable()->constrained('habitaciones')->nullOnDelete();
            $table->foreignId('trabajo_id')->nullable()->constrained('trabajos')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversacion_id')->constrained('conversaciones')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->text('texto');
            $table->boolean('leido')->default(false);
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('mensajes');
        Schema::dropIfExists('conversaciones');
        Schema::dropIfExists('favoritos');
        Schema::dropIfExists('reportes');
        Schema::dropIfExists('resenas');
        Schema::dropIfExists('postulaciones');

        Schema::table('habitaciones', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });

        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn(['foto', 'suspendido']);
        });
    }
};
