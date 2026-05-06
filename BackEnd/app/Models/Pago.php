<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pago';
    protected $primaryKey = 'idPago';
    public $timestamps = false;

    protected $fillable = [
        'idUsuario',
        'idMetodoPago',
        'idNivel',        // Nuevo campo
        'idGrado',        // Nuevo campo
        'descripcion',
        'importe',
        'igv',
        'total',
        'fechaPago',
        'conciliado',
    ];

    // Relación con la tabla Usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario');
    }

    // Relación con la tabla MetodoPago
    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'idMetodoPago');
    }

    // Relación con la tabla Nivel
    public function nivel()
    {
        return $this->belongsTo(Nivel::class, 'idNivel');
    }

    // Relación con la tabla Grado
    public function grado()
    {
        return $this->belongsTo(Grado::class, 'idGrado');
    }

    // Relación con la tabla MatriculaPagos
    public function matriculaPagos()
    {
        return $this->hasOne(MatriculaPagos::class, 'idPago');
    }

    // Relación con la tabla de Pago
    public function factura()
    {
        return $this->hasOne(Factura::class, 'idPago');
    }
}

