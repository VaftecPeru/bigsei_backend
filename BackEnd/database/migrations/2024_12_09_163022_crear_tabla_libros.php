<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaLibros extends Migration
{
    public function up()
    {
        Schema::create('libros', function (Blueprint $table) {
            $table->id(); // id_libro
            $table->string('titulo');
            $table->string('autor');
            $table->string('editorial');
            $table->text('descripcion');
            $table->unsignedBigInteger('id_categoria');
            $table->unsignedBigInteger('id_genero');
            $table->timestamps();

            // Llaves foráneas
            $table->foreign('id_categoria')->references('id')->on('categorias')->onDelete('cascade');
            $table->foreign('id_genero')->references('id')->on('generos')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('libros');
    }
}
