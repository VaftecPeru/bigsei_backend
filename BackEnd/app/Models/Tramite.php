<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;

class Tramite extends Model
{
    use HasFactory;
    protected $table = 'tramites';
    protected $primaryKey = 'idTramite';
    public $timestamps = false; 

    protected $fillable = [
        'idUsuario',
        'tipo_tramite',
        'estado',
        'fecha_solicitud',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario', 'id_usuario');
    }

}
