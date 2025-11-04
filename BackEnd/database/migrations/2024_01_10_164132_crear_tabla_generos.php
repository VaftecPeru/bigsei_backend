<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaGeneros extends Migration
{
    public function up()
    {
        Schema::create('generos', function (Blueprint $table) {
            $table->id(); // ID principal
            $table->string('nombre'); // Nombre del género
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('generos');
    }
}
