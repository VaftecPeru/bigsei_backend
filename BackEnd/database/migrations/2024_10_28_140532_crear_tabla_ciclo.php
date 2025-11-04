<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaCiclo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ciclo', function (Blueprint $table) {
            $table->id('idCiclo'); // Primary Key
            $table->unsignedBigInteger('idPeriodo');
            $table->string('nombreCiclo', 100); // Nombre del Ciclo

            $table->foreign('idPeriodo')->references('idPeriodo')->on('periodo')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ciclo');
    }
}
