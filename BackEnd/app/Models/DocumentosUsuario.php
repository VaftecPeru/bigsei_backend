<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentosUsuario extends Model
{
    use HasFactory;

    protected $table = 'documentos_usuario';
    protected $primaryKey = 'idDocumento';
    public $timestamps = false;

    protected $fillable = [
        'idUsuario',
        'nombreArchivo',
        'rutaArchivo',
        'tipoArchivo',
        'fechaSubida',
    ];

    // Relación con la tabla usuarios 
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario');
    }
}
