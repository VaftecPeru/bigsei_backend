<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
//LIBRERIAS
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
//MODELOS
use App\Models\CarreraCurso;
use App\Models\DocumentosUsuario;
use App\Models\Usuario;
use App\Models\UsuarioRol;
use App\Models\Curso;
use App\Models\CarreraEstudiantes;
use App\Models\CursoEstudiantes;
use App\Models\CursoHorario;
use App\Models\CursoHorarioEstudiantes;
use App\Models\Matricula;
use App\Models\MatriculaCurso;
use App\Models\MatriculaPagos;
use App\Models\Pago;
use Barryvdh\DomPDF\PDF;
use Carbon\Carbon;

class MatriculaController extends Controller
{
    // Funcion para registrar los datos de un estudiante
    public function registrarDatosEstudiante(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dni' => 'required|integer|digits:8|unique:usuarios',
            'nombres' => 'required|string|max:60',
            'apellidoPaterno' => 'required|string|max:40',
            'apellidoMaterno' => 'required|string|max:40',
            'fechaNacimiento' => 'required|date',
            'genero' => 'required|string|max:10',
            'telefono' => 'required|integer|digits:9',
            'correo' => 'required|string|email|max:50|unique:usuarios',
            'direccion' => 'required|string|max:60',
            'idCarrera' => 'required|exists:carrera,idCarrera',
        ], [
            'dni.required' => 'El dni es requerido',
            'dni.digits' => 'El dni debe tener 8 dígitos',
            'dni.unique' => 'El dni ya existe',
            'nombres.required' => 'El nombre es requerido',
            'apellidoPaterno.required' => 'El apellido paterno es requerido',
            'apellidoMaterno.required' => 'El apellido materno es requerido',
            'fechaNacimiento.required' => 'La fecha de nacimiento es requerida',
            'genero.required' => 'El género es requerido',
            'telefono.required' => 'El teléfono es requerido',
            'telefono.digits' => 'El teléfono debe tener 9 dígitos',
            'correo.required' => 'El correo es requerido',
            'correo.email' => 'El correo no es válido',
            'correo.unique' => 'El correo ya existe',
            'direccion.required' => 'La dirección es requerida',
            'idCarrera.required' => 'No se ha seleccionado una carrera',
            'idCarrera.exists' => 'La carrera seleccionada no existe',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Registrar los datos del estudiante
        $estudiante = Usuario::create([
            'dni' => $request->dni,
            'nombres' => $request->nombres,
            'apellidoPaterno' => $request->apellidoPaterno,
            'apellidoMaterno' => $request->apellidoMaterno,
            'fechaNacimiento' => $request->fechaNacimiento,
            'genero' => $request->genero,
            'telefono' => $request->telefono,
            'correo' => $request->correo,
            'direccion' => $request->direccion,
            'estado' => 'loggedOff',
            'fechaRegistro' => now(),
        ]);

        if (!$estudiante) {
            return response()->json(['mensaje' => 'Ocurrio un error al registrar los datos del estudiante'], 500);
        }

        // Asignar el rol de estudiante al usuario
        $idEstudiante = $estudiante->idUsuario;
        $idRol = 4;

        $asignarRol = UsuarioRol::create([
            'idUsuario' => $idEstudiante,
            'idRol' => $idRol,
        ]);

        if (!$asignarRol) {
            return response()->json(['mensaje' => 'Ocurrio un error al matricular al estudiante'], 500);
        }

        // Matricular al estudiante en la carrera seleccionada
        $matricularCarrera = CarreraEstudiantes::create([
            'idCarrera' => $request->idCarrera,
            'idEstudiante' => $idEstudiante,
        ]);

        if (!$matricularCarrera) {
            return response()->json(['mensaje' => 'Ocurrio un error al matricular en la carrera'], 500);
        }

        $token = JWTAuth::fromUser($estudiante);

        return response()->json([
            'mensaje' => 'Los datos del estudiante fueron registrados correctamente',
            'estudiante' => $estudiante,
            'token' => $token
        ], 201);

    }

    public function subirFotoPerfiMatricula(Request $request, $idUsuario)
    {
        $estudiante = Usuario::find($idUsuario);
        if (!$estudiante) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
        }
    
        // Verifica si hay un archivo en la solicitud
        if ($request->hasFile('perfil')) {
            // Almacenar en el directorio público de storage
            $path = "profiles/usuarios/$idUsuario";
    
            // Si hay una imagen de perfil existente, elimínala antes de guardar la nueva
            if ($estudiante->perfil && Storage::disk('public')->exists($estudiante->perfil)) {
                Storage::disk('public')->delete($estudiante->perfil);
            }
    
            // Guarda la nueva imagen de perfil en el disco 'public'
            $filename = $request->file('perfil')->store($path, 'public');
            $estudiante->perfil = $filename; // Actualiza la ruta en el campo `perfil` del estudiante
            $estudiante->save();
    
            return response()->json(['success' => true, 'filename' => basename($filename)]);
        }
    
        return response()->json(['success' => false, 'message' => 'No se cargó la imagen'], 400);
    }

    // Funcion para almacenar la documentacion de un estudiante
    public function registrarDocumentacionEstudiante(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idUsuario' => 'required|integer|exists:usuarios,idUsuario',
            'file_dni' => 'required|file|mimes:pdf,jpeg,png|max:5120',
            'file_partida_nacimiento' => 'required|file|mimes:pdf,jpeg,png|max:5120',
            'file_certificado_estudios' => 'required|file|mimes:pdf,jpeg,png|max:5120',
        ], [
            'idUsuario.required' => 'El ID del estudiante es requerido',
            'idUsuario.exists' => 'El ID del estudiante no existe',
            'file_dni.required' => 'El archivo del DNI es requerido',
            'file_dni.mimes' => 'El archivo del DNI debe ser un archivo PDF, JPEG o PNG',
            'file_dni.max' => 'El archivo del DNI no debe pesar más de 5MB',
            'file_partida_nacimiento.required' => 'El archivo de la partida de nacimiento es requerido',
            'file_partida_nacimiento.mimes' => 'El archivo de la partida de nacimiento debe ser un archivo PDF, JPEG o PNG',
            'file_partida_nacimiento.max' => 'El archivo de la partida de nacimiento no debe pesar más de 5MB',
            'file_certificado_estudios.required' => 'El archivo del certificado de estudios es requerido',
            'file_certificado_estudios.mimes' => 'El archivo del certificado de estudios debe ser un archivo PDF, JPEG o PNG',
            'file_certificado_estudios.max' => 'El archivo del certificado de estudios no debe pesar más de 5MB',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $estudiante = Usuario::find($request->idUsuario);

        // Obtener el primer nombre
        $nombre = explode(' ', $estudiante->nombres)[0];

        // Generar nombres de archivos
        $nombreDni = 'DNI_' . $nombre . '_' . $estudiante->apellidoPaterno . '.' . $request->file('file_dni')->getClientOriginalExtension();
        $nombrePartidaNacimiento = 'Partida_Nacimiento_' . $nombre . '_' . $estudiante->apellidoPaterno . '.' . $request->file('file_partida_nacimiento')->getClientOriginalExtension();
        $nombreCertificadoEstudios = 'Certificado_Estudios_' . $nombre . '_' . $estudiante->apellidoPaterno . '.' . $request->file('file_certificado_estudios')->getClientOriginalExtension();

        // Guardar los archivos
        $pathDni = $request->file('file_dni')->storeAs('documentos/dni', $nombreDni);
        $pathPartidaNacimiento = $request->file('file_partida_nacimiento')->storeAs('documentos/partida_nacimiento', $nombrePartidaNacimiento);
        $pathCertificadoEstudios = $request->file('file_certificado_estudios')->storeAs('documentos/certificado_estudios', $nombreCertificadoEstudios);

        // Guardar las rutas de los archivos en la base de datos
        $guardarDni = DocumentosUsuario::create([
            'idUsuario' => $request->idUsuario,
            'nombreArchivo' => $nombreDni,
            'rutaArchivo' => $pathDni,
            'tipoArchivo' => $request->file('file_dni')->getClientOriginalExtension(),
            'fechaSubida' => now(),
        ]);

        if (!$guardarDni) {
            return response()->json(['mensaje' => 'Error al guardar el archivo del DNI'], 500);
        }

        $guardarPartida = DocumentosUsuario::create([
            'idUsuario' => $request->idUsuario,
            'nombreArchivo' => $nombrePartidaNacimiento,
            'rutaArchivo' => $pathPartidaNacimiento,
            'tipoArchivo' => $request->file('file_partida_nacimiento')->getClientOriginalExtension(),
            'fechaSubida' => now(),
        ]);

        if (!$guardarPartida) {
            return response()->json(['mensaje' => 'Error al guardar el archivo de la partida de nacimiento'], 500);
        }

        $guardarCertificado = DocumentosUsuario::create([
            'idUsuario' => $request->idUsuario,
            'nombreArchivo' => $nombreCertificadoEstudios,
            'rutaArchivo' => $pathCertificadoEstudios,
            'tipoArchivo' => $request->file('file_certificado_estudios')->getClientOriginalExtension(),
            'fechaSubida' => now(),
        ]);

        if (!$guardarCertificado) {
            return response()->json(['mensaje' => 'Error al guardar el archivo del certificado de estudios'], 500);
        }

        return response()->json([
            'mensaje' => 'Documentación registrada correctamente',
        ], 201);
    }

    public function listarCursosMatricula(Request $request)
    {
        $idUsuario = $request->input('idUsuario'); 
        $idCarrera = $request->input('idCarrera');

        $cursos = CarreraCurso::with(['curso', 'carrera', 'curso.cicloCursos.ciclo'])
            ->where('idCarrera', $idCarrera) 
            ->get()
            ->map(function ($carreraCurso) use ($idUsuario) {
                
                $repitencias = CursoEstudiantes::where('idCurso', $carreraCurso->idCurso)
                    ->where('idUsuario', $idUsuario)
                    ->value('cantidadRepetencias');

                $ciclo = $carreraCurso->curso->cicloCursos->first()?->ciclo->nombreCiclo ?? 'Sin ciclo';

                return [
                    'ciclo' => $ciclo,
                    'carrera' => $carreraCurso->carrera->nombreCarrera,
                    'tipoCurso' => $carreraCurso->tipoCurso,
                    'codigo' => $carreraCurso->curso->codigoCurso,
                    'asignatura' => $carreraCurso->curso->nombreCurso,
                    'creditos' => $carreraCurso->curso->creditos,
                    'repitencias' => $repitencias, 
                ];
            });

        if ($cursos->isEmpty()) {
            return response()->json(['message' => 'No hay cursos disponibles para la carrera especificada.'], 404);
        }

        return response()->json($cursos);
    }

    public function listarSecciones(Request $request)
    {
        $idCurso = $request->input('idCurso');
        
        if (!$idCurso) {
            return response()->json(['message' => 'El campo idCurso es obligatorio.'], 400);
        }
        
        $curso = Curso::with([
            'cursoTipos' => function ($query) use ($idCurso) {
                $query->where('idCurso', $idCurso); 
            },
            'cursoTipos.tipoCurso',
            'cursoTipos.docente.usuario',
            'cursoTipos.cursoHorarios', 
        ])
        ->where('idCurso', $idCurso)
        ->first();
        
        if (!$curso) {
            return response()->json(['message' => 'No se encontró el curso con el ID proporcionado.'], 404);
        }
        
        $data = $curso->cursoTipos->map(function ($cursoTipo) use ($curso) {

            $docente = $cursoTipo->docente->usuario ?? null; 
        
            $fotoDocente = $docente && $docente->foto ? asset('' . $docente->foto) : asset('storage/profiles/default.png');

            $horarios = $cursoTipo->cursoHorarios->map(function ($cursoHorario) {
                return $cursoHorario->dia . ': ' . $cursoHorario->hora_ini . ' - ' . $cursoHorario->hora_fin;
            })->join(', '); 
            
            return [
                'seccion' => $curso->seccion->nombre ?? 'Sin sección',
                'foto' => $fotoDocente,
                'docente' => $docente ? ($docente->nombres . ' ' . $docente->apellidoPaterno) : 'Sin docente',
                'aula' => $cursoTipo->cursoHorarios->pluck('aula')->join(', ') ?? 'Sin aula', 
                'tipo' => $cursoTipo->tipoCurso->nombre ?? 'Sin tipo',
                'horario' => $horarios, 
                'vacantes' => $cursoTipo->cursoHorarios->pluck('vacantes')->join(', '),
                'vacantes_disponibles' => $cursoTipo->cursoHorarios->pluck('vacantes_disponibles')->join(', '),
            ];

        });

        return response()->json($data);
    }

    public function listarHorariosCurso(Request $request)
    {
        $idCurso = $request->input('idCurso'); 

        if (!$idCurso) {
            return response()->json(['message' => 'El campo idCurso es obligatorio.'], 400);
        }

        $curso = Curso::with([
            'cursoTipos' => function ($query) use ($idCurso) {
                $query->where('idCurso', $idCurso); 
            },
            'cursoTipos.tipoCurso',
            'cursoTipos.cursoHorarios', 
        ])
        ->where('idCurso', $idCurso)
        ->first();

        if (!$curso) {
            return response()->json(['message' => 'No se encontró el curso con el ID proporcionado.'], 404);
        }

        $result = $curso->cursoTipos->flatMap(function ($cursoTipo) use ($curso) {
            return $cursoTipo->cursoHorarios->map(function ($horario) use ($curso, $cursoTipo) {
                return [
                    'idCurso' => $curso->idCurso,
                    'idCursoHorario' => $horario->idCursoHorario,
                    'codigoCurso' => $curso->codigoCurso,
                    'nombreCurso' => $curso->nombreCurso,
                    'horaInicio' => $horario->hora_ini,
                    'horaFin' => $horario->hora_fin,
                    'aula' => $horario->aula,  
                    'día' => $horario->dia ?? 'No especificado',
                    'tipoCurso' => $cursoTipo->tipoCurso->nombre ?? 'Sin tipo',
                    'fechaInicio' => $curso->fecha_ini,
                    'fechaFin' => $curso->fecha_fin
                ];
            });
        });

        return response()->json($result);
    }

    public function obtenerEspecialidadUsuario(Request $request)
    {
        $idUsuario = $request->input('idUsuario'); 
    
        if (!$idUsuario) {
            return response()->json(['message' => 'El campo idUsuario es obligatorio.'], 400);
        }
    
        // Obtener las carreras asociadas al usuario
        $carreras = CarreraEstudiantes::with('carrera')
            ->where('idEstudiante', $idUsuario)
            ->get()
            ->map(function ($carreraEstudiante) {
                return [
                    // 'idCarrera' => $carreraEstudiante->carrera->idCarrera,
                    'nombreCarrera' => $carreraEstudiante->carrera->nombreCarrera,
                ];
            });
    
        if ($carreras->isEmpty()) {
            return response()->json(['message' => 'No se encontraron especialidades para el usuario proporcionado.'], 404);
        }
    
        return response()->json($carreras);
    }

    // Funcion para generar la matricula de un estudiante
    public function generarMatricula(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idUsuario' => 'required|integer|exists:usuarios,idUsuario',
            'cursos' => 'required|array',
            'cursos.*.idCursoHorario' => 'required|integer|exists:curso_horario,idCursoHorario',
            'importe' => 'required|numeric|not_in:0',
        ], [
            'idUsuario.required' => 'El ID del usuario es requerido',
            'idUsuario.exists' => 'El ID del usuario no existe',
            'cursos.required' => 'Los cursos son requeridos',
            'cursos.array' => 'Los cursos deben ser un arreglo',
            'cursos.*.idCursoHorario.required' => 'El ID del curso es requerido',
            'cursos.*.idCursoHorario.exists' => 'El curso no existe',
            'importe.required' => 'El importe es requerido',
            'importe.numeric' => 'El importe debe ser un número',
            'importe.not_in' => 'El importe no debe ser 0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $idUsuario = $request->idUsuario;

        // Verificar si el estudiante ya tiene una matricula activa
        $matriculaActiva = Matricula::where('idUsuario', $idUsuario)
            ->where('estado', 'Activa')
            ->first();

        if ($matriculaActiva) {
            return response()->json(['mensaje' => 'El estudiante ya tiene una matricula activa'], 400);
        }

        // Crear la matricula
        $matricula = Matricula::create([
            'idUsuario' => $idUsuario,
            'importe' => $request->importe,
            'estado' => 'pago_pendiente',
            'fechaRegistro' => now(),
        ]);

        if (!$matricula) {
            return response()->json(['mensaje' => 'Error al generar la matricula'], 500);
        }

        // Registrar los cursos de la matricula
        foreach ($request->cursos as $curso) {
            $matriculaCurso = MatriculaCurso::create([
                'idMatricula' => $matricula->idMatricula,
                'idCursoHorario' => $curso['idCursoHorario'],
            ]);

            if (!$matriculaCurso) {
                return response()->json(['mensaje' => 'Error al registrar los cursos de la matricula'], 500);
            }
        }

        return response()->json([
            'mensaje' => 'Matricula generada correctamente',
            'matricula' => $matricula,
        ], 201);
    }

    // Funcion para pagar la matricula de un estudiante
    public function pagarMatricula(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idMatricula' => 'required|integer|exists:matricula,idMatricula',
            'idMetodoPago' => 'required|integer|exists:metodo_pago,idMetodoPago',
            'monto' => 'required|numeric|not_in:0',
        ], [
            'idMatricula.required' => 'El ID de la matricula es requerido',
            'idMatricula.exists' => 'La matricula no existe',
            'idMetodoPago.required' => 'El ID del método de pago es requerido',
            'idMetodoPago.exists' => 'El método de pago seleccionado no existe',
            'monto.required' => 'El monto es requerido',
            'monto.numeric' => 'El monto debe ser un número',
            'monto.not_in' => 'El monto no debe ser 0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $idMatricula = $request->idMatricula;
        $matricula = Matricula::find($idMatricula);

        // Verificar si la matricula ya fue pagada
        if ($matricula->estado === 'pagada') {
            return response()->json(['mensaje' => 'La matricula ya fue pagada'], 400);
        }

        // Verificar que el monto a pagar sea igual al importe de la matricula
        if ($request->monto != $matricula->importe) {
            return response()->json(['mensaje' => 'El monto a pagar no coincide con el importe de la matricula'], 400);
        }

        // Registramos el pago
        $pagoMatricula = Pago::create([
            'idUsuario' => $matricula->idUsuario,
            'idMetodoPago' => $request->idMetodoPago,
            'descripcion' => 'Pago de matricula',
            'importe' => $request->monto,
            'igv' => $request->monto * 0.18,
            'total' => $request->monto * 1.18,
            'fechaPago' => now(),
        ]);

        if (!$pagoMatricula) {
            return response()->json(['mensaje' => 'Error al registrar el pago de la matricula'], 500);
        }

        // Relacionar el pago con la matricula
        $matriculaPago = MatriculaPagos::create([
            'idMatricula' => $idMatricula,
            'idPago' => $pagoMatricula->idPago,
        ]);

        if (!$matriculaPago) {
            return response()->json(['mensaje' => 'Error al registrar el pago de la matricula'], 500);
        }

        // Actualizar el estado de la matricula
        $matricula->estado = 'pagada';
        $matricula->save();

        // Registramos al estudiante en los cursos
        $matriculaCursos = MatriculaCurso::where('idMatricula', $idMatricula)->get();

        foreach ($matriculaCursos as $curso) {

            $cursoHorarioEstudiante = CursoHorarioEstudiantes::create([
                'idCursoHorario' => $curso->idCursoHorario,
                'idUsuario' => $matricula->idUsuario,
            ]);
            
            if (!$cursoHorarioEstudiante) {
                return response()->json(['mensaje' => 'Error al registrar al estudiante en los cursos'], 500);
            }

            // Si el estudiante no esta registrado en el curso, lo registramos
            $cursoEstudiante = CursoEstudiantes::where('idCurso', $curso->idCurso)
                ->where('idUsuario', $matricula->idUsuario)
                ->first();

            if (!$cursoEstudiante) {

                $cursoEstudiante = CursoEstudiantes::create([
                    'idCurso' => $curso->idCurso,
                    'idUsuario' => $matricula->idUsuario,
                ]);

                if (!$cursoEstudiante) {
                    return response()->json(['mensaje' => 'Error al registrar al estudiante en el curso'], 500);
                }

            }
            
            // Actualizar las vacantes del curso
            $cursoHorario = CursoHorario::find($curso->idCursoHorario);

            if ($cursoHorario->vacantes_disponibles < 0) {
                return response()->json(['mensaje' => 'No hay vacantes disponibles en el curso'], 400);
            } else {
                $cursoHorario->vacantes_disponibles -= 1;
                $cursoHorario->save();
            }

        }

        return response()->json([
            'mensaje' => 'Matricula pagada correctamente',
            'matricula' => $matricula,
        ], 200);
    }

    // Funcion para generar el reporte de la matricula (pago y cursos)
    public function reporteMatricula(Request $request)
    {
        $idMatricula = $request->input('idMatricula');

        $validator = Validator::make($request->all(), [
            'idMatricula' => 'required|integer|exists:matricula,idMatricula',
        ], [
            'idMatricula.required' => 'El ID de la matrícula es requerido',
            'idMatricula.exists' => 'La matrícula no existe',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $pagoMatricula = MatriculaPagos::where('idMatricula', $idMatricula)
            ->join('pago', 'matricula_pagos.idPago', '=', 'pago.idPago')
            ->join('metodo_pago', 'pago.idMetodoPago', '=', 'metodo_pago.idMetodoPago')
            ->get(['descripcion', 'metodo_pago.nombre as metodoPago', 'importe', 'igv', 'total', 'fechaPago'])
            ->first();

        $cursosMatricula = MatriculaCurso::with(['cursoHorario.cursoTipo', 'cursoHorario.cursoDocentes'])
            ->where('idMatricula', $idMatricula)
            ->get()
            ->map(function ($matriculaCurso) {

                $docente = $matriculaCurso->cursoHorario->cursoDocentes->first()?->usuario ?? null;

                return [
                    'nombreCurso' => $matriculaCurso->cursoHorario->cursoTipo->curso->nombreCurso,
                    'docente' => $docente ? ($docente->nombres . ' ' . $docente->apellidoPaterno . ' ' . $docente->apellidoMaterno) : 'Sin docente',
                    'dia' => $matriculaCurso->cursoHorario->dia,
                    'horaInicio' => $matriculaCurso->cursoHorario->hora_ini,
                    'horaFin' => $matriculaCurso->cursoHorario->hora_fin,
                ];
            });

        return response()->json([
            'pago' => $pagoMatricula,
            'cursos' => $cursosMatricula,
        ]);
    }

    // Funcion para descargar el reporte de cursos matriculados en PDF
    public function descargarCursosMatriculados(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idMatricula' => 'required|integer|exists:matricula,idMatricula',
        ], [
            'idMatricula.required' => 'El ID de la matrícula es requerido',
            'idMatricula.exists' => 'La matrícula no existe',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $idMatricula = $request->input('idMatricula');

        // Obtener los cursos matriculados
        $cursosMatricula = MatriculaCurso::with(['cursoHorario.cursoTipo', 'cursoHorario.cursoDocentes'])
            ->where('idMatricula', $idMatricula)
            ->get()
            ->map(function ($matriculaCurso) {

                $docente = $matriculaCurso->cursoHorario->cursoDocentes->first()?->usuario ?? null;

                return [
                    'nombreCurso' => $matriculaCurso->cursoHorario->cursoTipo->curso->nombreCurso,
                    'docente' => $docente ? ($docente->nombres . ' ' . $docente->apellidoPaterno . ' ' . $docente->apellidoMaterno) : 'Sin docente',
                    'dia' => $matriculaCurso->cursoHorario->dia,
                    'horaInicio' => $matriculaCurso->cursoHorario->hora_ini,
                    'horaFin' => $matriculaCurso->cursoHorario->hora_fin,
                ];
            });

        // Obtener los datos del estudiante
        $matricula = Matricula::find($idMatricula);
        $idUsuario = $matricula->idUsuario;
        $estudiante = Usuario::find($idUsuario);
        $nombreEstudiante = $estudiante->nombres . ' ' . $estudiante->apellidoPaterno . ' ' . $estudiante->apellidoMaterno;

        // Crear el PDF con los cursos
        $pdf = app(PDF::class)->loadView(
            'exports.cursos-matriculados', // Vista que se generará para el PDF
            [
                'cursos' => $cursosMatricula, 
                'estudiante' => $nombreEstudiante
            ]
        );

        // Descargar el PDF
        return $pdf->download('cursos_matriculados.pdf');
    }

    // Funcion para descargar el pago de la matricula
    public function descargarPagoMatricula(Request $request)
    {
        $idMatricula = $request->input('idMatricula');

        $validator = Validator::make($request->all(), [
            'idMatricula' => 'required|integer|exists:matricula,idMatricula',
        ], [
            'idMatricula.required' => 'El ID de la matrícula es requerido',
            'idMatricula.exists' => 'La matrícula no existe',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Obtener los datos del estudiante
        $matricula = Matricula::find($idMatricula);
        $idUsuario = $matricula->idUsuario;
        $estudiante = Usuario::find($idUsuario);
        $nombreEstudiante = $estudiante->nombres . ' ' . $estudiante->apellidoPaterno . ' ' . $estudiante->apellidoMaterno;

        // Obtener los datos del pago de la matricula
        $pagoMatricula = MatriculaPagos::where('idMatricula', $idMatricula)
            ->join('pago', 'matricula_pagos.idPago', '=', 'pago.idPago')
            ->join('metodo_pago', 'pago.idMetodoPago', '=', 'metodo_pago.idMetodoPago')
            ->get(['descripcion', 'metodo_pago.nombre as metodoPago', 'importe', 'igv', 'total', 'fechaPago'])
            ->first();
        
        $pdf = app(PDF::class)->loadView(
            'exports.pago-matricula', // Nombre de la vista
            ['pago' => $pagoMatricula, 'estudiante' => $nombreEstudiante] // Datos a enviar a la vista
        );

        return $pdf->download('pago_matricula.pdf');
    }

    public function obtenerSeccionesPorAnho($idAnho)
    {
        // Obtener las secciones para el año a consultar
        $secciones = Curso::where('idAnho', $idAnho)
            ->with([
                'seccion',       // Relación con la tabla Sección
                'cursoDocentes', // Relación con los docentes asignados
                'cursoHorarios', // Relación con los horarios del curso
            ])
            ->get();

        if ($secciones->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No se encontraron secciones para este año.'], 404);
        }

        
        $resultado = $secciones->map(function ($curso) {
            // Obtener la lista de docentes
            $docenteNombre = $curso->cursoDocentes->map(function ($cursoDocente) {
                return $cursoDocente->usuario->nombre; 
            })->implode(', ');

            // Obtener la descripción de los horarios
            $horarioDescripcion = $curso->cursoHorarios->map(function ($horario) {
                return $horario->dia . ' ' . $horario->hora_ini . ' - ' . $horario->hora_fin;
            })->implode(', ');

            // Contar las vacantes
            $cantVacantes = $curso->cursoHorarios->sum('vacantes');
            $cantDisponibles = $curso->cursoHorarios->sum('vacantes_disponibles');

            return [
                'seccion_nombre' => $curso->seccion->nombre,
                'docente_nombre' => $docenteNombre,
                'horario_descripcion' => $horarioDescripcion,
                'cant_vacantes' => $cantVacantes,
                'cant_disponibles' => $cantDisponibles,
            ];
        });

        return response()->json(['success' => true, 'secciones' => $resultado]);
    }

}
