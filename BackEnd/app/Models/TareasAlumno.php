<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TareasAlumno extends Model
{
    use HasFactory;

    protected $table = 'tareas_alumnos';
    protected $primaryKey = 'idTarea';
    public $timestamps = false;

    protected $fillable = [
        'idUsuario', 
        'idTareaCurso', 
        'nota', 
        'archivo_nombre', 
        'archivo_tipo', 
        'ruta', 
        'fecha_subida', 
        'revisado',
        'visto'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario');
    }

    // Relación con la tarea del curso
    public function tareaCurso()
    {
        return $this->belongsTo(TareasCurso::class, 'idTareaCurso', 'idTareaCurso');
    }
}
