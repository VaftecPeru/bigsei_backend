<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MensajeriaMensaje extends Model
{
    protected $table = 'mensajeria_mensaje';
    protected $primaryKey = 'id_mensajeriamensaje';
    public $timestamps = false;

    protected $fillable = [
        'id_mensajeriamensaje',
        'id_mensajeriagrupo',
        'id_persona',
        'texto',
        'fecha'
    ];
}