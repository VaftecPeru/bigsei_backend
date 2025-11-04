<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoModulo extends Model
{
    protected $table = 'periodo_modulo';
    protected $primaryKey = 'id_periodomodulo';
    public $timestamps = false;

    protected $fillable = [
        'id_periodomodulo',
        'id_empresa',
        'id_periodocurso',
        'titulo',
        'fecha_inicio',
        'fecha_fin',
        'orden',
        'id_usuarioreg',
        'fechareg',
        'descripcion'
    ];
}