<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenciaTipo extends Model
{
    protected $table = 'licencia_tipo';
    protected $primaryKey = 'id_licenciatipo';
    public $timestamps = false;

    protected $fillable = [
        'id_licenciatipo',
        'nombre',
        'descripcion',
        'precio',
        'estado',
        'id_usuarioreg',
        'fechareg',
    ];

}
