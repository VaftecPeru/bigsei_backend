<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioRol extends Model
{
    protected $table = 'usuario_rol';
    protected $primaryKey = 'id_usuariorol';
    public $timestamps = false;

    protected $fillable = [
        'id_usuariorol',
        'id_empresa',
        'id_usuario',
        'id_rol',
        'es_principal'
    ];

    // // // Relación con la tabla Usuario
    // public function usuario()
    // {
    //     return $this->belongsTo(Usuario::class, 'idUsuario');
    // }

    // // // Relación con la tabla Rol
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    // Relación con Rol (🔥 CORREGIDO)
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }
}
