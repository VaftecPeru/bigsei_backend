<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoCurso extends Model
{
    protected $table = 'tipo_curso';
    protected $primaryKey = 'idTipoCurso';
    public $timestamps = false;

    protected $fillable = [
        'nombre'
    ];

    // Relación con la tabla CursoTipo
    public function cursoTipo()
    {
        return $this->belongsToMany(Curso::class, 'curso_tipo', 'idTipoCurso', 'idCurso');
    }
}
