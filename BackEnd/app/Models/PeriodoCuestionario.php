<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoCuestionario extends Model
{
    protected $table = 'periodo_cuestionario';
    protected $primaryKey = 'id_periodocuestionario';
    public $timestamps = false;

    protected $fillable = [
        'id_periodocuestionario',
        'id_empresa',
        'id_periodotema',
        'titulo',
        'instruccion',
        'id_usuarioreg',
        'fechareg'
    ];

}