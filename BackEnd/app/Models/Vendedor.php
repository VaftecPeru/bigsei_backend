<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendedor extends Model
{
    protected $table = 'vendedor';
    protected $primaryKey = 'id_vendedor';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'id_vendedor',
        'fechareg',
        'id_usuarioreg'
    ];
}