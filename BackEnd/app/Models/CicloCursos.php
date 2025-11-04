<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CicloCursos extends Model
{
    use HasFactory;

    protected $table = 'ciclo_cursos';
    protected $primaryKey = 'idRegistroCurso';
    public $timestamps = false;

    protected $fillable = [
        'idCurso',
        'idCiclo',
    ];

    // Relación con la tabla Curso
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'idCurso');
    }

    // Relación con la tabla Ciclo 
    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class, 'idCiclo');
    }
}
