<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
// use Illuminate\Database\Eloquent\Model;

class Usuario extends Authenticatable implements JWTSubject //, Model
{
    use Notifiable;

    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_empresa',
        'dni',
        'nombres',
        'apellidoPaterno',
        'apellidoMaterno',
        'fechaNacimiento',
        'genero',
        'telefono',
        'correo',
        'direccion',
        'foto',
        'username',
        'password', 
        'estado',
        'id_usuarioreg',
        'id_usuariomod',
        'fechareg',
        'fechamod',
        'google_id',
        'email'
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_usuario', 'id_persona');
    }

    protected $hidden = [
        'password',
    ];

    // Accessor para obtener el nombre completo
    public function getNombreCompletoAttribute()
    {
        return $this->nombres . ' ' . $this->apellidoPaterno . ' ' . $this->apellidoMaterno;
    }

    // Relación con las reservas de libros
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'idUsuario', 'idUsuario');
    }

    // JWT Methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        $rolUsuario = $this->roles()->first();
        $nombreRol = optional(optional($rolUsuario)->rol)->nombreRol ?? 'student';

        return [
            'idUsuario' => $this->idUsuario,
            'dni' => $this->dni,
            'nombres' => $this->nombres,
            'estado' => $this->estado,
            'rol' => $nombreRol ?? 'Sin rol',
            'foto' => $this->foto,
        ];
    }

    // Relación con la tabla usuarioRol
    public function roles()
    {
        return $this->hasMany(UsuarioRol::class, 'id_usuario');
    }

    // Relación con Actividad_Usuario
    public function activity()
    {
        return $this->hasOne(ActividadUsuario::class, 'idUsuario');
    }

    // Relación con la tabla matricula
    public function matricula()
    {
        return $this->hasMany(Matricula::class, 'idUsuario');
    }

    // Relación con la tabla documentosUsuario
    public function documentosUsuario()
    {
        return $this->hasMany(DocumentosUsuario::class, 'idUsuario');
    }

    // Relación con la tabla cursosDocente
    public function cursosDocente()
    {
        return $this->hasMany(CursoDocentes::class, 'idUsuario');
    }

    // Relación con la tabla cursosEstudiante
    public function cursosEstudiante()
    {
        return $this->hasMany(CursoEstudiantes::class, 'idUsuario');
    }

    //RELACION CON TABLA CARRERA_ESTUDIANTES
    public function carrerasEstudiante()
    {
        return $this->hasMany(CarreraEstudiantes::class, 'idEstudiante', 'idUsuario');
    }

        // Relación con las tareas de los cursos
        public function tareas()
        {
            return $this->hasMany(TareasAlumno::class, 'idUsuario', 'idUsuario');
        }

      // Definimos la relación con Cursos
      public function cursos()
      {
          return $this->belongsToMany(Curso::class, 'curso_estudiantes', 'idUsuario', 'idCurso');
      }
}