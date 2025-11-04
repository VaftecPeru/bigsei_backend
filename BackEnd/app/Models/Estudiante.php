<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    protected $table = 'estudiante';
    protected $primaryKey = 'id_estudiante';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'id_estudiante',
        'codigo',
        'estado',
        'id_usuario',
        'id_usuarioreg',
        'fechareg'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}
