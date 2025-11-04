<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MensajeriaPersona extends Model
{
    protected $table = 'mensajeria_persona';
    protected $primaryKey = 'id_mensajeriapersona';
    public $timestamps = false;

    protected $fillable = [
        'id_mensajeriapersona',
        'id_mensajeriagrupo',
        'id_persona',
        'estado',
        'id_usuarioreg',
        'fechareg'
    ];
}