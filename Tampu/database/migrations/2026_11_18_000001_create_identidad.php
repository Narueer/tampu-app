<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// correo confirmado, correo institucional y verificacion en dos pasos
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->timestamp('email_verificado_at')->nullable();
            $table->string('codigo_correo')->nullable();
            $table->timestamp('codigo_correo_vence')->nullable();
            $table->string('correo_institucional', 100)->nullable()->unique();
            $table->boolean('dos_pasos_activo')->default(false);
            $table->text('dos_pasos_secreto')->nullable();
            $table->text('dos_pasos_codigos')->nullable();
            $table->unsignedBigInteger('dos_pasos_ultimo')->nullable();
        });

        // las cuentas que ya existian se dan por confirmadas
        DB::table('usuarios')->update(['email_verificado_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropUnique(['correo_institucional']);
            $table->dropColumn([
                'email_verificado_at', 'codigo_correo', 'codigo_correo_vence', 'correo_institucional',
                'dos_pasos_activo', 'dos_pasos_secreto', 'dos_pasos_codigos', 'dos_pasos_ultimo',
            ]);
        });
    }
};
