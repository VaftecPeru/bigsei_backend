<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoVideo extends Model
{
    protected $table = 'periodo_video';
    protected $primaryKey = 'id_periodovideo';
    public $timestamps = false;

    protected $fillable = [
        'id_periodovideo',
        'id_empresa',
        'id_periodotema',
        'nombre',
        'descripcion',
        'url',
        'tipo',
        'tiene_contenido',
        'id_archivo',
        'id_usuarioreg',
        'fechareg'
    ];

}