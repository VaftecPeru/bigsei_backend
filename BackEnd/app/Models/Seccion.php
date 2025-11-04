<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seccion extends Model
{
    protected $table = 'seccion';
    protected $primaryKey = 'id_seccion';
    public $timestamps = false;

    protected $fillable = [
        'id_seccion',
        'nombre',
        'estado',
    ];

    // // Relación con la tabla Curso
    // public function cursos()
    // {
    //     return $this->hasMany(Curso::class, 'idSeccion', 'idSeccion');
    // }

    // public function tareas()
    // {
    //     return $this->hasMany(TareasCurso::class, 'id_seccion', 'idSeccion');
    // }
}
