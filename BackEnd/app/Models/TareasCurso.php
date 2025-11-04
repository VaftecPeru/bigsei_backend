<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TareasCurso extends Model
{
    use HasFactory;

    protected $primaryKey = 'idTareaCurso'; 
    protected $table = 'tareas_curso';
    public $timestamps = false;

    // Especifica los campos que pueden ser asignados masivamente
    protected $fillable = [
        'idCurso', 
        'descripcion', 
        'fecha_inicio', 
        'fecha_fin', 
        'idSeccion',
        'nota'
    ];

    // Relación con el modelo Curso (uno a muchos)
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'idCurso', 'id_curso');
    }

    // Relación con las tareas de los alumnos
    public function tareasAlumnos()
    {
        return $this->hasMany(TareasAlumno::class, 'idTareaCurso', 'idTarea');
    }
}
