<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Archivo extends Model
{
    protected $table = 'archivo';
    protected $primaryKey = 'id_archivo';
    public $timestamps = false;

    protected $fillable = [
        'id_archivo',
        'nombre',
        'url',
        'extension',
        'tipo',
        'tamanho',
        'id_periodotarea',
        'id_periodotema',
        'id_usuarioreg',
        'fechareg'
    ];

}