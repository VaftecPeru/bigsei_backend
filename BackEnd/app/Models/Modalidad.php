<?php

// app/Models/Modalidad.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modalidad extends Model
{
    protected $table = 'modalidad';
    protected $primaryKey = 'idModalidad';
    public $timestamps = false;

    protected $fillable = ['nombreModalidad'];

    // Relación con curso
    public function curso()
    {
        return $this->hasMany(Curso::class, 'idModalidad');
    }
}
