<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitasBiblioteca extends Model
{
    use HasFactory;

    // Nombre de la tabla
    protected $table = 'visitas_biblioteca';

    // Atributos asignables en masa
    protected $fillable = [
        'id_anho',
        'id_mes',
        'mes_nombre',
        'cant_visitas',
    ];
}
