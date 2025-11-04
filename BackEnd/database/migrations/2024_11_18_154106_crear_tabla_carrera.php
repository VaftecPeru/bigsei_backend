<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaCarrera extends Migration
{
    public function up()
    {
        Schema::create('carrera', function (Blueprint $table) {
            $table->id('idCarrera'); // Llave primaria
            $table->string('nombreCarrera', 255); // Nombre de la carrera
        });
    }

    public function down()
    {
        Schema::dropIfExists('carrera');
    }
}
