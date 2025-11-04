<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CursoTipo extends Model
{
    protected $table = 'curso_tipo';
    protected $primaryKey = 'idCursoTipo';
    public $timestamps = false;

    protected $fillable = [
        'idCurso',
        'idTipoCurso'
    ];

    // Relación con la tabla Curso
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'idCurso');
    }

    // Relación con la tabla TipoCurso
    public function tipoCurso()
    {
        return $this->belongsTo(TipoCurso::class, 'idTipoCurso');
    }

    public function cursoHorarios()
    {
        return $this->hasMany(CursoHorario::class, 'idCursoTipo');
    }

    public function docente()
    {
        return $this->hasOne(CursoDocentes::class, 'idCurso', 'idCurso');
    }


    // Relación con la tabla TareasCurso
    public function tareas()
    {
        return $this->hasManyThrough(TareasCurso::class, Curso::class, 'idCurso', 'idCurso');
    }
}
