<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deuda extends Model
{
    use HasFactory;

    protected $table = 'deudas'; // Nombre de la tabla

    // Campos asignables en la tabla
    protected $fillable = [
        'idUsuario',
        'descripcion',
        'importe',
        'fecha_a_pagar',
        'estado',
        'observacion',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario', 'id_usuario');
    }

}