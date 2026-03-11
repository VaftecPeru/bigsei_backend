<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gastos', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->string('nro_operacion')->nullable(); 

            $table->string('nombre_destinatario'); 

            $table->unsignedBigInteger('id_usuario');

            $table->decimal('monto', 10, 2); 

            $table->dateTime('fecha_registro'); 

            $table->date('fecha_pago')->nullable(); 

            $table->string('estado_pago'); 

            $table->string('estado_sunat'); 

            $table->text('nota')->nullable(); 

            $table->timestamps();

            $table->foreign('id_usuario')
                ->references('id_usuario')
                ->on('usuario')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
