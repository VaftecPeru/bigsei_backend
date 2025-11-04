<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CursoEvaluaciones extends Model
{
    protected $table = 'curso_evaluaciones';
    protected $primaryKey = 'idCursoEvaluacion';
    public $timestamps = false;

    protected $fillable = [
        'idCurso',
        'idEvaluacion',
        'porcentaje',
        'fechaEvaluacion'
    ];

    // Relación con la tabla Curso
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'idCurso','id_curso');
    }

    // Relación con la tabla Evaluacion
    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class, 'idEvaluacion');
    }

    // Relación con la tabla NotasCursoEstudiante
    public function evaluacionesNotas()
    {
        return $this->hasMany(EvaluacionesNotas::class, 'idEvaluacionNota');
    }
}
