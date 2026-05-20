<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'persona';
    protected $primaryKey = 'id_persona';
    public $timestamps = false;

    protected $fillable = [
        'id_persona',
        'id_empresa',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'numero_documento',
        'correo',
        'telefono',
        'estado',
        'fechareg',
        'nombre_completo',
        'direccion',
        'sexo',
        'fecha_nacimiento',
        'id_tipodocumento',
        'foto',
        'id_archivo_foto',
        'id_archivo_baner',
        'id_tiponiveleducativo',
        'programa_estudios',
        'id_tiponiveleducativo_formativo'
    ];
    
    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id_usuario', 'id_persona');
    }

    public function nivelEducativo()
    {
        return $this->belongsTo(TipoNiveleducativo::class, 'id_tiponiveleducativo');
    }

    public function nivelFormativo()
    {
        return $this->belongsTo(TipoNiveleducativo::class, 'id_tiponiveleducativo_formativo');
    }

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'id_tipodocumento');
    }
}
