<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Membresia extends Model
{
    protected $table = 'membresia';
    protected $primaryKey = 'id_membresia';
    public $timestamps = false;

    protected $fillable = [
        'id_membresia',
        'id_persona',
        'id_membresiatipo',
        'precio',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'id_usuarioreg',
        'fechareg'
    ];
}