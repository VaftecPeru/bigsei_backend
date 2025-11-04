<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nivel extends Model
{
    protected $table = 'nivel';
    protected $fillable = ['nombre'];

    // Relación con grado
    public function grados()
    {
        return $this->hasMany(Grado::class, 'idNivel');
    }
}
