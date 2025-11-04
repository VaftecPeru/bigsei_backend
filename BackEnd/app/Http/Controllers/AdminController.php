<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\UsuarioRol;
use App\Models\Curso;
use App\Models\Modalidad;
use App\Models\Periodo;
use App\Models\Ciclo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\CursoAsistencia;
use App\Models\EvaluacionesNotas;
use App\Models\Pago;
use Barryvdh\DomPDF\PDF;

class AdminController extends Controller
{

    //FUNCION PARA REPORTE DE CALIFICACIONES
    public function descargarReporte($idCursoEvaluacion)
    {
        // Obtener las notas con relaciones
        $notas = EvaluacionesNotas::with(['usuario', 'cursoEvaluacion'])
            ->where('idCursoEvaluacion', $idCursoEvaluacion)
            ->orderBy('nota', 'desc')
            ->get();

        // Verificar si hay datos
        if ($notas->isEmpty()) {
            return back()->with('error', 'No hay calificaciones para mostrar.');
        }

        // Obtener información de la evaluación
        $evaluacion = $notas->first()->cursoEvaluacion;

        // Generar el PDF
        $pdf = app(PDF::class)->loadView('expors.reporte-calificaciones', [
            'notas' => $notas,
            'evaluacion' => $evaluacion
        ]);

        // Nombre del archivo
        $filename = 'reporte_calificaciones_' . $evaluacion->nombre . '.pdf';

        // Descargar el PDF
        return $pdf->download($filename);
    }

    //FUNCION PARA REPORTE DE PAGOS
    public function generarReportePago(Request $request)
    {
        // Validar los parámetros de filtrado
        $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'idNivel' => 'nullable|integer',
            'idGrado' => 'nullable|integer',
            'idMetodoPago' => 'nullable|integer'
        ]);

        // Construir la consulta con relaciones
        $query = Pago::with(['usuario', 'metodoPago', 'nivel', 'grado']);

        // Aplicar filtros
        if ($request->fecha_inicio) {
            $query->where('fechaPago', '>=', $request->fecha_inicio);
        }

        if ($request->fecha_fin) {
            $query->where('fechaPago', '<=', $request->fecha_fin);
        }

        if ($request->idNivel) {
            $query->where('idNivel', $request->idNivel);
        }

        if ($request->idGrado) {
            $query->where('idGrado', $request->idGrado);
        }

        if ($request->idMetodoPago) {
            $query->where('idMetodoPago', $request->idMetodoPago);
        }

        // Ordenar por fecha de pago descendente
        $pagos = $query->orderBy('fechaPago', 'desc')->get();

        // Calcular totales
        $totalImporte = $pagos->sum('importe');
        $totalIgv = $pagos->sum('igv');
        $totalGeneral = $pagos->sum('total');

        // Generar el PDF
        $pdf = app(PDF::class)->loadView('exports.reporte-pagos', [
            'pagos' => $pagos,
            'filtros' => $request->all(),
            'totalImporte' => $totalImporte,
            'totalIgv' => $totalIgv,
            'totalGeneral' => $totalGeneral
        ]);

        // Nombre del archivo
        $filename = 'reporte_pagos_' . now()->format('Ymd_His') . '.pdf';

        // Opciones: download (descarga) o stream (visualiza en navegador)
        return $pdf->download($filename);
    }

    // FUNCION PARA REGISTRAR UN USUARIO
    public function registrarUsuario(Request $request, $tipoUsuario = null)
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
            'username' => 'required|string|max:60|unique:usuarios',
            'password' => 'required|string|min:6|confirmed',
            'idRol' => 'required_if:tipoUsuario,null|exists:rol,idRol',
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
            'username.required' => 'El nombre de usuario es requerido',
            'username.unique' => 'El nombre de usuario ya existe',
            'password.required' => 'La contraseña es requerida',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'idRol.required_if' => 'No se ha seleccionado un rol',
            'idRol.exists' => 'El rol no existe',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = Usuario::create([
            'dni' => $request->dni,
            'nombres' => $request->nombres,
            'apellidoPaterno' => $request->apellidoPaterno,
            'apellidoMaterno' => $request->apellidoMaterno,
            'fechaNacimiento' => $request->fechaNacimiento,
            'genero' => $request->genero,
            'telefono' => $request->telefono,
            'correo' => $request->correo,
            'direccion' => $request->direccion,
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'estado' => 'loggedOff',
            'fechaRegistro' => now(),
        ]);

        $idUsuario = $user->idUsuario;
        $idRol = 0;

        if ($tipoUsuario) {
            switch ($tipoUsuario) {
                case 'estudiante':
                    $idRol = 4;
                    break;
                case 'docente':
                    $idRol = 5;
                    break;
                default:
                    return response()->json(['mensaje' => 'Tipo de usuario no válido'], 400);
            }
        } else {
            $idRol = $request->idRol;
        }

        $asignarRol = UsuarioRol::create([
            'idUsuario' => $idUsuario,
            'idRol' => $idRol,
        ]);

        if (!$asignarRol) {
            return response()->json(['mensaje' => 'Error al asignar rol'], 500);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'), 201);
    }

    // FUNCION PARA AGREGAR ROL
    public function agregarRol(Request $request)
    {
        // Validar que el campo 'nombreRol' esté presente
        $validator = Validator::make($request->all(), [
            'nombreRol' => 'required|string|max:50|unique:rol,nombreRol',  // Asegurar que el rol sea único
        ], [
            'nombreRol.required' => 'El nombre del rol es requerido',
            'nombreRol.unique' => 'El rol ya existe',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Crear el nuevo rol
        $rol = Rol::create([
            'nombreRol' => $request->nombreRol,
        ]);

        // Retornar la respuesta de éxito
        return response()->json(['mensaje' => 'Rol creado exitosamente', 'rol' => $rol], 201);
    }

    // FUNCION PARA LISTAR ROLES
    public function listarRoles()
    {
        $roles = Rol::whereNotIn('nombreRol', ['admin', 'estudiante', 'docente'])
            ->select('idRol', 'nombreRol')
            ->get();

        return response()->json([
            'mensaje' => 'Roles listados exitosamente',
            'roles' => $roles
        ], 200);
    }

    // FUNCION PARA AGREGAR MODALIDAD
    public function agregarModalidad(Request $request)
    {
        // Validar la entrada
        $validator = Validator::make($request->all(), [
            'nombreModalidad' => 'required|string|max:50|unique:modalidad,nombreModalidad',  // Asegurar que la modalidad sea única
        ], [
            'nombreModalidad.required' => 'El nombre de la modalidad es requerido',
            'nombreModalidad.unique' => 'La modalidad ingresada ya existe',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Crear la nueva modalidad
        $modalidad = Modalidad::create([
            'nombreModalidad' => $request->nombreModalidad,
        ]);

        // Retornar la respuesta de éxito
        return response()->json([
            'mensaje' => 'Modalidad agregada exitosamente',
            'modalidad' => $modalidad
        ], 201);
    }

    // FUNCION PARA AGREGAR CURSO
    public function agregarCurso(Request $request)
    {
        // Validación de datos
        $validator = Validator::make($request->all(), [
            'idSeccion' => 'required|exists:seccion,idSeccion',
            'idModalidad' => 'required|exists:modalidad,idModalidad',
            'codigoCurso' => 'required|string|max:255',
            'nombreCurso' => 'required|string|max:255',
            'creditos' => 'required|integer',
            'vacantes' => 'required|integer',
            'fecha_ini' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_ini',
        ], [
            'idSeccion.required' => 'No se ha seleccionado una sección',
            'idSeccion.exists' => 'La sección no existe',
            'idModalidad.required' => 'No se ha seleccionado una modalidad',
            'idModalidad.exists' => 'La modalidad no existe',
            'codigoCurso.required' => 'El codigo del curso es requerido',
            'nombreCurso.required' => 'El nombre del curso es requerido',
            'creditos.required' => 'Los créditos son requeridos',
            'vacantes.required' => 'La cantidad de vacantes es requerida',
            'fecha_ini.required' => 'La fecha de inicio es requerida',
            'fecha_fin.required' => 'La fecha de fin es requerida',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Crear un nuevo curso
        $curso = Curso::create([
            'idSeccion' => $request->input('idSeccion'),
            'idModalidad' => $request->input('idModalidad'),
            'nombreCurso' => $request->input('nombreCurso'),
            'creditos' => $request->input('creditos'),
            'vacantes' => $request->input('vacantes'),
            'fecha_ini' => $request->input('fecha_ini'),
            'fecha_fin' => $request->input('fecha_fin'),
        ]);

        if (!$curso) {
            return response()->json(['mensaje' => 'Error al registrar el curso'], 500);
        }

        return response()->json([
            'message' => 'Curso agregado exitosamente',
            'curso' => $curso
        ], 201);
    }

    // FUNCION PARA AGREGAR PERIODO A TABLA PERIODO
    public function agregarPeriodo(Request $request)
    {
        $validator  = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100|unique:periodo,nombre', // Asegurar que no se repita el nombre
            'descripcion' => 'nullable|string|max:300',
            'fechaINI' => 'required|date',
            'fechaFIN' => 'required|date|after:fechaINI', // Validar fecha de fin debe ser después a la de inicio
        ], [
            'nombre.required' => 'El nombre del periodo es requerido',
            'nombre.unique' => 'El nombre del periodo ya existe',
            'fechaINI.required' => 'La fecha de inicio es requerida',
            'fechaFIN.required' => 'La fecha de fin es requerida',
            'fechaFIN.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $periodo = Periodo::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'fechaINI' => $request->fechaINI,
            'fechaFIN' => $request->fechaFIN,
        ]);

        return response()->json([
            'mensaje' => 'Periodo agregado exitosamente',
            'periodo' => $periodo
        ], 201);
    }

    // FUNCION PARA AGREGAR CICLO A TABLA CICLO
    public function agregarCiclo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idPeriodo' => 'required|exists:periodo,idPeriodo', // Asegurarse que el periodo exista
            'nombreCiclo' => 'required|string|max:100', // Validar nombreCiclo
        ], [
            'idPeriodo.required' => 'No se ha seleccionado un periodo',
            'idPeriodo.exists' => 'El periodo no existe',
            'nombreCiclo.required' => 'El nombre del ciclo es requerido',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $ciclo = Ciclo::create([
            'idPeriodo' => $request->idPeriodo,
            'nombreCiclo' => $request->nombreCiclo,
        ]);

        return response()->json(['mensaje' => 'Ciclo agregado exitosamente', 'ciclo' => $ciclo], 201);
    }

    public function generarPdfCursos($cursos)
    {
        $pdf = app(PDF::class)->loadView('exports.lista_cursos', [
            'cursos' => $cursos,
            'titulo' => 'Reporte de Cursos',
            'fecha' => now()->format('d/m/Y H:i')
        ]);

        return $pdf->download('reporte_cursos.pdf');
    }

    // FUNCIÓN PARA GENERAR PDF DE CICLOS
    public function generarPdfCiclos($ciclos)
    {
        $pdf = app(PDF::class)->loadView('exports.lista_ciclos', [
            'ciclos' => $ciclos,
            'titulo' => 'Reporte de Ciclos Académicos',
            'fecha' => now()->format('d/m/Y H:i')
        ]);

        return $pdf->download('reporte_ciclos.pdf');
    }

    // FUNCIÓN PARA GENERAR PDF DE PERIODOS
    public function generarPdfPeriodos($periodos)
    {
        $pdf = app(PDF::class)->loadView('exports.lista_periodos', [
            'periodos' => $periodos,
            'titulo' => 'Reporte de Periodos Académicos',
            'fecha' => now()->format('d/m/Y H:i')
        ]);

        return $pdf->download('reporte_periodos.pdf');
    }

    // FUNCION PARA LISTAR CURSO
    public function listarCursos(Request $request)
    {
        $cursos = Curso::select('idCurso', 'nombreCurso')->get();

        return response()->json([
            'mensaje' => 'Cursos listados exitosamente',
            'cursos' => $cursos
        ], 200);
    }

    // FUNCION PARA LISTAR CICLOS
    public function listarCiclos(Request $request)
    {

        $ciclos = Ciclo::with('periodo:idPeriodo,nombre')
            ->select('idCiclo', 'nombreCiclo', 'idPeriodo')
            ->get()
            ->map(function ($ciclo) {
                return [
                    'idCiclo' => $ciclo->idCiclo,
                    'nombreCiclo' => $ciclo->nombreCiclo,
                    'nombre' => $ciclo->periodo->nombre,
                ];
            });

        return response()->json([
            'mensaje' => 'Ciclos listados exitosamente',
            'ciclos' => $ciclos
        ], 200);
    }

    // FUNCION PARA LISTAR PERIODOS
    public function listarPeriodos(Request $request)
    {
        $periodos = Periodo::select('idPeriodo', 'nombre')->get();

        return response()->json([
            'mensaje' => 'Periodos listados exitosamente',
            'periodos' => $periodos
        ], 200);
    }

    // FUNCION PARA LISTAR MODALIDADES
    public function listarModalidad(Request $request)
    {
        $modalidades = Modalidad::select('idModalidad', 'nombreModalidad')->get();

        return response()->json([
            'mensaje' => 'Modalidades listados exitosamente',
            'modalidades' => $modalidades
        ], 200);
    }


    // FUNCION PARA LISTAR DOCENTES
    public function listarDocentes(Request $request)
    {
        $docentes = Usuario::whereHas('roles', function ($query) {
            $query->where('nombreRol', 'docente');
        })
            ->select('idUsuario', 'nombre')
            ->get();


        return response()->json($docentes);
    }

    // FUNCION PARA DESCARGAR LISTADO DE DOCENTES
    public function descargarListaDocentes(Request $request)
    {
        $docentes = Usuario::whereHas('roles', function ($query) {
            $query->where('nombreRol', 'docente');
        })
            ->select('idUsuario', 'nombre')
            ->get();

        // Datos para la vista
        $data = [
            'docentes' => $docentes,
            'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
            'titulo' => 'Listado de Docentes'
        ];

        // Generar el PDF
        $pdf = app(PDF::class)->loadView('exports.lista_docentes', $data);

        // Descargar el PDF con un nombre específico
        return $pdf->download('lista_docentes.pdf');
    }

    // FUNCION PARA DESCARGAR LISTADO DE ESTUDIANTES
    public function descargarListaEstudiantes(Request $request)
    {
        $estudiantes = Usuario::whereHas('roles', function ($query) {
            $query->where('nombreRol', 'estudiante');
        })
            ->select('idUsuario', 'nombre')
            ->get();

        // Datos para la vista
        $data = [
            'estudiantes' => $estudiantes,
            'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
            'titulo' => 'Listado de Estudiantes'
        ];

        // Generar el PDF
        $pdf = app(PDF::class)->loadView('exports.lista_estudiantes', $data);

        // Descargar el PDF con un nombre específico
        return $pdf->download('lista_estudiantes.pdf');
    }

    public function descargarListaUsuarios(Request $request)
    {
        $usuarios = Usuario::with('roles')
            ->select('idUsuario', 'nombre', 'email')
            ->get();

        // Datos para la vista
        $data = [
            'usuarios' => $usuarios,
            'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
            'titulo' => 'Listado General de Usuarios'
        ];

        // Generar el PDF
        $pdf = app(PDF::class)->loadView('exports.lista_usuarios', $data);

        // Descargar el PDF
        return $pdf->download('lista_usuarios.pdf');
    }

    //FUNCION PARA HACER REPORTE DE ASISTENCIA POR CURSO
    public function generarReporte(Request $request)
    {
        $periodo = $request->input('periodo', 'Periodo no especificado');
        $ciclo = $request->input('ciclo', 'Ciclo no especificado');
        $curso = $request->input('curso', 'Curso no especificado');

        $estadisticas = $this->calcularEstadisticasAsistencia($periodo, $ciclo, $curso);

        $data = [
            'periodo' => $periodo,
            'ciclo' => $ciclo,
            'curso' => $curso,
            'porcentajeAsistencia' => $estadisticas['porcentaje_asistencia'],
            'porcentajeInasistencia' => $estadisticas['porcentaje_inasistencia'],
            'variacionAsistencia' => $estadisticas['variacion_asistencia'],
            'variacionInasistencia' => $estadisticas['variacion_inasistencia'],
            'fechaGeneracion' => now()->format('d/m/Y H:i:s')
        ];

        // Generar el PDF
        $pdf = app(PDF::class)->loadView('exports.reportes-asistencia-curso', $data);

        // Opciones del PDF
        return $pdf->download('reporte_asistencia.pdf');
    }

    protected function calcularEstadisticasAsistencia($periodo, $ciclo, $curso)
    {
        $totalRegistros = CursoAsistencia::whereHas('cursoHorario', function ($query) use ($curso) {
            $query->where('idCurso', $curso);
        })->count();

        $asistencias = CursoAsistencia::whereHas('cursoHorario', function ($query) use ($curso) {
            $query->where('idCurso', $curso);
        })->where('estado', 'presente')->count();

        $porcentajeAsistencia = $totalRegistros > 0 ? ($asistencias / $totalRegistros) * 100 : 0;
        $porcentajeInasistencia = $totalRegistros > 0 ? 100 - $porcentajeAsistencia : 0;

        $variacionAsistencia = 2.5;
        $variacionInasistencia = -1.2;

        return [
            'porcentaje_asistencia' => round($porcentajeAsistencia, 2),
            'porcentaje_inasistencia' => round($porcentajeInasistencia, 2),
            'variacion_asistencia' => $variacionAsistencia,
            'variacion_inasistencia' => $variacionInasistencia
        ];
    }
}
