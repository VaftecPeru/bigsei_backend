<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    use HasFactory;

    protected $table = 'gastos';

    protected $fillable = [
        'nro_operacion',
        'nombre_destinatario',
        'id_usuario',
        'monto',
        'fecha_registro',
        'fecha_pago',
        'estado_pago',
        'estado_sunat',
        'nota',
    ];

    // Relación con la tabla de usuarios
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}