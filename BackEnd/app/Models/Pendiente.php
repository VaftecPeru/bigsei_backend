<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pendiente extends Model
{
    protected $table = 'pendiente';
    protected $primaryKey = 'id_pendiente';

    protected $fillable = [
        'id_usuario',
        'id_empresa',
        'titulo',
        'descripcion',
        'prioridad',
        'completado',
        'fecha_limite',
    ];

    protected $casts = [
        'completado' => 'boolean',
        'fecha_limite' => 'date',
    ];
}
