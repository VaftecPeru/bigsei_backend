<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolModulo extends Model
{
    protected $table = 'rol_modulo';
    protected $primaryKey = ['id_rol','id_modulo'];
    public $timestamps = false;

    protected $fillable = [
        'id_rol',
        'id_modulo'
    ];
}
