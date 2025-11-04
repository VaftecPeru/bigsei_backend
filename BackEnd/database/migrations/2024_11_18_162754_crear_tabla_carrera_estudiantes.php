<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaCarreraEstudiantes extends Migration
{
    public function up()
    {
        Schema::create('carrera_estudiantes', function (Blueprint $table) {
            $table->id('idCarreraEstudiante'); // Clave primaria
            $table->unsignedBigInteger('idCarrera'); // Clave foránea hacia carrera
            $table->unsignedBigInteger('idEstudiante'); // Clave foránea hacia usuario

            // Llaves foráneas
            $table->foreign('idCarrera')->references('idCarrera')->on('carrera')->onDelete('cascade');
            $table->foreign('idEstudiante')->references('idUsuario')->on('usuarios')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('carrera_estudiantes');
    }
}
