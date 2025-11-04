<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CursoClases extends Model
{
    protected $table = 'curso_clases';
    protected $primaryKey = 'idCursoClase';
    public $timestamps = false;

    protected $fillable = [
        'idCurso',
        'totalClases'
    ];

    // Relacion con la tabla curso
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'idCurso');
    }
}
