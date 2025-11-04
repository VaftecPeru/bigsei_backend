<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaMovimientos extends Migration
{
    public function up()
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id(); 
            $table->integer('id_mes'); // Número del mes
            $table->string('mes_nombre'); // Nombre del mes
            $table->date('fecha'); // Fecha del movimiento
            $table->decimal('monto', 10, 2); // Monto del movimiento
            $table->string('metodopago_descripcion'); // Método de pago
            $table->enum('tipo', ['I', 'E']); // Tipo (Ingreso o Egreso)
            $table->string('usuario_nombre'); // Nombre del usuario que registra
            $table->string('rol_nombre'); // Rol del usuario
            $table->text('descripcion')->nullable(); // Descripción opcional
            $table->timestamps(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('movimientos');
    }
}

