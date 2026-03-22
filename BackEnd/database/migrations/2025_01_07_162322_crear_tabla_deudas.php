<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaDeudas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deudas', function (Blueprint $table) {
            $table->id(); // ID único para cada deuda
            $table->unsignedBigInteger('idUsuario'); // Relación con la tabla usuarios
            $table->string('descripcion'); // Descripción de la deuda
            $table->decimal('importe', 10, 2); // Monto de la deuda
            $table->date('fecha_a_pagar'); // Fecha límite de pago
            $table->enum('estado', ['pendiente', 'pagado'])->default('pendiente'); // Estado de la deuda
            $table->text('observacion')->nullable(); 
            $table->timestamps(); 

            //Relación con la tabla usuarios
            $table->foreign('idUsuario')->references('id_usuario')->on('usuario')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deudas');
    }
}

