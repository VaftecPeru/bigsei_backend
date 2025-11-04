<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluacionesNotas extends Model
{
    protected $table = 'evaluaciones_notas';
    protected $primaryKey = 'idNotaCursoEstudiante';
    public $timestamps = false;

    protected $fillable = [
        'idEvaluacionNota',
        'idUsuario',
        'nota',
    ];

    // Relación con la tabla CursoEvaluaciones
    public function cursoEvaluacion()
    {
        return $this->belongsTo(CursoEvaluaciones::class, 'idEvaluacionNota','idCursoEvaluacion');
    }

    // Relación con la tabla Usuarios
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario','id_usuario');
    }
}
