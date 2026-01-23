<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseProgress extends Model
{
    protected $table = 'course_progress';
    protected $primaryKey = 'id_course_progress';
    public $timestamps = false;

    protected $fillable = [
        'id_course_progress',
        'id_usuario',
        'id_periodocurso',
        'status',
        'progress_percentage',
        'started_at',
        'completed_at',
        'fechareg'
    ];

    protected $casts = [
        'progress_percentage' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'fechareg' => 'datetime',
    ];

    /**
     * Estados posibles del curso
     */
    const STATUS_NOT_STARTED = 'not_started';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';

    /**
     * Relación con el usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Relación con el periodo curso
     */
    public function periodoCurso()
    {
        return $this->belongsTo(PeriodoCurso::class, 'id_periodocurso', 'id_periodocurso');
    }

    /**
     * Obtiene o crea el progreso de un curso para un usuario
     */
    public static function getOrCreate(int $id_usuario, int $id_periodocurso): self
    {
        return self::firstOrCreate(
            [
                'id_usuario' => $id_usuario,
                'id_periodocurso' => $id_periodocurso,
            ],
            [
                'status' => self::STATUS_NOT_STARTED,
                'progress_percentage' => 0,
                'fechareg' => now(),
            ]
        );
    }

    /**
     * Actualiza el progreso y el estado del curso
     */
    public function updateProgress(float $percentage): void
    {
        $this->progress_percentage = $percentage;

        if ($percentage >= 100 && $this->status !== self::STATUS_COMPLETED) {
            $this->status = self::STATUS_COMPLETED;
            $this->completed_at = now();
        } elseif ($percentage > 0 && $this->status === self::STATUS_NOT_STARTED) {
            $this->status = self::STATUS_IN_PROGRESS;
            $this->started_at = now();
        }

        $this->save();
    }

    /**
     * Verifica si el curso está completado
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
