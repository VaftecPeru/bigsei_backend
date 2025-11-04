<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaDevoluciones extends Migration
{
    public function up()
    {
        Schema::create('devoluciones', function (Blueprint $table) {
            $table->id(); 
            $table->string('estado'); // 'atrasada', 'a tiempo', etc.
            $table->date('fecha_devolucion'); // Fecha de la devolución
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('devoluciones');
    }
}

