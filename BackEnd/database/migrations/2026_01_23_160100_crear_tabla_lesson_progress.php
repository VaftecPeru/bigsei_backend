<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Hito 12: Tabla lesson_progress para seguimiento de progreso por lección
     * Hito 6: Una lección solo se registra una vez (unique constraint)
     */
    public function up(): void
    {
        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id('id_lesson_progress');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_periodocurso');
            $table->unsignedBigInteger('id_lesson'); // ID del video, tarea o cuestionario
            $table->enum('lesson_type', ['video', 'tarea', 'cuestionario']);
            $table->enum('status', ['completed'])->default('completed');
            $table->timestamp('completed_at')->useCurrent();
            $table->timestamp('fechareg')->useCurrent();

            // Indexes para mejorar rendimiento
            $table->index('id_usuario', 'idx_lesson_progress_usuario');
            $table->index('id_periodocurso', 'idx_lesson_progress_curso');
            $table->index(['id_lesson', 'lesson_type'], 'idx_lesson_progress_lesson');
            
            // Una lección solo se registra una vez por usuario (Hito 6)
            $table->unique(['id_usuario', 'id_periodocurso', 'id_lesson', 'lesson_type'], 'unique_user_lesson_progress');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_progress');
    }
};
