<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TituloAcademico extends Model
{
    protected $table = 'titulo_academico';
    protected $primaryKey = 'id_tituloacademico';
    public $timestamps = false;

    protected $fillable = [
        'id_tituloacademico',
        'id_tipotituloacademico',
        'id_carrera',
        'nombre',
        'estado',
        'orden'
    ];

}