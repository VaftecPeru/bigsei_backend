<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PadreHijo extends Model
{
    protected $table = 'padre_hijo';
    protected $primaryKey = 'idPadreHijo';

    protected $fillable = [
        'idPadre',
        'idHijo',
    ];

    /**
     * Relación con el usuario padre
     */
    public function padre()
    {
        return $this->belongsTo(Usuario::class, 'idPadre', 'idUsuario');
    }

    /**
     * Relación con el usuario hijo (estudiante)
     */
    public function hijo()
    {
        return $this->belongsTo(Usuario::class, 'idHijo', 'idUsuario');
    }
}
