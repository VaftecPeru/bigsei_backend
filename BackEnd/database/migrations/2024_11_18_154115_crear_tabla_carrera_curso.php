<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaCarreraCurso extends Migration
{
    public function up()
    {
        Schema::create('carrera_curso', function (Blueprint $table) {
            $table->id('idCarreraCurso'); // Llave primaria
            $table->unsignedBigInteger('idCarrera'); // Llave foránea a carrera
            $table->unsignedBigInteger('idCurso'); // Llave foránea a curso
            $table->string('tipoCurso', 100); // Tipo de curso

            // Llaves foráneas
            $table->foreign('idCarrera')->references('idCarrera')->on('carrera')->onDelete('cascade');
            $table->foreign('idCurso')->references('idCurso')->on('curso')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('carrera_curso');
    }
}
