<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
    protected $table = 'periodo';
    protected $primaryKey = 'id_periodo';
    public $timestamps = false;

    protected $fillable = [
        'id_periodo',
        'nombre',
        'descripcion',
        'fecha_ini',
        'fecha_fin',
        'fechareg',
        'id_usuarioreg',
        'id_empresa',
        'estado',
        'esta_abierto'
    ];

    // Si necesitas la relación inversa
    public function ciclos()
    {
        return $this->hasMany(Ciclo::class, 'id_periodo');
    }

}
