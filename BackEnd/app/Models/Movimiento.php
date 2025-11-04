<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    use HasFactory;

    // Especificar la tabla (opcional, si el nombre no es el plural del modelo)
    protected $table = 'movimientos';

    // Campos que se pueden asignar de forma masiva
    protected $fillable = [
        'id_mes',
        'mes_nombre',
        'fecha',
        'monto',
        'metodopago_descripcion',
        'tipo', // "I" para ingreso, "E" para egreso
        'usuario_nombre',
        'rol_nombre',
        'descripcion'
    ];

    // Cast para convertir automáticamente los valores en las consultas
    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario');
    }

    public function metodoPago()
    {
        return $this->hasOne(MetodoPago::class, 'id', 'idMetodoPago');
    }
}
