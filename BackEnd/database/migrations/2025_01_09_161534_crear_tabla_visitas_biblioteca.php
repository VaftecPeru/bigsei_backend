<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaVisitasBiblioteca extends Migration
{
    public function up()
    {
        Schema::create('visitas_biblioteca', function (Blueprint $table) {
            $table->id(); 
            $table->integer('id_anho'); // Año
            $table->integer('id_mes'); // Número del mes (1-12)
            $table->string('mes_nombre', 20); // Nombre del mes 
            $table->integer('cant_visitas')->default(0); // Contador de visitas
            $table->timestamps(); // Timestamps 
        });
    }

    public function down()
    {
        Schema::dropIfExists('visitas_biblioteca');
    }
}

