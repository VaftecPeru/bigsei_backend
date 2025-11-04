<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    protected $table = 'curso';
    protected $primaryKey = 'id_curso';
    public $timestamps = false;

    protected $fillable = [
        'id_curso',
        'codigo',
        'id_empresa',
        'nombre',
        'url_img',
        'id_archivo'
    ];

    public function cursoEstudiantes()
    {
        return $this->hasMany(CursoEstudiantes::class, 'idCurso','id_curso');
    }

    public function cursoDocentes()
    {
        return $this->hasMany(CursoDocentes::class, 'idCurso','id_curso');
    }
}
