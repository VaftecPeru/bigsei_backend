<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsistenciaDocente extends Model
{
    use HasFactory;

    protected $table = 'asistencias_docentes';
    protected $primaryKey = 'id';
    protected $fillable = [
        'idCurso',
        'idUsuario',
        'fecha',
        'estado',
        'created_at',
        'updated_at'
    ];

    public $timestamps = true; 

    // Relación con el modelo Curso
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'idCurso');
    }

    // Relación con el modelo Usuario (docente)
    public function docente()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario');
    }

    public function horario()
    {
        return $this->hasOneThrough(
            CursoHorario::class,
            Curso::class,
            'id', 
            'idCurso', 
            'idCurso', 
            'id' 
        );
    }

    protected $casts = [
        'fecha' => 'date',
        'estado' => 'boolean', 
    ];

    protected $attributes = [
        'estado' => true, 
    ];
}