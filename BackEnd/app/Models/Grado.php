<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grado extends Model
{
    protected $table = 'grado';
    protected $fillable = ['nombre', 'idNivel'];

    // Relación con nivel
    public function nivel()
    {
        return $this->belongsTo(Nivel::class, 'idNivel');
    }
}
