<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Storage;
use App\Models\AsistenciaDocente;
use App\Models\CursoDocentes;
use App\Models\CursoHorario;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\PDF;
use App\Models\HorarioCurso;
use App\Models\Docente;

class DocenteController extends Controller
{

    //FUNCION PARA REPORTE DE NOTAS:
    public function reporteNotasPorDocente(Request $request, $idDocente)
    {
        // Validar que el docente exista
        $docente = Docente::findOrFail($idDocente);

        // Obtener los cursos del docente
        $cursosDocente = CursoDocentes::with(['curso', 'cursoEvaluaciones.evaluacionesNotas.usuario'])
            ->where('idUsuario', $docente->id_docente)
            ->get();

        // Verificar si el docente tiene cursos asignados
        if ($cursosDocente->isEmpty()) {
            return back()->with('error', 'El docente no tiene cursos asignados.');
        }

        // Organizar datos para el reporte
        $datosReporte = [];

        foreach ($cursosDocente as $cursoDocente) {
            $curso = $cursoDocente->curso;

            // Obtener todas las evaluaciones del curso con sus notas
            $evaluaciones = $cursoDocente->cursoEvaluaciones;

            $alumnosNotas = [];

            foreach ($evaluaciones as $evaluacion) {
                foreach ($evaluacion->evaluacionesNotas as $nota) {
                    $idAlumno = $nota->usuario->idUsuario;

                    if (!isset($alumnosNotas[$idAlumno])) {
                        $alumnosNotas[$idAlumno] = [
                            'alumno' => $nota->usuario,
                            'notas' => []
                        ];
                    }

                    $alumnosNotas[$idAlumno]['notas'][$evaluacion->idCursoEvaluacion] = [
                        'nombre_evaluacion' => $evaluacion->nombre,
                        'nota' => $nota->nota
                    ];
                }
            }

            $datosReporte[] = [
                'curso' => $curso,
                'alumnos' => $alumnosNotas,
                'evaluaciones' => $evaluaciones
            ];
        }

        // Generar el PDF
        $pdf = app(PDF::class)->loadView('exports.notas_docente', [
            'docente' => $docente,
            'datosReporte' => $datosReporte,
            'fechaReporte' => now()->format('d/m/Y H:i')
        ]);

        return $pdf->download("reporte_notas_{$docente->codigo}.pdf");
    }

    //FUNCION PARA REPORTE DE HORARIO:
    public function descargarReporteHorario(Request $request)
    {
        // Validar parámetros de filtrado
        $request->validate([
            'idCursoTipo' => 'nullable|integer',
            'dia' => 'nullable|string',
            'fecha_ini' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_ini'
        ]);

        // Construir consulta con relaciones
        $query = HorarioCurso::with([
            'cursoTipo',
            'cursoDocentes.docente',
            'cursoEstudiantes.estudiante'
        ]);

        // Aplicar filtros
        if ($request->idCursoTipo) {
            $query->where('idCursoTipo', $request->idCursoTipo);
        }

        if ($request->dia) {
            $query->where('dia', $request->dia);
        }

        if ($request->fecha_ini) {
            $query->where('fecha_ini', '>=', $request->fecha_ini);
        }

        if ($request->fecha_fin) {
            $query->where('fecha_fin', '<=', $request->fecha_fin);
        }

        // Ordenar por día y aula
        $horarios = $query->orderBy('dia')->orderBy('aula')->get();

        // Generar el PDF
        $pdf = app(PDF::class)->loadView('exports.horario-docente', [
            'horarios' => $horarios,
            'filtros' => $request->all()
        ]);

        return $pdf->download('reporte_horarios.pdf');
    }

    public function subirFotoPerfilDocente(Request $request, $idUsuario)
    {
        $docente = Usuario::find($idUsuario);
        if (!$docente) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
        }

        // Verifica si hay un archivo en la solicitud
        if ($request->hasFile('perfil')) {
            // Ruta correcta para almacenar en el directorio público de storage
            $path = "profiles/docentes/$idUsuario";

            // Si hay una imagen de perfil existente, elimínala antes de guardar la nueva
            if ($docente->perfil && Storage::disk('public')->exists($docente->perfil)) {
                Storage::disk('public')->delete($docente->perfil);
            }

            // Guarda la nueva imagen de perfil en el disco 'public'
            $filename = $request->file('perfil')->store($path, 'public');
            $docente->perfil = $filename; // Actualiza la ruta en el campo `perfil` del docente
            $docente->save();

            return response()->json(['success' => true, 'filename' => basename($filename)]);
        }

        return response()->json(['success' => false, 'message' => 'No se cargó la imagen'], 400);
    }

    public function obtenerCursosAsignados(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idUsuario' => 'required|integer|exists:usuarios,idUsuario',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Datos incorrectos'], 400);
        }

        $cursos = CursoHorario::with('cursoDocentes', 'cursoTipo.curso')
            ->whereHas('cursoDocentes', function ($query) use ($request) {
                $query->where('idUsuario', $request->idUsuario);
            })
            ->get()
            ->map(function ($claseCurso) {
                return [
                    'seccion' => $claseCurso->cursoTipo->curso->seccion->nombre,
                    'nombre_curso' => $claseCurso->cursoTipo->curso->nombreCurso,
                    'tipo' => $claseCurso->cursoTipo->tipoCurso->nombre,
                    'aula' => $claseCurso->aula,
                    'dia' => $claseCurso->dia,
                    'hora_ini' => $claseCurso->hora_ini,
                    'hora_fin' => $claseCurso->hora_fin,
                ];
            });

        return response()->json(['cursos' => $cursos]);
    }

    public function obtenerReporteAsistenciaCursoDocente(Request $request)
    {
        $validatedData = $request->validate([
            'id_curso' => 'required|integer|exists:cursos,id',
            'id_anho' => 'required|integer',
            'id_mes' => 'required|integer|min:1|max:12',
        ]);

        $asistencias = AsistenciaDocente::where('id_curso', $validatedData['id_curso'])
            ->whereYear('fecha', $validatedData['id_anho'])
            ->whereMonth('fecha', $validatedData['id_mes'])
            ->with(['curso', 'docente'])
            ->get(['fecha', 'id_curso', 'id_docente', 'estado']);

        $reporte = $asistencias->map(function ($asistencia) {
            return [
                'fecha' => $asistencia->fecha,
                'curso_nombre' => $asistencia->curso ? $asistencia->curso->nombre : null,
                'docente_nombre' => $asistencia->docente ? $asistencia->docente->nombre : null,
                'asistencia_estado' => $asistencia->estado,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $reporte,
        ]);
    }

    public function reporteNotasEstudiantes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idUsuario' => 'required|integer|exists:usuarios,idUsuario',
            'idCurso' => 'required|integer|exists:curso,idCurso',
        ], [
            'idUsuario.required' => 'El id del docente es requerido',
            'idUsuario.integer' => 'El id del docente debe ser un número entero',
            'idUsuario.exists' => 'El docente no existe',
            'idCurso.required' => 'El id del curso es requerido',
            'idCurso.integer' => 'El id del curso debe ser un número entero',
            'idCurso.exists' => 'El curso no existe',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()], 400);
        }

        $notasCurso = CursoDocentes::where('idUsuario', $request->idUsuario)
            ->where('idCurso', $request->idCurso)
            ->with('curso.cursoEvaluaciones.evaluacionesNotas')
            ->get()
            ->map(function ($cursoDocente) {
                return [
                    'curso' => $cursoDocente->curso->nombreCurso,
                    'evaluaciones' => $cursoDocente->curso->cursoEvaluaciones->map(function ($evaluacion) {
                        return [
                            'nombre' => $evaluacion->evaluacion->nombre,
                            'porcentaje' => $evaluacion->porcentaje,
                            'fecha' => $evaluacion->fechaEvaluacion,
                            'notas' => $evaluacion->evaluacionesNotas->map(function ($nota) {
                                return [
                                    'estudiante' => $nota->usuario->nombres . ' ' . $nota->usuario->apellidoPaterno . ' ' . $nota->usuario->apellidoMaterno,
                                    'nota' => $nota->nota,
                                ];
                            }),
                        ];
                    }),
                ];
            })->first();

        return response()->json($notasCurso);
    }

    // Función para obtener el horario de un docente
    public function reporteHorario(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idUsuario' => 'required|integer|exists:usuarios,idUsuario',
            'nroMes' => 'required|integer|between:1,12',
        ], [
            'idUsuario.required' => 'El ID del docente es requerido',
            'idUsuario.exists' => 'El docente no existe',
            'nroMes.required' => 'El número del mes es requerido',
            'nroMes.between' => 'El número del mes debe estar entre 1 y 12',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $nroMes = $request->nroMes;

        $cursosDocente = CursoDocentes::with('cursoHorario')
            ->where('idUsuario', $request->idUsuario)
            ->whereHas('cursoHorario.cursoTipo.curso', function ($query) use ($nroMes) {
                $query->whereMonth('fecha_ini', '<=', $nroMes)
                    ->whereMonth('fecha_fin', '>=', $nroMes);
            })
            ->get()
            ->map(function ($cursoDocente) {
                return [
                    'curso' => $cursoDocente->curso->nombreCurso,
                    'horario' => $cursoDocente->cursoHorario->map(function ($horario) {
                        return [
                            'dia' => $horario->dia,
                            'hora_ini' => $horario->hora_ini,
                            'hora_fin' => $horario->hora_fin,
                            'aula' => $horario->aula,
                        ];
                    }),
                ];
            });

        return response()->json($cursosDocente);
    }

    public function obtenerAsistenciasDocente(Request $request)
    {
        // Validación de la fecha
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date', // La fecha debe ser válida
        ], [
            'fecha.required' => 'La fecha es obligatoria',
            'fecha.date' => 'La fecha debe tener un formato válido (YYYY-MM-DD)',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 400);
        }

        // Buscar asistencias según la fecha proporcionada
        $asistencias = AsistenciaDocente::with(['curso', 'docente', 'horario'])
            ->whereDate('fecha', $request->fecha) // Filtrar por la fecha
            ->get();

        // Mapear los resultados en la estructura solicitada
        $resultado = $asistencias->map(function ($asistencia) {
            return [
                'curso_nombre' => optional($asistencia->curso)->nombre, // Nombre del curso
                'docente_nombre' => optional($asistencia->docente)->nombres . ' ' . optional($asistencia->docente)->apellidoPaterno,
                'horario_fecha' => $asistencia->fecha, // Fecha del horario
                'horario_hora' => optional($asistencia->horario)->hora_ini, // Hora de inicio del horario
                'estado_descripcion' => $asistencia->estado, // Estado de la asistencia
            ];
        });

        return response()->json([
            'success' => true,
            'asistencias' => $resultado,
        ], 200);
    }
}
