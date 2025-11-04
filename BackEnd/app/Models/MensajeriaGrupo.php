<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MensajeriaGrupo extends Model
{
    protected $table = 'mensajeria_grupo';
    protected $primaryKey = 'id_mensajeriagrupo';
    public $timestamps = false;

    protected $fillable = [
        'id_mensajeriagrupo',
        'nombre',
        'tipo',
        'id_periodocurso',
        'estado',
        'id_usuarioreg',
        'fechareg'
    ];
}