<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluacionNota extends Model
{
    protected $table = 'evaluacion_nota';
    protected $primaryKey = 'id_evaluacionnota';
    public $timestamps = false;

    protected $fillable = [
        'id_evaluacionnota',
        'id_evaluacioncriterio',
        'id_periodocurso',
        'id_estudiante',
        'id_docente',
        'nota',
        'id_tiponota',
        'id_usuarioreg',
        'fechareg'
    ];

}