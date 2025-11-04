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
        Schema::create('curso_asistencia', function (Blueprint $table) {
            $table->id('idAsistencia');
            $table->unsignedBigInteger('idCursoEstudiante');
            $table->unsignedBigInteger('idCursoHorario');
            $table->string('estado', 20);
            $table->string('justificacion', 255)->nullable();
            $table->date('fechaRegistro')->useCurrent();

            $table->foreign('idCursoEstudiante')->references('idCursoEstudiante')->on('curso_estudiantes')->onDelete('cascade');
            $table->foreign('idCursoHorario')->references('idCursoHorario')->on('curso_horario')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_asistencia');
    }
};
