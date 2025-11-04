<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    use HasFactory;

    protected $fillable = [
        'idLibro',
        'idUsuario',
        'tipo_usuario',
        'fecha',
        'estado',
    ];

    public function libro()
    {
        return $this->belongsTo(Libro::class, 'idLibro', 'id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario', 'idUsuario');
    }
}
