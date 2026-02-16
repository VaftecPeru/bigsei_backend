<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendiente', function (Blueprint $table) {
            $table->bigIncrements('id_pendiente');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_empresa');
            $table->string('titulo', 255);
            $table->text('descripcion')->nullable();
            $table->enum('prioridad', ['Alta', 'Media', 'Baja'])->default('Media');
            $table->boolean('completado')->default(false);
            $table->date('fecha_limite')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendiente');
    }
};
