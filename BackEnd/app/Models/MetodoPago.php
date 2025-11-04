<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model
{
    protected $table = 'metodo_pago';
    protected $primaryKey = 'idMetodoPago';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    // Relación con la tabla MatriculaPagos
    public function matriculaPagos()
    {
        return $this->hasMany(MatriculaPagos::class, 'idMetodoPago');
    }
}
