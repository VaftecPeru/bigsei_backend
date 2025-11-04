<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaReservas extends Migration
{
    public function up()
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id(); // ID de la reserva
            $table->unsignedBigInteger('idLibro'); // Relación con la tabla libros
            $table->unsignedBigInteger('idUsuario'); // Relación con la tabla usuarios
            $table->string('tipo_usuario'); // 'estudiante' o 'docente'
            $table->date('fecha'); // Fecha de la reserva
            $table->string('estado')->default('activa'); // Estado de la reserva
            $table->timestamps();

            $table->foreign('idLibro')->references('id')->on('libros')->onDelete('cascade');
            $table->foreign('idUsuario')->references('idUsuario')->on('usuarios')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reservas');
    }
}
