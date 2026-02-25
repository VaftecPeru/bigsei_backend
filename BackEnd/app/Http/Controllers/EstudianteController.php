<?php

namespace App\Http\Controllers;

use App\Models\CarreraEstudiantes;
use App\Models\DocumentosUsuario;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Curso;
use App\Models\CursoAsistencia;
use App\Models\CursoEstudiantes;
use App\Models\CursoHorarioEstudiantes;
use App\Models\Estudiante;
use App\Models\Pago;
use App\Models\PlanEstudio;
use App\Models\Usuario;
use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EstudianteController extends Controller
{

    public function subirDocumentos(Request $request)
    {
        // Verificación de tamaño 
        if ($request->file('documento')->getSize() > 10 * 1024 * 1024) { // 10 MB en bytes
            return response()->json([
                'success' => false,
                'message' => 'El tamaño del archivo es demasiado grande. El límite es de 10 MB.',
            ], 400);
        }

        // Validación de Laravel
        $request->validate([
            'idUsuario' => 'required|integer|exists:usuario,id_usuario',
            'documento' => 'required|file|max:10240', // Límite en el servidor, máximo 10 MB
        ]);

        // Crear la carpeta de usuario en 'documentos/idUsuario'
        $userFolder = 'documentos/' . $request->input('idUsuario');

        // Guardar el archivo en la carpeta del usuario con un nombre único
        $uniqueName = time() . '_' . $request->file('documento')->getClientOriginalName();
        $path = $request->file('documento')->storeAs($userFolder, $uniqueName);

        // Crear el registro en la base de datos
        $documento = DocumentosUsuario::create([
            'idUsuario' => $request->input('idUsuario'),
            'nombreArchivo' => $request->file('documento')->getClientOriginalName(),
            'rutaArchivo' => $path,
            'tipoArchivo' => $request->file('documento')->getClientOriginalExtension(),
            'fechaSubida' => Carbon::now()->format('Y-m-d'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Documento subido exitosamente.',
            'data' => $documento,
        ], 201);
    }

    public function obtenerHorarioYDocentesPorCurso($idCurso)
    {

        $result = DB::table('horarios_curso as h')
            ->join('registrodocentecurso as rdc', 'h.idCurso', '=', 'rdc.idCurso')
            ->join('usuarios as u', 'rdc.idUsuario', '=', 'u.idUsuario')
            ->where('h.idCurso', $idCurso)
            ->select('h.dia', 'h.hora_ini_teorica', 'h.hora_fin_practica', 'h.fecha_ini', 'h.fecha_fin', 'u.nombre as docente_nombre', 'u.foto as docente_foto')
            ->get();

        return response()->json($result);
    }


    //APIS ESTUDIANTE REGULAR
    //===================================================================================
    public function obtenerCursoPorHoraActual()
    {
        // Mapeo de días de la semana en español
        $diaSemana = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo'
        ];

        // Obtener el nombre del día actual en español
        $diaHoy = $diaSemana[date('l')] ?? 'Domingo';

        // Obtener la hora actual en formato de 24 horas
        $horaActual = now()->format('H:i:s');

        // Obtener los cursos que están ocurriendo actualmente
        $cursos = Curso::join('curso_tipo as ct', 'curso.idCurso', '=', 'ct.idCurso')
            ->join('curso_horario as ch', 'ct.idTipoCurso', '=', 'ch.idCursoTipo')
            ->join('tipo_curso as tc', 'ct.idTipoCurso', '=', 'tc.idTipoCurso')
            ->where('ch.dia', $diaHoy) // Filtrar por el día actual
            ->whereRaw("TIME(NOW()) BETWEEN ch.hora_ini AND ch.hora_fin") // Filtrar por la hora actual
            ->select('curso.idCurso', 'curso.nombreCurso', 'ch.aula', 'ch.dia', 'ch.hora_ini', 'ch.hora_fin', 'tc.nombre as tipoCurso')
            ->get();

        // Crear un arreglo para almacenar los resultados formateados
        $resultados = [];

        // Iterar sobre los cursos y sus horarios
        foreach ($cursos as $curso) {
            $resultados[] = [
                'idCurso' => $curso->idCurso,
                'nombreCurso' => $curso->nombreCurso,
                'aula' => $curso->aula,
                'dia' => $curso->dia,
                'hora_ini' => $curso->hora_ini,
                'hora_fin' => $curso->hora_fin,
                'tipoCurso' => $curso->tipoCurso,
            ];
        }

        // Retornar la respuesta en formato JSON
        return response()->json($resultados);
    }


    public function listarHorariosCurso(Request $request)
    {
        // Obtener el idUsuario desde el request
        $idUsuario = $request->input('idUsuario');

        if (!$idUsuario) {
            return response()->json(['message' => 'El campo idUsuario es obligatorio.'], 400);
        }

        // Obtener los cursos del estudiante con el idUsuario
        $cursos = Curso::with([
            'cursoTipos' => function ($query) use ($idUsuario) {
                // Filtrar por los cursos en los que el estudiante está matriculado
                $query->whereHas('cursoHorarios.cursoEstudiantes', function ($query) use ($idUsuario) {
                    $query->where('idUsuario', $idUsuario);
                });
            },
            'cursoTipos.tipoCurso',
            'cursoTipos.docente.usuario', // Obtener los datos del docente
            'cursoTipos.cursoHorarios',   // Obtener los horarios del curso
        ])
            ->get();

        // Verificar si existen cursos
        if ($cursos->isEmpty()) {
            return response()->json(['message' => 'No se encontraron cursos para el usuario proporcionado.'], 404);
        }

        // Arreglo para almacenar los resultados
        $resultados = [];

        // Agrupar los cursos por idCurso
        foreach ($cursos as $curso) {
            $cursoData = [
                'idCurso' => $curso->idCurso,
                'codigoCurso' => $curso->codigoCurso,
                'nombreCurso' => $curso->nombreCurso,
                'seccion' => $curso->seccion->nombre ?? 'Sin sección',
                'docente' => '',
                'aula' => '',
                'tipoCurso' => '',
                'horario' => ''
            ];

            // Arrays para almacenar los horarios y las aulas
            $horarios = [];
            $aulas = [];
            $tiposCurso = [];

            foreach ($curso->cursoTipos as $cursoTipo) {
                // Obtener los datos del docente
                $docente = $cursoTipo->docente->usuario ?? null;
                $cursoData['docente'] = $docente ? ($docente->nombres . ' ' . $docente->apellidoPaterno) : 'Sin docente';

                // Agrupar los horarios de tipo teórico y práctico
                foreach ($cursoTipo->cursoHorarios as $cursoHorario) {
                    $horarios[] = $cursoHorario->dia . ': ' . $cursoHorario->hora_ini . ' - ' . $cursoHorario->hora_fin;
                    $aulas[] = $cursoHorario->aula;
                }

                // Tipo (teórico o práctico)
                $tiposCurso[] = $cursoTipo->tipoCurso->nombre ?? 'Sin tipo';
            }

            // Convertir los arrays a cadenas
            $cursoData['horario'] = implode(', ', $horarios);  // Concatenar horarios
            $cursoData['aula'] = implode(', ', $aulas);  // Concatenar aulas
            $cursoData['tipoCurso'] = implode(', ', $tiposCurso);  // Concatenar tipo de curso

            // Agregar la información agrupada al arreglo de resultados
            $resultados[] = $cursoData;
        }

        // Retornar la respuesta en formato JSON
        return response()->json($resultados);
    }

    public function obtenerPromedioPorCurso(Request $request)
    {
        // Obtener el idUsuario desde el request
        $idUsuario = $request->input('idUsuario');

        if (!$idUsuario) {
            return response()->json(['message' => 'El campo idUsuario es obligatorio.'], 400);
        }

        // Obtener los cursos del estudiante con el idUsuario
        $cursos = Curso::with([
            'cursoTipos' => function ($query) use ($idUsuario) {
                // Filtrar por los cursos en los que el estudiante está matriculado
                $query->whereHas('cursoHorarios.cursoEstudiantes', function ($query) use ($idUsuario) {
                    $query->where('idUsuario', $idUsuario);
                });
            },
            'cursoTipos.tareas.tareasAlumnos' => function ($query) use ($idUsuario) {
                // Relacionar las tareas del curso con el estudiante
                $query->where('idUsuario', $idUsuario);
            }
        ])
            ->get();

        // Verificar si existen cursos
        if ($cursos->isEmpty()) {
            return response()->json(['message' => 'No se encontraron cursos para el usuario proporcionado.'], 404);
        }


        // Agrupar los cursos por idCurso
        foreach ($cursos as $curso) {
            $cursoData = [
                'idCurso' => $curso->idCurso,
                'nombreCurso' => $curso->nombreCurso,
                'promedio' => 0  // Inicializamos el promedio como 0
            ];

            // Variables para el cálculo del promedio de notas
            $notasTotales = 0;
            $cantidadTareas = 0;

            foreach ($curso->cursoTipos as $cursoTipo) {
                // Obtener las tareas y calcular el promedio de notas
                foreach ($cursoTipo->tareas as $tarea) {
                    $tareaAlumno = $tarea->tareasAlumnos->where('idUsuario', $idUsuario)->first();
                    if ($tareaAlumno) {
                        $notasTotales += $tareaAlumno->nota;
                        $cantidadTareas++;
                    }
                }
            }

            // Calcular el promedio de las tareas
            if ($cantidadTareas > 0) {
                $cursoData['promedio'] = round($notasTotales / $cantidadTareas, 2); // Promedio redondeado
            }

            // Agregar la información del curso al arreglo de resultados
            $resultados[] = $cursoData;
        }

        // Retornar la respuesta en formato JSON con los cursos y su promedio
        return response()->json($resultados);
    }

    public function listarAsistenciaPorCurso(Request $request)
    {
        $idUsuario = $request->input('idUsuario');
        $idCursoHorario = $request->input('idCursoHorario');

        $validator = Validator::make($request->all(), [
            'idUsuario' => 'required|integer|exists:usuario,id_usuario',
            'idCursoHorario' => 'required|integer|exists:curso_horario,idCursoHorario',
        ], [
            'idUsuario.required' => 'El ID del estudiante es requerido',
            'idUsuario.exists' => 'El estudiante no existe',
            'idCursoHorario.required' => 'El ID del curso es requerido',
            'idCursoHorario.exists' => 'El curso no existe',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $asistencias = CursoAsistencia::join('curso_estudiantes', 'curso_asistencia.idCursoEstudiante', '=', 'curso_estudiantes.idCursoEstudiante')
            ->join('curso', 'curso_estudiantes.idCurso', '=', 'curso.idCurso')
            ->join('curso_clases', 'curso.idCurso', '=', 'curso_clases.idCurso')
            ->where('curso_estudiantes.idUsuario', '=', $idUsuario)
            ->where('curso_asistencia.idCursoHorario', '=', $idCursoHorario)
            ->where('curso_asistencia.estado', '=', 'presente')
            ->select(
                'curso.nombreCurso as nombre_curso',
                DB::raw('COUNT(*) as cantidad_asistencias'),
                DB::raw('CONCAT(ROUND(COUNT(*) / curso_clases.totalClases * 100, 1), "%") as porcentaje_asistencia')
            )
            ->groupBy('curso.nombreCurso', 'curso_clases.totalClases')
            ->get();

        return response()->json($asistencias);
    }

    // Funcion para generar el reporte de cursos de un estudiante
    public function reporteCursosEstudiante(Request $request)
    {
        $idUsuario = $request->input('idUsuario');

        $validator = Validator::make($request->all(), [
            'idUsuario' => 'required|integer|exists:usuario,id_usuario',
        ], [
            'idUsuario.required' => 'El ID del estudiante es requerido',
            'idUsuario.exists' => 'El estudiante no existe',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $estudiante = Usuario::find($idUsuario);

        $cursosEstudiante = CursoEstudiantes::with([
            'curso.cursoDocentes.usuario',
            'curso.cursoTipos.cursoHorarios'
        ])
            ->where('idUsuario', $idUsuario)
            ->get()
            ->map(function ($cursoEstudiante) {
                $firstDocente = $cursoEstudiante->curso->cursoDocentes->first();
                $docente = $firstDocente ? $firstDocente->usuario : null;

                return [
                    'nombreCurso' => $cursoEstudiante->curso->nombreCurso,
                    'docente' => $docente ? $docente->nombres . ' ' . $docente->apellidoPaterno . ' ' . $docente->apellidoMaterno : 'Sin docente',
                    'dias' => $cursoEstudiante->curso->cursoTipos->map(function ($cursoTipo) {
                        return $cursoTipo->cursoHorarios->first()->dia;
                    })->unique()->join(' & '),
                ];
            });

        // Para API devuelve JSON
        if ($request->wantsJson()) {
            return response()->json($cursosEstudiante);
        }

        // Para generar PDF
        $pdf = app(PDF::class)->loadView(
            'exports.reporte-cursos-estudiante',
            [
                'estudiante' => $estudiante,
                'cursos' => $cursosEstudiante,
                'fecha' => now()->format('d/m/Y'),
            ]
        );
        return $pdf->download('cursos-estudiante-' . $estudiante->nombre . '.pdf');
    }

    // Funcion para generar el reporte de notas de un estudiante
    public function reporteNotasEstudiante(Request $request)
    {
        $idUsuario = $request->input('idUsuario');

        $validator = Validator::make($request->all(), [
            'idUsuario' => 'required|integer|exists:usuario,id_usuario',
        ], [
            'idUsuario.required' => 'El ID del estudiante es requerido',
            'idUsuario.exists' => 'El estudiante no existe',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $estudiante = Usuario::find($idUsuario);

        $cursosEstudiantePeriodo = CursoEstudiantes::with([
            'curso.cicloCursos.ciclo',
            'curso.cursoEvaluaciones.evaluacion',
            'curso.cursoEvaluaciones.evaluacionesNotas'
        ])
            ->where('idUsuario', $idUsuario)
            ->get()
            ->map(function ($cursoEstudiante) {
                return [
                    'ciclo' => ($cursoEstudiante->curso->cicloCursos->first() && $cursoEstudiante->curso->cicloCursos->first()->ciclo) ? $cursoEstudiante->curso->cicloCursos->first()->ciclo->nombreCiclo : 'Sin ciclo',
                    'cursos' => $cursoEstudiante->curso->cicloCursos->map(function ($cicloCurso) {
                        return [
                            'nombreCurso' => $cicloCurso->curso->nombreCurso,
                            'evaluaciones' => $cicloCurso->curso->cursoEvaluaciones->map(function ($cursoEvaluacion) {
                                return [
                                    'nombre' => $cursoEvaluacion->evaluacion->nombre,
                                    'nota' => $cursoEvaluacion->evaluacionesNotas->first() ? $cursoEvaluacion->evaluacionesNotas->first()->nota : 'Sin nota',
                                    'porcentaje' => $cursoEvaluacion->porcentaje,
                                ];
                            }),
                            'formula' => $cicloCurso->curso->cursoEvaluaciones->map(function ($cursoEvaluacion) {
                                return $cursoEvaluacion->evaluacion->abreviatura . ' (' . $cursoEvaluacion->porcentaje . '%)';
                            })->join(' + '),
                        ];
                    }),
                ];
            });

        // Para API sigue devolviendo JSON
        if ($request->wantsJson()) {
            return response()->json($cursosEstudiantePeriodo);
        }

        // Para generar PDF
        $pdf = app(PDF::class)->loadView(
            'exports.reporte-notas-estudiante',
            [
                'estudiante' => $estudiante,
                'cursos' => $cursosEstudiantePeriodo,
                'fecha' => now()->format('d/m/Y'),
            ]
        );

        return $pdf->download('reporte-notas-' . $estudiante->nombre . '.pdf');
    }

    // Funcion para generar el reporte de horario de un estudiante
    public function reporteHorarioEstudiante(Request $request)
    {
        $idUsuario = $request->input('idUsuario');
        $nroMes = $request->input('nroMes');

        $validator = Validator::make($request->all(), [
            'idUsuario' => 'required|integer|exists:usuario,id_usuario',
            'nroMes' => 'required|integer|between:1,12',
        ], [
            'idUsuario.required' => 'El ID del estudiante es requerido',
            'idUsuario.exists' => 'El estudiante no existe',
            'nroMes.required' => 'El número del mes es requerido',
            'nroMes.between' => 'El número del mes debe estar entre 1 y 12',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $estudiante = Usuario::find($idUsuario);
        $nombreMes = \DateTime::createFromFormat('!m', $nroMes)->format('F');

        $cursosEstudiante = CursoHorarioEstudiantes::with(['cursoHorario.cursoTipo', 'cursoHorario.cursoDocentes'])
            ->where('idUsuario', $idUsuario)
            ->whereHas('cursoHorario.cursoTipo.curso', function ($query) use ($nroMes) {
                $query->whereMonth('fecha_ini', '<=', $nroMes)
                    ->whereMonth('fecha_fin', '>=', $nroMes);
            })
            ->get()
            ->map(function ($cursoEstudiante) {
                return [
                    'nombreCurso' => $cursoEstudiante->cursoHorario->cursoTipo->curso->nombreCurso,
                    'tipoCurso' => $cursoEstudiante->cursoHorario->cursoTipo->tipoCurso->nombre,
                    'dia' => $cursoEstudiante->cursoHorario->dia,
                    'horaInicio' => $cursoEstudiante->cursoHorario->hora_ini,
                    'horaFin' => $cursoEstudiante->cursoHorario->hora_fin,
                ];
            });

        // Si es una solicitud API, devolver JSON
        if ($request->wantsJson()) {
            return response()->json($cursosEstudiante);
        }

        // Para generar PDF
        $pdf = app(PDF::class)->loadView(
            'exports.reporte-notas-estudiante',
            [
                'estudiante' => $estudiante,
                'cursos' => $cursosEstudiante,
                'mes' => $nombreMes,
                'fecha' => now()->format('d/m/Y')
            ]
        );

        return $pdf->download('horario-estudiante-' . $estudiante->nombre . '.pdf');
    }

    // Funcion para generar el reporte de pagos de un estudiante
    public function reportePagosEstudiante(Request $request)
    {
        $idUsuario = $request->input('idUsuario');

        $validator = Validator::make($request->all(), [
            'idUsuario' => 'required|integer|exists:usuario,id_usuario',
        ], [
            'idUsuario.required' => 'El ID del estudiante es requerido',
            'idUsuario.exists' => 'El estudiante no existe',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $estudiante = Usuario::find($idUsuario);

        $pagos = Pago::where('idUsuario', $idUsuario)
            ->join('metodo_pago', 'pago.idMetodoPago', '=', 'metodo_pago.idMetodoPago')
            ->orderBy('fechaPago', 'desc')
            ->get(['descripcion', 'metodo_pago.nombre as metodoPago', 'importe', 'igv', 'total', 'fechaPago']);

        // Calcular totales
        $totales = [
            'totalImporte' => $pagos->sum('importe'),
            'totalIgv' => $pagos->sum('igv'),
            'totalGeneral' => $pagos->sum('total')
        ];

        // Para API devuelve JSON
        if ($request->wantsJson()) {
            return response()->json(['pagos' => $pagos, 'totales' => $totales]);
        }

        // Para generar PDF
        $pdf = app(PDF::class)->loadView('exports.reporte-pagos-estudiante', [
            'estudiante' => $estudiante,
            'pagos' => $pagos,
            'totales' => $totales,
            'fecha' => now()->format('d/m/Y'),
        ]);
        return $pdf->download('pagos-estudiante-' . $estudiante->nombre . '.pdf');
    }

    public function obtenerReporteAsistenciaPai(Request $request)
    {
        $idUsuario = $request->input('idUsuario');

        $validator = Validator::make($request->all(), [
            'idUsuario' => 'required|integer|exists:usuario,id_usuario',
        ], [
            'idUsuario.required' => 'El ID del estudiante es requerido',
            'idUsuario.exists' => 'El estudiante no existe',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Obtener información del estudiante
        $estudiante = Usuario::find($idUsuario);
        if (!$estudiante) {
            return response()->json([
                'message' => 'Estudiante no encontrado',
            ], 404);
        }

        // Recuperar los datos de asistencia
        $asistencias = CursoAsistencia::join('curso_estudiantes', 'curso_asistencia.idCursoEstudiante', '=', 'curso_estudiantes.idCursoEstudiante')
            ->join('curso', 'curso_estudiantes.idCurso', '=', 'curso.idCurso')
            ->join('curso_clases', 'curso.idCurso', '=', 'curso_clases.idCurso')
            ->where('curso_estudiantes.idUsuario', '=', $idUsuario)
            ->select(
                'curso.nombreCurso',
                DB::raw('SUM(CASE WHEN curso_asistencia.estado = "presente" THEN 1 ELSE 0 END) as cantidad_asistencias'),
                DB::raw('CONCAT(ROUND(SUM(CASE WHEN curso_asistencia.estado = "presente" THEN 1 ELSE 0 END) / SUM(curso_clases.totalClases) * 100, 1), "%") as porcentaje_asistencia'),
                DB::raw('SUM(CASE WHEN curso_asistencia.estado = "ausente" THEN 1 ELSE 0 END) as cantidad_ausencias'),
                DB::raw('CONCAT(ROUND(SUM(CASE WHEN curso_asistencia.estado = "ausente" THEN 1 ELSE 0 END) / SUM(curso_clases.totalClases) * 100, 1), "%") as porcentaje_ausencias'),
                DB::raw('SUM(CASE WHEN curso_asistencia.estado = "falta_justificada" THEN 1 ELSE 0 END) as cantidad_faltas_justificadas'),
                DB::raw('CONCAT(ROUND(SUM(CASE WHEN curso_asistencia.estado = "falta_justificada" THEN 1 ELSE 0 END) / SUM(curso_clases.totalClases) * 100, 1), "%") as porcentaje_faltas_justificadas'),
                DB::raw('SUM(curso_clases.totalClases) as total_clases')
            )
            ->groupBy('curso.idCurso', 'curso.nombreCurso')
            ->get();

        // Verificar si hay datos disponibles
        if ($asistencias->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron registros de asistencia para los datos proporcionados.',
            ], 404);
        }

        // Calcular totales generales
        $totales = [
            'asistencias' => $asistencias->sum('cantidad_asistencias'),
            'ausencias' => $asistencias->sum('cantidad_ausencias'),
            'faltas_justificadas' => $asistencias->sum('cantidad_faltas_justificadas'),
            'total_clases' => $asistencias->sum('total_clases')
        ];

        // Generar PDF
        $pdf = app(PDF::class)->loadView('exports.reporte-asistencia-estudiante', [
            'estudiante' => $estudiante,
            'asistencias' => $asistencias,
            'totales' => $totales,
            'fecha' => now()->format('d/m/Y'),
        ]);

        // Descargar el PDF
        return $pdf->download('asistencia-estudiante-' . $estudiante->nombre . '.pdf');
    }

    // Funcion para obtener el plan de estudios de un estudiante
    public function obtenerPlanEstudios(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idUsuario' => 'required|integer|exists:usuario,id_usuario',
        ], [
            'idUsuario.required' => 'El ID del estudiante es requerido',
            'idUsuario.exists' => 'El estudiante no existe',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $planEstudio = CarreraEstudiantes::where('idEstudiante', $request->idUsuario)
            ->with('carrera.planEstudios.planEstudioCiclos.ciclo.cicloCursos')
            ->get()
            ->map(function ($carreraEstudiante) {
                return [
                    'carrera' => $carreraEstudiante->carrera->nombreCarrera,
                    'nombrePlan' => $carreraEstudiante->carrera->planEstudios->first()->anio . ' - ' . 'Plan de Estudios ' . $carreraEstudiante->carrera->planEstudios->first()->anio,
                    'anio' => $carreraEstudiante->carrera->planEstudios->first()->anio,
                    'ciclos' => $carreraEstudiante->carrera->planEstudios->first()->planEstudioCiclos->map(function ($planEstudioCiclo) {
                        return [
                            'nombreCiclo' => $planEstudioCiclo->ciclo->nombreCiclo,
                            'cursos' => $planEstudioCiclo->ciclo->cicloCursos->map(function ($cicloCurso) {
                                return [
                                    'nombreCurso' => $cicloCurso->curso->nombreCurso,
                                    'creditos' => $cicloCurso->curso->creditos,
                                    'horas' => $cicloCurso->horas,
                                ];
                            }),
                        ];
                    }),
                ];
            })
            ->first();

        return response()->json($planEstudio);
    }

    // Funcion para descargar el plan de estudios de un estudiante
    public function descargarPlanEstudios(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idUsuario' => 'required|integer|exists:usuario,id_usuario',
        ], [
            'idUsuario.required' => 'El ID del estudiante es requerido',
            'idUsuario.exists' => 'El estudiante no existe',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $estudiante = Usuario::find($request->idUsuario);
        $nombreEstudiante = $estudiante->nombres . ' ' . $estudiante->apellidoPaterno . ' ' . $estudiante->apellidoMaterno;

        $planEstudio = CarreraEstudiantes::where('idEstudiante', $request->idUsuario)
            ->with('carrera.planEstudios.planEstudioCiclos.ciclo.cicloCursos')
            ->get()
            ->map(function ($carreraEstudiante) {
                return [
                    'carrera' => $carreraEstudiante->carrera->nombreCarrera,
                    'nombrePlan' => $carreraEstudiante->carrera->planEstudios->first()->anio . ' - ' . 'Plan de Estudios ' . $carreraEstudiante->carrera->planEstudios->first()->anio,
                    'anio' => $carreraEstudiante->carrera->planEstudios->first()->anio,
                    'ciclos' => $carreraEstudiante->carrera->planEstudios->first()->planEstudioCiclos->map(function ($planEstudioCiclo) {
                        return [
                            'nombreCiclo' => $planEstudioCiclo->ciclo->nombreCiclo,
                            'cursos' => $planEstudioCiclo->ciclo->cicloCursos->map(function ($cicloCurso) {
                                return [
                                    'nombreCurso' => $cicloCurso->curso->nombreCurso,
                                    'creditos' => $cicloCurso->curso->creditos,
                                    'horas' => $cicloCurso->horas,
                                ];
                            }),
                        ];
                    }),
                ];
            })
            ->first();

        $pdf = app(PDF::class)->loadView(
            'exports.plan-estudios',
            ['planEstudio' => $planEstudio, 'nombreEstudiante' => $nombreEstudiante]
        );

        return $pdf->download('plan-estudios.pdf');
    }

    public function descargarTramiteAcademico(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idUsuario' => 'required|integer|exists:usuario,id_usuario',
            'tipoTramite' => 'required|string',
            'lugarTramite' => 'required|string',
        ], [
            'idUsuario.required' => 'El ID del estudiante es requerido',
            'idUsuario.exists' => 'El estudiante no existe',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $estudiante = Usuario::find($request->idUsuario);
        $nombreEstudiante = $estudiante->nombres . ' ' . $estudiante->apellidoPaterno . ' ' . $estudiante->apellidoMaterno;
        $dniEstudiante = $estudiante->dni;
        $telefonoEstudiante = $estudiante->telefono;
        $correoEstudiante = $estudiante->correo;

        $nombreCarrera = CarreraEstudiantes::where('idEstudiante', $request->idUsuario)
            ->join('carreras', 'carreras.idCarrera', '=', 'carrera_estudiantes.idCarrera')
            ->value('carreras.nombreCarrera');

        // Datos para la vista
        $data = [
            'nombreEstudiante' => $nombreEstudiante,
            'dniEstudiante' => $dniEstudiante,
            'telefonoEstudiante' => $telefonoEstudiante,
            'correoEstudiante' => $correoEstudiante,
            'nombreCarrera' => $nombreCarrera,
            'tipoTramite' => $request->tipoTramite,
            'lugarTramite' => $request->lugarTramite,
            'fechaActual' => now()->format('d/m/Y'),
        ];

        // Generar PDF
        $pdf = app(PDF::class)->loadView('exports.tramites-academicos', $data);

        // Nombre del archivo
        $filename = 'Solicitud_' . str_replace(' ', '_', $request->tipoTramite) . '_' . $dniEstudiante . '.pdf';

        // Descargar el PDF
        return $pdf->download($filename);
    }

    public function exportarPerfilPdf(Request $request)
    {
        $query = Estudiante::query();

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('id_empresa')) {
            $query->where('id_empresa', $request->id_empresa);
        }

        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('fechareg', [
                $request->fecha_inicio,
                $request->fecha_fin
            ]);
        }

        $estudiantes = $query->get();

        $pdf = app(PDF::class)->loadView('exports.reporte_perfil.pdf', [
            'estudiantes' => $estudiantes,
            'filtros' => $request->all()
        ]);

        $nombreArchivo = 'reporte_estudiantes_' . date('Ymd_His') . '.pdf';

        return $pdf->download($nombreArchivo);
    }

    public function reportePagos(Request $request) {
        // En una implementación real, esto filtraría por el usuario autenticado.
        // Simulamos obteniendo pagos recientes o todos.
        
        $pagos = DB::table('pago')
             ->join('metodo_pago', 'pago.idMetodoPago', '=', 'metodo_pago.idMetodoPago')
             ->select(
                 'pago.idPago',
                 'pago.importe',
                 'pago.igv',
                 'pago.total',
                 'pago.descripcion',
                 'pago.fechaPago',
                 'metodo_pago.nombre as metodo_pago'
             )
             ->orderBy('pago.fechaPago', 'desc')
             ->limit(10)
             ->get();

        return response()->json($pagos);
    }

    /**
     * Devuelve los trámites del estudiante autenticado.
     */
    public function misTramites(Request $request)
    {
        $idUsuario = $request->input('idUsuario');

        $validator = Validator::make($request->all(), [
            'idUsuario' => 'required|integer|exists:usuario,id_usuario',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Intentar buscar tramites directamente por id_usuario en la tabla tramites
        // Si la tabla usa columna 'idUsuario' o 'id_usuario', probamos ambas
        try {
            $columns = DB::select("SHOW COLUMNS FROM tramites");
            $columnNames = array_map(fn($c) => $c->Field, $columns);

            // Determinar la columna que relaciona al usuario
            if (in_array('id_usuario', $columnNames)) {
                $tramites = DB::table('tramites')
                    ->where('id_usuario', $idUsuario)
                    ->orderBy('fecha_solicitud', 'desc')
                    ->get();
            } elseif (in_array('idUsuario', $columnNames)) {
                $tramites = DB::table('tramites')
                    ->where('idUsuario', $idUsuario)
                    ->orderBy('fecha_solicitud', 'desc')
                    ->get();
            } else {
                // La tabla tramites no tiene columna de usuario directa
                // Retornar lista vacía
                return response()->json(['data' => []]);
            }

            return response()->json([
                'data' => $tramites->map(function ($t) {
                    return [
                        'idTramite'     => $t->id ?? null,
                        'ticket'        => $t->ticket ?? ($t->matricula ?? '-') . '-' . ($t->id ?? ''),
                        'tipo'          => $t->tipo_tramite ?? $t->tipo ?? '—',
                        'fechaRegistro' => $t->fecha_solicitud ?? $t->created_at ?? null,
                        'estado'        => $t->estado ?? '—',
                        'mensaje'       => $t->mensaje ?? null,
                        'observacion'   => $t->observacion ?? null,
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'error' => 'Tabla tramites no disponible: ' . $e->getMessage()]);
        }
    }
}
