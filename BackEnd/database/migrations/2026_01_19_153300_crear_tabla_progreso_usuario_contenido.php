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
        Schema::create('progreso_usuario_contenido', function (Blueprint $table) {
            $table->id('id_progreso');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_periodocurso');
            $table->string('tipo_contenido', 50); // 'video', 'tarea', 'cuestionario'
            $table->unsignedBigInteger('id_contenido'); // ID del video, tarea o cuestionario
            $table->boolean('completado')->default(false);
            $table->timestamp('fecha_completado')->nullable();
            $table->timestamp('fechareg')->useCurrent();

            // Indexes for performance
            $table->index('id_usuario', 'idx_progreso_usuario');
            $table->index('id_periodocurso', 'idx_progreso_curso');
            $table->unique(['id_usuario', 'tipo_contenido', 'id_contenido'], 'unique_user_content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progreso_usuario_contenido');
    }
};
