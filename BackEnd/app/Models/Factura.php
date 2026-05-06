<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $table = 'factura';
    protected $primaryKey = 'idFactura';
    public $timestamps = false;

    protected $fillable = [
        'idPago',
        'numeroFactura',
        'cliente',
        'documento',
        'subtotal',
        'igv',
        'total',
        'estado',
        'fecha'
    ];

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'idPago');
    }
}
