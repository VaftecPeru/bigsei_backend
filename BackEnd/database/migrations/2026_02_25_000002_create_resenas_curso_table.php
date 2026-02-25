<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Crea la tabla resenas_curso para que el estudiante deje reseñas de cursos.
     */
    public function up(): void
    {
        Schema::create('resenas_curso', function (Blueprint $table) {
            $table->id('idResena');
            $table->unsignedBigInteger('idUsuario')->comment('Estudiante que escribe la reseña');
            $table->unsignedBigInteger('idPeriodoCurso')->comment('Curso reseñado');
            $table->tinyInteger('calificacion')->unsigned()->comment('Calificación de 1 a 5 estrellas');
            $table->text('comentario')->nullable();
            $table->timestamps();

            // Solo FK al usuario (la tabla de cursos puede variar según el proyecto)
            $table->foreign('idUsuario')->references('idUsuario')->on('usuario')->onDelete('cascade');

            // Un estudiante solo puede reseñar un curso una vez
            $table->unique(['idUsuario', 'idPeriodoCurso']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resenas_curso');
    }
};
