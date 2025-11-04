<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('curso_horario_estudiantes', function (Blueprint $table) {
            $table->id('idCurHorEstudiante');
            $table->unsignedBigInteger('idCursoHorario');
            $table->unsignedBigInteger('idUsuario');

            $table->foreign('idCursoHorario')->references('idCursoHorario')->on('curso_horario')->onDelete('cascade');
            $table->foreign('idUsuario')->references('idUsuario')->on('usuarios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_horario_estudiantes');
    }
};
