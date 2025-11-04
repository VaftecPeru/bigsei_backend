<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoPregunta extends Model
{
    protected $table = 'periodo_pregunta';
    protected $primaryKey = 'id_periodopregunta';
    public $timestamps = false;

    protected $fillable = [
        'id_periodopregunta',
        'id_periodocuestionario',
        'descripcion',
        'orden',
        'es_requerida',
        'id_usuarioreg',
        'fechareg',
        'id_tipopregunta'
    ];

}
