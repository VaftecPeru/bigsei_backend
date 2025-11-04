<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'rol';
    protected $primaryKey = 'id_rol';
    public $timestamps = false;

    protected $fillable = [
        'id_rol',
        'nombre',
        'codigo'
    ];

    // // Relación con la tabla usuarioRol
    // public function usuarioRol()
    // {
    //     return $this->hasMany(UsuarioRol::class, 'idRol');
    // }
}
