<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluacion extends Model
{
    protected $table = 'evaluacion';
    protected $primaryKey = 'idEvaluacion';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'abreviatura'
    ];

    // Relación con la tabla CursoEvaluciones
    public function cursoEvaluaciones()
    {
        return $this->hasMany(CursoEvaluaciones::class, 'idEvaluacion');
    }
}
