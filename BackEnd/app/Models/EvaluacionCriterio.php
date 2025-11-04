<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluacionCriterio extends Model
{
    protected $table = 'evaluacion_criterio';
    protected $primaryKey = 'id_evaluacioncriterio';
    public $timestamps = false;

    protected $fillable = [
        'id_evaluacioncriterio',
        'id_empresa',
        'id_periodocurso',
        'titulo',
        'descripcion',
        'estado',
        'id_usuarioreg',
        'fechareg'
    ];

}