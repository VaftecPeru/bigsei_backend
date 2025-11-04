<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
    protected $table = 'matricula';
    protected $primaryKey = 'id_matricula';
    public $timestamps = false;

    protected $fillable = [
        'id_matricula',
        'id_periodo',
        'idUsuario',
        'importe',
        'estado',
        'id_usuarioreg',
        'fechaRegistro',
        'id_empresa',
        'id_estudiante',
        'fechareg',
        'tipo',
        'id_membresia'
    ];

    // Relacion con la tabla usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario');
    }

    public function usuarioRegistra()
    {
        return $this->belongsTo(Usuario::class, 'id_usuarioreg');
    }

    // Relacion con la tabla matriculaCursos
    public function matriculaCursos()
    {
        return $this->hasMany(MatriculaCurso::class, 'idMatricula');
    }

    // Relacion con la tabla matriculaPagos
    public function matriculaPagos()
    {
        return $this->hasOne(MatriculaPagos::class, 'idMatricula');
    }
}
