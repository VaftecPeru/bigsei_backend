<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ciclo extends Model
{
    protected $table = 'ciclo';
    protected $primaryKey = 'id_ciclo';
    public $timestamps = false;

    protected $fillable = [
        'id_ciclo',
        'nombre',
        'orden',
        'estado'
    ];

    // // Relación con la tabla Modalidad
    // public function modalidad()
    // {
    //     return $this->belongsTo(Modalidad::class, 'idModalidad');
    // }

    // // Relación con la tabla CicloCursos
    // public function cicloCursos()
    // {
    //     return $this->hasMany(CicloCursos::class, 'idCiclo');
    // }

    // Relación con la tabla Periodo
    public function periodo()
    {
        return $this->belongsTo(Periodo::class, 'idPeriodo');
    }
}
