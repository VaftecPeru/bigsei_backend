<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoTema extends Model
{
    protected $table = 'periodo_tema';
    protected $primaryKey = 'id_periodotema';
    public $timestamps = false;

    protected $fillable = [
        'id_periodotema',
        'id_empresa',
        'id_periodomodulo',
        'titulo',
        'descripcion',
        'id_usuarioreg',
        'fechareg',
        'id_tipocategoria',
        'fecha'
    ];
}