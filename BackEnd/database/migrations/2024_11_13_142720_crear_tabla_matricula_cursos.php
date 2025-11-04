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
        Schema::create('matricula_cursos', function (Blueprint $table) {
            $table->id('idMatriculaCurso');
            $table->unsignedBigInteger('idMatricula');
            $table->unsignedBigInteger('idCursoHorario');

            $table->foreign('idMatricula')->references('idMatricula')->on('matricula')->onDelete('cascade');
            $table->foreign('idCursoHorario')->references('idCursoHorario')->on('curso_horario')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matricula_cursos');
    }
};
