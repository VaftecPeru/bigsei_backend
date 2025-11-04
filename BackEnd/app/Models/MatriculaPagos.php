<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatriculaPagos extends Model
{
    protected $table = 'matricula_pagos';
    protected $primaryKey = 'idMatriculaPago';
    public $timestamps = false;

    protected $fillable = [
        'idMatricula', 
        'idPago'
    ];

    // Relación con la tabla Matricula
    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'idMatricula');
    }

    // Relación con la tabla Pago
    public function pago()
    {
        return $this->belongsTo(Pago::class, 'idPago');
    }
}
