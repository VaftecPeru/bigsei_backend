<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    protected $table = 'docente';
    protected $primaryKey = 'id_docente';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'id_docente',
        'idUsuario',
        'codigo',
        'estado',
        'id_usuarioreg',
        'fechareg',
        'anhos_de_experiencia',
        'id_tiponiveleducativo',
        'id_tipoespecializacion'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario');
    }

    public function nivel()
    {
        return $this->belongsTo(Nivel::class, 'id');
    }
}