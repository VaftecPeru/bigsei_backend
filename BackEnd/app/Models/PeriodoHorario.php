<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoHorario extends Model
{
    protected $table = 'periodo_horario';
    protected $primaryKey = 'id_periodohorario';
    public $timestamps = false;

    protected $fillable = [
        'id_periodohorario',
        'id_empresa',
        'id_periodo',
        'id_periodocurso',
        'id_dia',
        'id_aula',
        'hora_inicio',
        'hora_fin',
        'id_usuarioreg',
        'fechareg'
    ];
}
