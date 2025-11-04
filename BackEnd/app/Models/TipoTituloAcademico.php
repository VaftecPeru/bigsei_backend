<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoTituloAcademico extends Model
{
    protected $table = 'tipo_titulo_academico';
    protected $primaryKey = 'id_tipotituloacademico';
    public $timestamps = false;

    protected $fillable = [
        'id_tipotituloacademico',
        'nombre',
        'estado',
        'orden'
    ];

}