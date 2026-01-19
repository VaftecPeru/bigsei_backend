<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\ProgresoUsuarioContenido;
use App\Models\Certificado;
use App\Models\PeriodoCurso;
use App\Models\PeriodoModulo;
use App\Models\PeriodoTema;
use App\Models\PeriodoVideo;
use App\Models\PeriodoTarea;
use App\Models\PeriodoCuestionario;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CertificadoController extends Controller
{
    /**
     * Obtiene el progreso del usuario en un curso específico
     */
    public function getProgresoCurso(Request $request, $id_periodocurso)
    {
        try {
            $id_usuario = $request->sessionUser->id_usuario;

            // Obtener el curso
            $periodoCurso = PeriodoCurso::with(['curso', 'empresa'])->find($id_periodocurso);
            if (!$periodoCurso) {
                return response()->json(['mensaje' => 'Curso no encontrado'], 404);
            }

            // Contar todo el contenido del curso
            $totalContenido = $this->contarTotalContenido($id_periodocurso);

            // Contar contenido completado por el usuario
            $contenidoCompletado = ProgresoUsuarioContenido::where('id_usuario', $id_usuario)
                ->where('id_periodocurso', $id_periodocurso)
                ->where('completado', true)
                ->count();

            // Calcular porcentaje
            $porcentaje = $totalContenido > 0 ? round(($contenidoCompletado / $totalContenido) * 100, 2) : 0;

            // Verificar si ya tiene certificado
            $certificado = Certificado::where('id_usuario', $id_usuario)
                ->where('id_periodocurso', $id_periodocurso)
                ->where('estado', true)
                ->first();

            return response()->json([
                'id_periodocurso' => $id_periodocurso,
                'nombre_curso' => $periodoCurso->curso->nombre ?? 'Curso',
                'nombre_empresa' => $periodoCurso->empresa->razon_social ?? 'BIGSEI',
                'descripcion_curso' => $periodoCurso->curso->detalle ?? '',
                'imagen_curso' => $periodoCurso->curso->url_img ?? '',
                'total_contenido' => $totalContenido,
                'contenido_completado' => $contenidoCompletado,
                'porcentaje_progreso' => $porcentaje,
                'curso_completado' => $porcentaje >= 100,
                'tiene_certificado' => $certificado !== null,
                'certificado' => $certificado ? [
                    'id_certificado' => $certificado->id_certificado,
                    'codigo_certificado' => $certificado->codigo_certificado,
                    'fecha_emision' => $certificado->fecha_emision->format('d/m/Y'),
                ] : null,
                'desglose' => $this->obtenerDesgloseProgreso($id_usuario, $id_periodocurso),
                'detalle_contenido' => $this->obtenerDetalleContenido($id_usuario, $id_periodocurso),
            ]);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error al obtener el progreso: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene el detalle completo del contenido v su estado
     */
    private function obtenerDetalleContenido($id_usuario, $id_periodocurso)
    {
        $modulos = PeriodoModulo::where('id_periodocurso', $id_periodocurso)
            ->orderBy('orden', 'asc')
            ->get();

        $resultado = [];

        foreach ($modulos as $modulo) {
            $temas = PeriodoTema::where('id_periodomodulo', $modulo->id_periodomodulo)->get();
            $temasData = [];

            foreach ($temas as $tema) {
                // Videos
                $videos = PeriodoVideo::where('id_periodotema', $tema->id_periodotema)->get()->map(function ($video) use ($id_usuario, $id_periodocurso) {
                    $completado = ProgresoUsuarioContenido::where('id_usuario', $id_usuario)
                        ->where('id_periodocurso', $id_periodocurso)
                        ->where('tipo_contenido', 'video')
                        ->where('id_contenido', $video->id_periodovideo)
                        ->where('completado', true)
                        ->exists();
                    return [
                        'id' => $video->id_periodovideo,
                        'titulo' => $video->nombre,
                        'tipo' => 'video',
                        'completado' => $completado
                    ];
                });

                // Tareas
                $tareas = PeriodoTarea::where('id_periodotema', $tema->id_periodotema)->get()->map(function ($tarea) use ($id_usuario, $id_periodocurso) {
                    $completado = ProgresoUsuarioContenido::where('id_usuario', $id_usuario)
                        ->where('id_periodocurso', $id_periodocurso)
                        ->where('tipo_contenido', 'tarea')
                        ->where('id_contenido', $tarea->id_periodotarea)
                        ->where('completado', true)
                        ->exists();
                    return [
                        'id' => $tarea->id_periodotarea,
                        'titulo' => $tarea->titulo,
                        'tipo' => 'tarea',
                        'completado' => $completado
                    ];
                });

                // Cuestionarios
                $cuestionarios = PeriodoCuestionario::where('id_periodotema', $tema->id_periodotema)->get()->map(function ($cuestionario) use ($id_usuario, $id_periodocurso) {
                    $completado = ProgresoUsuarioContenido::where('id_usuario', $id_usuario)
                        ->where('id_periodocurso', $id_periodocurso)
                        ->where('tipo_contenido', 'cuestionario')
                        ->where('id_contenido', $cuestionario->id_periodocuestionario)
                        ->where('completado', true)
                        ->exists();
                    return [
                        'id' => $cuestionario->id_periodocuestionario,
                        'titulo' => $cuestionario->titulo,
                        'tipo' => 'cuestionario',
                        'completado' => $completado
                    ];
                });

                $temasData[] = [
                    'id' => $tema->id_periodotema,
                    'titulo' => $tema->titulo,
                    'contenidos' => $videos->concat($tareas)->concat($cuestionarios)
                ];
            }

            $resultado[] = [
                'id' => $modulo->id_periodomodulo,
                'titulo' => $modulo->titulo,
                'temas' => $temasData
            ];
        }

        return $resultado;
    }

    /**
     * Marca un contenido como completado
     */
    public function marcarContenidoCompletado(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_periodocurso' => 'required|integer|exists:periodo_curso,id_periodocurso',
            'tipo_contenido' => 'required|string|in:video,tarea,cuestionario',
            'id_contenido' => 'required|integer',
        ], [
            'id_periodocurso.required' => 'El ID del curso es requerido',
            'id_periodocurso.exists' => 'El curso no existe',
            'tipo_contenido.required' => 'El tipo de contenido es requerido',
            'tipo_contenido.in' => 'El tipo de contenido debe ser: video, tarea o cuestionario',
            'id_contenido.required' => 'El ID del contenido es requerido',
        ]);

        if ($validator->fails()) {
            return response()->json(['errores' => $validator->errors()], 422);
        }

        try {
            $id_usuario = $request->sessionUser->id_usuario;

            // Verificar que el contenido existe
            $contenidoExiste = $this->verificarContenidoExiste(
                $request->tipo_contenido,
                $request->id_contenido
            );

            if (!$contenidoExiste) {
                return response()->json(['mensaje' => 'El contenido especificado no existe'], 404);
            }

            // Crear o actualizar el progreso
            $progreso = ProgresoUsuarioContenido::updateOrCreate(
                [
                    'id_usuario' => $id_usuario,
                    'tipo_contenido' => $request->tipo_contenido,
                    'id_contenido' => $request->id_contenido,
                ],
                [
                    'id_periodocurso' => $request->id_periodocurso,
                    'completado' => true,
                    'fecha_completado' => Carbon::now(),
                ]
            );

            // Verificar si el curso está 100% completado
            $progresoActual = $this->calcularProgresoCurso($id_usuario, $request->id_periodocurso);

            return response()->json([
                'mensaje' => 'Contenido marcado como completado',
                'progreso' => $progreso,
                'porcentaje_progreso' => $progresoActual['porcentaje'],
                'curso_completado' => $progresoActual['porcentaje'] >= 100,
            ]);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error al marcar el contenido: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Genera el certificado para un curso completado al 100%
     */
    public function generarCertificado(Request $request, $id_periodocurso)
    {
        try {
            $id_usuario = $request->sessionUser->id_usuario;

            // Verificar que el curso existe
            $periodoCurso = PeriodoCurso::with(['curso', 'empresa'])->find($id_periodocurso);
            if (!$periodoCurso) {
                return response()->json(['mensaje' => 'Curso no encontrado'], 404);
            }

            // Verificar que el curso está 100% completado
            $progreso = $this->calcularProgresoCurso($id_usuario, $id_periodocurso);
            if ($progreso['porcentaje'] < 100) {
                return response()->json([
                    'mensaje' => 'Debe completar el 100% del curso para obtener el certificado',
                    'porcentaje_actual' => $progreso['porcentaje'],
                    'contenido_faltante' => $progreso['total'] - $progreso['completado'],
                ], 400);
            }

            // Verificar si ya existe un certificado para obtener su código (para regenerarlo con el nuevo diseño)
            $certificadoExistente = Certificado::where('id_usuario', $id_usuario)
                ->where('id_periodocurso', $id_periodocurso)
                ->first();

            // Obtener datos del usuario
            $usuario = DB::table('usuario')
                ->leftJoin('persona', 'usuario.id_usuario', '=', 'persona.id_persona')
                ->where('usuario.id_usuario', $id_usuario)
                ->select('usuario.*', 'persona.nombre', 'persona.apellido_paterno', 'persona.apellido_materno')
                ->first();

            // Usar código existente o generar uno nuevo
            $codigoCertificado = $certificadoExistente ? $certificadoExistente->codigo_certificado : Certificado::generarCodigoUnico();
            $fechaEmision = $certificadoExistente ? $certificadoExistente->fecha_emision : Carbon::now();

            // Generar PDF
            $nombreArchivo = 'certificado_' . $codigoCertificado . '.pdf';
            $rutaArchivo = 'certificados/' . $nombreArchivo;

            $empresaNombre = $periodoCurso->empresa ? $periodoCurso->empresa->razon_social : 'BIGSEI';
            $nombreEstudiante = trim(($usuario->nombre ?? '') . ' ' . ($usuario->apellido_paterno ?? '') . ' ' . ($usuario->apellido_materno ?? ''));

            // Convertir logo a base64 para evitar problemas de rutas en DomPDF
            $logoPath = public_path('img/logo.png');
            $logoBase64 = '';
            if (file_exists($logoPath)) {
                $logoData = base64_encode(file_get_contents($logoPath));
                $logoBase64 = 'data:image/png;base64,' . $logoData;
            }

            $dataPdf = [
                'nombre_estudiante' => $nombreEstudiante,
                'nombre_curso' => $periodoCurso->curso->nombre ?? 'Curso',
                'nombre_empresa' => $empresaNombre,
                'fecha_emision' => $fechaEmision->format('d \\d\\e F \\d\\e Y'),
                'codigo_certificado' => $codigoCertificado,
                'duracion_curso' => $periodoCurso->horas_semanal ?? '40',
                'es_sincrono' => $periodoCurso->es_sincrono,
                'logo_base64' => $logoBase64,
            ];

            $pdf = Pdf::loadView('pdf.certificado', $dataPdf);
            $pdf->setPaper('landscape', 'A4');

            // Guardar el PDF directamente para evitar dependencia de finfo
            $fullPath = storage_path('app/public/' . $rutaArchivo);
            if (!file_exists(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }
            file_put_contents($fullPath, $pdf->output());

            // Crear o Actualizar el registro del certificado
            $certificado = Certificado::updateOrCreate(
                [
                    'id_usuario' => $id_usuario,
                    'id_periodocurso' => $id_periodocurso
                ],
                [
                    'codigo_certificado' => $codigoCertificado,
                    'ruta_archivo' => $rutaArchivo,
                    'nombre_archivo' => $nombreArchivo,
                    'fecha_emision' => $fechaEmision,
                    'estado' => true
                ]
            );

            return response()->json([
                'mensaje' => 'Certificado generado/actualizado exitosamente',
                'certificado' => [
                    'id_certificado' => $certificado->id_certificado,
                    'codigo_certificado' => $certificado->codigo_certificado,
                    'fecha_emision' => $certificado->fecha_emision->format('d/m/Y'),
                    'nombre_curso' => $dataPdf['nombre_curso'],
                    'ruta_descarga' => url('/api/estudiante/descargar-certificado/' . $certificado->id_certificado) 
                ],
            ], 201);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error Generar Certificado: " . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());
            return response()->json(['mensaje' => 'Error al generar el certificado: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Descarga el certificado en PDF
     */
    public function descargarCertificado(Request $request, $id_certificado)
    {
        try {
            $id_usuario = $request->sessionUser->id_usuario;

            $certificado = Certificado::where('id_certificado', $id_certificado)
                ->where('id_usuario', $id_usuario)
                ->where('estado', true)
                ->first();

            if (!$certificado) {
                return response()->json(['mensaje' => 'Certificado no encontrado'], 404);
            }

            // Usar storage_path directo para evitar error de finfo en Storage::disk
            $rutaCompleta = storage_path('app/public/' . $certificado->ruta_archivo);

            if (!file_exists($rutaCompleta)) {
                return response()->json(['mensaje' => 'El archivo del certificado no existe'], 404);
            }

            // Retornar archivo manualmente para evitar detección de mime type
            return response(file_get_contents($rutaCompleta))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $certificado->nombre_archivo . '"');
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error al descargar el certificado: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Lista todos los certificados del usuario
     */
    public function listarMisCertificados(Request $request)
    {
        try {
            $id_usuario = $request->sessionUser->id_usuario;
            $per_page = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            $certificados = Certificado::where('id_usuario', $id_usuario)
                ->where('estado', true)
                ->with(['periodoCurso.curso', 'periodoCurso.empresa'])
                ->orderBy('fecha_emision', 'desc')
                ->paginate($per_page, ['*'], 'page', $page);

            $resultado = $certificados->map(function ($cert) {
                return [
                    'id_certificado' => $cert->id_certificado,
                    'codigo_certificado' => $cert->codigo_certificado,
                    'nombre_curso' => $cert->periodoCurso->curso->nombre ?? 'Curso',
                    'nombre_empresa' => $cert->periodoCurso->empresa->nombre ?? 'BIGSEI',
                    'fecha_emision' => $cert->fecha_emision->format('d/m/Y'),
                    'es_sincrono' => $cert->periodoCurso->es_sincrono,
                ];
            });

            return response()->json([
                'data' => $resultado,
                'total' => $certificados->total(),
                'per_page' => $certificados->perPage(),
                'current_page' => $certificados->currentPage(),
                'last_page' => $certificados->lastPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error al listar los certificados: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Lista los cursos matriculados del usuario con su progreso
     */
    public function listarMisCursosProgreso(Request $request)
    {
        try {
            $sessionUser = $request->sessionUser;
            $id_usuario = $sessionUser->id_usuario;
            $id_empresa = $sessionUser->id_empresa;
            $per_page = $request->get('per_page', 15);
            $page = $request->get('page', 1);

            // Obtener cursos matriculados del usuario
            // Usamos lógica similar a MiAcademicoController pero necesitamos modelos Eloquent para facilitar las relaciones
            /*
            Consulta SQL equivalente:
            select * from periodo_curso a 
            inner join matricula_curso b on a.id_periodocurso = b.id_periodocurso
            inner join matricula c on b.id_matricula = c.id_matricula
            inner join curso d on a.id_curso = d.id_curso
            inner join persona e on a.id_docente = e.id_persona
            where c.id_estudiante = ?
            */

             $cursos = DB::table("matricula as z")
                ->join("matricula_curso as y", "z.id_matricula", "y.id_matricula")
                ->join("periodo_curso as a", "y.id_periodocurso", "a.id_periodocurso")
                ->join("curso as c", "a.id_curso", "c.id_curso")
                ->leftJoin("persona as b", "a.id_docente", "b.id_persona")
                ->leftJoin("periodo as p", "a.id_periodo", "p.id_periodo")
                ->leftJoin("empresa as emp", "a.id_empresa", "emp.id_empresa")
                ->select(
                    "a.id_periodocurso",
                    "c.nombre as curso_nombre",
                    "c.codigo as curso_codigo",
                    "c.url_img as curso_imagen",
                    "c.id_archivo",
                    "a.detalle as curso_descripcion",
                    "b.nombre_completo as docente_nombre",
                    "p.nombre as periodo_nombre",
                    "emp.razon_social as empresa_nombre",
                    "a.fecha_inicio",
                    "a.fecha_fin"
                )
                ->where("z.id_estudiante", $id_usuario)
                ->orderBy("a.id_periodocurso", "desc")
                ->paginate($per_page, ['*'], 'page', $page);

            // Calcular progreso para cada curso
            $resultado = $cursos->getCollection()->map(function ($curso) use ($id_usuario) {
                // Calcular progreso
                $progreso = $this->calcularProgresoCurso($id_usuario, $curso->id_periodocurso);
                
                return [
                    'id_periodocurso' => $curso->id_periodocurso,
                    'nombre' => $curso->curso_nombre,
                    'codigo' => $curso->curso_codigo,
                    'descripcion' => $curso->curso_descripcion,
                    'imagen' => $curso->id_archivo, // Usamos id_archivo para el frontend
                    'docente' => $curso->docente_nombre,
                    'periodo' => $curso->periodo_nombre,
                    'empresa' => $curso->empresa_nombre,
                    'fecha_inicio' => $curso->fecha_inicio,
                    'progreso' => $progreso['porcentaje'],
                    'items_completados' => $progreso['completado'],
                    'items_total' => $progreso['total'],
                    'estado' => $progreso['porcentaje'] >= 100 ? 'finalizado' : ($progreso['porcentaje'] > 0 ? 'en-progreso' : 'por-empezar')
                ];
            });

            return response()->json([
                'data' => $resultado,
                'total' => $cursos->total(),
                'per_page' => $cursos->perPage(),
                'current_page' => $cursos->currentPage(),
                'last_page' => $cursos->lastPage(),
            ]);

        } catch (\Exception $e) {
            \Log::error("Error listarMisCursosProgreso: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json(['mensaje' => 'Error al listar cursos con progreso: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // Métodos auxiliares privados
    // ==========================================

    /**
     * Cuenta el total de contenido en un curso
     */
    private function contarTotalContenido($id_periodocurso): int
    {
        // Obtener todos los módulos del curso
        $modulosIds = PeriodoModulo::where('id_periodocurso', $id_periodocurso)
            ->pluck('id_periodomodulo');

        if ($modulosIds->isEmpty()) {
            return 0;
        }

        // Obtener todos los temas de los módulos
        $temasIds = PeriodoTema::whereIn('id_periodomodulo', $modulosIds)
            ->pluck('id_periodotema');

        if ($temasIds->isEmpty()) {
            return 0;
        }

        // Contar videos, tareas y cuestionarios
        $totalVideos = PeriodoVideo::whereIn('id_periodotema', $temasIds)->count();
        $totalTareas = PeriodoTarea::whereIn('id_periodotema', $temasIds)->count();
        $totalCuestionarios = PeriodoCuestionario::whereIn('id_periodotema', $temasIds)->count();

        return $totalVideos + $totalTareas + $totalCuestionarios;
    }

    /**
     * Calcula el progreso del curso
     */
    private function calcularProgresoCurso($id_usuario, $id_periodocurso): array
    {
        $total = $this->contarTotalContenido($id_periodocurso);

        $completado = ProgresoUsuarioContenido::where('id_usuario', $id_usuario)
            ->where('id_periodocurso', $id_periodocurso)
            ->where('completado', true)
            ->count();

        $porcentaje = $total > 0 ? round(($completado / $total) * 100, 2) : 0;

        return [
            'total' => $total,
            'completado' => $completado,
            'porcentaje' => $porcentaje,
        ];
    }

    /**
     * Verifica que el contenido existe
     */
    private function verificarContenidoExiste($tipo, $id): bool
    {
        return match ($tipo) {
            'video' => PeriodoVideo::where('id_periodovideo', $id)->exists(),
            'tarea' => PeriodoTarea::where('id_periodotarea', $id)->exists(),
            'cuestionario' => PeriodoCuestionario::where('id_periodocuestionario', $id)->exists(),
            default => false,
        };
    }

    /**
     * Obtiene el desglose del progreso por tipo de contenido
     */
    private function obtenerDesgloseProgreso($id_usuario, $id_periodocurso): array
    {
        // Obtener IDs de módulos y temas
        $modulosIds = PeriodoModulo::where('id_periodocurso', $id_periodocurso)
            ->pluck('id_periodomodulo');

        $temasIds = PeriodoTema::whereIn('id_periodomodulo', $modulosIds)
            ->pluck('id_periodotema');

        if ($temasIds->isEmpty()) {
            return [
                'videos' => ['total' => 0, 'completados' => 0],
                'tareas' => ['total' => 0, 'completados' => 0],
                'cuestionarios' => ['total' => 0, 'completados' => 0],
            ];
        }

        // Contar totales
        $totalVideos = PeriodoVideo::whereIn('id_periodotema', $temasIds)->count();
        $totalTareas = PeriodoTarea::whereIn('id_periodotema', $temasIds)->count();
        $totalCuestionarios = PeriodoCuestionario::whereIn('id_periodotema', $temasIds)->count();

        // Contar completados
        $videosCompletados = ProgresoUsuarioContenido::where('id_usuario', $id_usuario)
            ->where('id_periodocurso', $id_periodocurso)
            ->where('tipo_contenido', 'video')
            ->where('completado', true)
            ->count();

        $tareasCompletadas = ProgresoUsuarioContenido::where('id_usuario', $id_usuario)
            ->where('id_periodocurso', $id_periodocurso)
            ->where('tipo_contenido', 'tarea')
            ->where('completado', true)
            ->count();

        $cuestionariosCompletados = ProgresoUsuarioContenido::where('id_usuario', $id_usuario)
            ->where('id_periodocurso', $id_periodocurso)
            ->where('tipo_contenido', 'cuestionario')
            ->where('completado', true)
            ->count();

        return [
            'videos' => ['total' => $totalVideos, 'completados' => $videosCompletados],
            'tareas' => ['total' => $totalTareas, 'completados' => $tareasCompletadas],
            'cuestionarios' => ['total' => $totalCuestionarios, 'completados' => $cuestionariosCompletados],
        ];
    }
}
