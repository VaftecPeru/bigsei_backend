<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioSesion extends Model
{
    protected $table = 'usuario_sesion';
    protected $primaryKey = 'id_usuariosesion';
    public $timestamps = false;

    protected $fillable = [
        'id_usuariosesion',
        'id_empresa',
        'id_usuario',
        'id_rol',
        'token',
        'estado',
        'fechareg',
        'fechamod'
    ];
}
