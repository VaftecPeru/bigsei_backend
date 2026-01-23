<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\CourseProgress;

/**
 * Evento que se dispara cuando un curso alcanza 100% de progreso
 * Hito 8/9: Regla de Finalización del Curso
 */
class CourseCompleted
{
    use Dispatchable, SerializesModels;

    public int $id_usuario;
    public int $id_periodocurso;
    public CourseProgress $courseProgress;

    /**
     * Create a new event instance.
     */
    public function __construct(int $id_usuario, int $id_periodocurso, CourseProgress $courseProgress)
    {
        $this->id_usuario = $id_usuario;
        $this->id_periodocurso = $id_periodocurso;
        $this->courseProgress = $courseProgress;
    }
}
