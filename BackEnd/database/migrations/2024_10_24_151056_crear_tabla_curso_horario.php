<?php

// database/migrations/2024_11_06_142751_crear_tabla_horariosCurso.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaCursoHorario extends Migration
{
    public function up()
    {
        Schema::create('curso_horario', function (Blueprint $table) {
            $table->id('idCursoHorario');
            $table->unsignedBigInteger('idCursoDocente');
            $table->string('aula', 10);
            $table->string('dia', 15);
            $table->time('hora_ini');
            $table->time('hora_fin');
            $table->integer('vacantes');
            $table->integer('vacantes_disponibles');

            $table->foreign('idCursoDocente')->references('idCursoDocente')->on('curso_docentes')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('curso_horario');
    }
}