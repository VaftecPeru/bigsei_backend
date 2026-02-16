<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DirectorDashboardController extends Controller
{
    /**
     * Task 1: Indicadores educativos
     * Tasa de aprobación, deserción, asistencia promedio
     */
    public function indicadoresEducativos(Request $request)
    {
        $user = $request->sessionUser;
        $id_empresa = $user->id_empresa;

        // Tasa de aprobación: estudiantes con nota promedio >= 11 / total evaluados
        $aprobacion = DB::table('evaluacion_nota as en')
            ->join('periodo_curso as pc', 'en.id_periodocurso', 'pc.id_periodocurso')
            ->where('pc.id_empresa', $id_empresa)
            ->selectRaw('
                COUNT(DISTINCT en.id_estudiante) as total_evaluados,
                COUNT(DISTINCT CASE WHEN en.nota >= 11 THEN en.id_estudiante END) as aprobados
            ')
            ->first();

        $tasa_aprobacion = $aprobacion && $aprobacion->total_evaluados > 0
            ? round(($aprobacion->aprobados / $aprobacion->total_evaluados) * 100, 1)
            : 0;

        // Tasa de deserción: matriculas con estado != 1 / total matriculas
        $matriculas = DB::table('matricula')
            ->where('id_empresa', $id_empresa)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN estado != '1' THEN 1 ELSE 0 END) as desertores
            ")
            ->first();

        $tasa_desercion = $matriculas && $matriculas->total > 0
            ? round(($matriculas->desertores / $matriculas->total) * 100, 1)
            : 0;

        // Promedio de asistencia
        $asistencia = DB::table('asistencia as a')
            ->join('periodo_curso as pc', 'a.id_periodocurso', 'pc.id_periodocurso')
            ->where('pc.id_empresa', $id_empresa)
            ->where('a.tipo', 'E')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN a.estado = 'A' OR a.estado = 'T' THEN 1 ELSE 0 END) as asistieron
            ")
            ->first();

        $tasa_asistencia = $asistencia && $asistencia->total > 0
            ? round(($asistencia->asistieron / $asistencia->total) * 100, 1)
            : 0;

        // Total de estudiantes activos
        $total_estudiantes = DB::table('estudiante')
            ->where('estado', '1')
            ->count();

        // Total de docentes activos
        $total_docentes = DB::table('docente')
            ->where('estado', '1')
            ->count();

        return response()->json([
            'tasa_aprobacion' => $tasa_aprobacion,
            'tasa_desercion' => $tasa_desercion,
            'tasa_asistencia' => $tasa_asistencia,
            'total_estudiantes' => $total_estudiantes,
            'total_docentes' => $total_docentes,
        ]);
    }

    /**
     * Task 6: Comparar periodos
     * Comparar estadísticas entre dos periodos
     */
    public function compararPeriodos(Request $request)
    {
        $user = $request->sessionUser;
        $id_empresa = $user->id_empresa;
        $id_periodo1 = $request->id_periodo1;
        $id_periodo2 = $request->id_periodo2;

        $periodos = [];

        foreach ([$id_periodo1, $id_periodo2] as $id_periodo) {
            if (!$id_periodo) continue;

            $nombre = DB::table('periodo')
                ->where('id_periodo', $id_periodo)
                ->where('id_empresa', $id_empresa)
                ->value('nombre');

            // Cantidad de estudiantes matriculados en el periodo
            $estudiantes = DB::table('matricula')
                ->where('id_periodo', $id_periodo)
                ->where('id_empresa', $id_empresa)
                ->count();

            // Cantidad de cursos en el periodo
            $cursos = DB::table('periodo_curso')
                ->join('periodo_ciclo', 'periodo_curso.id_periodociclo', 'periodo_ciclo.id_periodociclo')
                ->where('periodo_ciclo.id_periodo', $id_periodo)
                ->where('periodo_curso.id_empresa', $id_empresa)
                ->count();

            // Tasa de aprobación del periodo
            $aprobacion = DB::table('evaluacion_nota as en')
                ->join('periodo_curso as pc', 'en.id_periodocurso', 'pc.id_periodocurso')
                ->join('periodo_ciclo as pcl', 'pc.id_periodociclo', 'pcl.id_periodociclo')
                ->where('pcl.id_periodo', $id_periodo)
                ->where('pc.id_empresa', $id_empresa)
                ->selectRaw('
                    COUNT(DISTINCT en.id_estudiante) as total,
                    COUNT(DISTINCT CASE WHEN en.nota >= 11 THEN en.id_estudiante END) as aprobados
                ')
                ->first();

            $tasa = $aprobacion && $aprobacion->total > 0
                ? round(($aprobacion->aprobados / $aprobacion->total) * 100, 1)
                : 0;

            // Asistencia del periodo
            $asistencia = DB::table('asistencia as a')
                ->join('periodo_curso as pc', 'a.id_periodocurso', 'pc.id_periodocurso')
                ->join('periodo_ciclo as pcl', 'pc.id_periodociclo', 'pcl.id_periodociclo')
                ->where('pcl.id_periodo', $id_periodo)
                ->where('pc.id_empresa', $id_empresa)
                ->where('a.tipo', 'E')
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN a.estado = 'A' OR a.estado = 'T' THEN 1 ELSE 0 END) as asistieron
                ")
                ->first();

            $tasa_asistencia = $asistencia && $asistencia->total > 0
                ? round(($asistencia->asistieron / $asistencia->total) * 100, 1)
                : 0;

            $periodos[] = [
                'id_periodo' => $id_periodo,
                'nombre' => $nombre,
                'estudiantes' => $estudiantes,
                'cursos' => $cursos,
                'tasa_aprobacion' => $tasa,
                'tasa_asistencia' => $tasa_asistencia,
            ];
        }

        return response()->json($periodos);
    }

    /**
     * Task 7: Progreso por carrera
     * Ver cómo van los estudiantes en cada carrera
     */
    public function progresoCarrera(Request $request)
    {
        $user = $request->sessionUser;
        $id_empresa = $user->id_empresa;

        $carreras = DB::table('carrera as c')
            ->where('c.id_empresa', $id_empresa)
            ->select('c.id_carrera', 'c.nombre')
            ->get();

        $resultado = [];

        foreach ($carreras as $carrera) {
            // Estudiantes matriculados en esta carrera
            $estudiantesMatriculados = DB::table('periodo_ciclo as pcl')
                ->join('periodo_curso as pc', 'pcl.id_periodociclo', 'pc.id_periodociclo')
                ->join('asistencia as a', function ($join) {
                    $join->on('pc.id_periodocurso', '=', 'a.id_periodocurso')
                        ->where('a.tipo', '=', 'E');
                })
                ->where('pcl.id_carrera', $carrera->id_carrera)
                ->where('pcl.id_empresa', $id_empresa)
                ->distinct()
                ->count('a.id_estudiante');

            // Nota promedio en esta carrera
            $promedioNota = DB::table('evaluacion_nota as en')
                ->join('periodo_curso as pc', 'en.id_periodocurso', 'pc.id_periodocurso')
                ->join('periodo_ciclo as pcl', 'pc.id_periodociclo', 'pcl.id_periodociclo')
                ->where('pcl.id_carrera', $carrera->id_carrera)
                ->where('pc.id_empresa', $id_empresa)
                ->avg('en.nota');

            // Tasa de asistencia en esta carrera
            $asistencia = DB::table('asistencia as a')
                ->join('periodo_curso as pc', 'a.id_periodocurso', 'pc.id_periodocurso')
                ->join('periodo_ciclo as pcl', 'pc.id_periodociclo', 'pcl.id_periodociclo')
                ->where('pcl.id_carrera', $carrera->id_carrera)
                ->where('pc.id_empresa', $id_empresa)
                ->where('a.tipo', 'E')
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN a.estado = 'A' OR a.estado = 'T' THEN 1 ELSE 0 END) as asistieron
                ")
                ->first();

            $tasa_asistencia = $asistencia && $asistencia->total > 0
                ? round(($asistencia->asistieron / $asistencia->total) * 100, 1)
                : 0;

            $resultado[] = [
                'id_carrera' => $carrera->id_carrera,
                'carrera' => $carrera->nombre,
                'estudiantes' => $estudiantesMatriculados,
                'promedio_nota' => $promedioNota ? round($promedioNota, 1) : 0,
                'tasa_asistencia' => $tasa_asistencia,
            ];
        }

        return response()->json($resultado);
    }

    /**
     * Task 8: Rendimiento de docentes
     * Métricas de cómo van los docentes
     */
    public function rendimientoDocentes(Request $request)
    {
        $user = $request->sessionUser;
        $id_empresa = $user->id_empresa;

        $docentes = DB::table('docente as d')
            ->join('persona as p', 'd.id_docente', 'p.id_persona')
            ->where('d.estado', '1')
            ->select('d.id_docente', 'p.nombre_completo', 'p.correo')
            ->get();

        $resultado = [];

        foreach ($docentes as $docente) {
            // Cursos asignados
            $cursos = DB::table('periodo_curso')
                ->where('id_docente', $docente->id_docente)
                ->where('id_empresa', $id_empresa)
                ->count();

            // Nota promedio de sus estudiantes
            $promedio = DB::table('evaluacion_nota as en')
                ->join('periodo_curso as pc', 'en.id_periodocurso', 'pc.id_periodocurso')
                ->where('pc.id_docente', $docente->id_docente)
                ->where('pc.id_empresa', $id_empresa)
                ->avg('en.nota');

            // Cantidad de estudiantes
            $totalEstudiantes = DB::table('asistencia as a')
                ->join('periodo_curso as pc', 'a.id_periodocurso', 'pc.id_periodocurso')
                ->where('pc.id_docente', $docente->id_docente)
                ->where('pc.id_empresa', $id_empresa)
                ->where('a.tipo', 'E')
                ->distinct()
                ->count('a.id_estudiante');

            // Tasa de asistencia de sus cursos
            $asistencia = DB::table('asistencia as a')
                ->join('periodo_curso as pc', 'a.id_periodocurso', 'pc.id_periodocurso')
                ->where('pc.id_docente', $docente->id_docente)
                ->where('pc.id_empresa', $id_empresa)
                ->where('a.tipo', 'E')
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN a.estado = 'A' OR a.estado = 'T' THEN 1 ELSE 0 END) as asistieron
                ")
                ->first();

            $tasa_asistencia = $asistencia && $asistencia->total > 0
                ? round(($asistencia->asistieron / $asistencia->total) * 100, 1)
                : 0;

            $resultado[] = [
                'id_docente' => $docente->id_docente,
                'nombre' => $docente->nombre_completo,
                'correo' => $docente->correo,
                'cursos_asignados' => $cursos,
                'promedio_notas_estudiantes' => $promedio ? round($promedio, 1) : 0,
                'total_estudiantes' => $totalEstudiantes,
                'tasa_asistencia' => $tasa_asistencia,
            ];
        }

        return response()->json($resultado);
    }

    /**
     * Task 9: Estudiantes en riesgo
     * Alertas automáticas cuando un alumno tiene muchas faltas o notas bajas
     */
    public function estudiantesEnRiesgo(Request $request)
    {
        $user = $request->sessionUser;
        $id_empresa = $user->id_empresa;

        // Estudiantes con alto índice de inasistencia (>30%)
        $inasistencia = DB::table('asistencia as a')
            ->join('periodo_curso as pc', 'a.id_periodocurso', 'pc.id_periodocurso')
            ->join('persona as p', 'a.id_estudiante', 'p.id_persona')
            ->where('pc.id_empresa', $id_empresa)
            ->where('a.tipo', 'E')
            ->groupBy('a.id_estudiante', 'p.nombre_completo', 'p.correo', 'p.numero_documento')
            ->havingRaw("SUM(CASE WHEN a.estado = 'F' THEN 1 ELSE 0 END) / COUNT(*) > 0.3")
            ->selectRaw("
                a.id_estudiante,
                p.nombre_completo,
                p.correo,
                p.numero_documento,
                COUNT(*) as total_registros,
                SUM(CASE WHEN a.estado = 'F' THEN 1 ELSE 0 END) as inasistencias,
                ROUND(SUM(CASE WHEN a.estado = 'F' THEN 1 ELSE 0 END) / COUNT(*) * 100, 1) as porcentaje_inasistencia
            ")
            ->get();

        // Estudiantes con nota promedio baja (<11)
        $notas_bajas = DB::table('evaluacion_nota as en')
            ->join('periodo_curso as pc', 'en.id_periodocurso', 'pc.id_periodocurso')
            ->join('persona as p', 'en.id_estudiante', 'p.id_persona')
            ->where('pc.id_empresa', $id_empresa)
            ->groupBy('en.id_estudiante', 'p.nombre_completo', 'p.correo', 'p.numero_documento')
            ->havingRaw('AVG(en.nota) < 11')
            ->selectRaw('
                en.id_estudiante,
                p.nombre_completo,
                p.correo,
                p.numero_documento,
                ROUND(AVG(en.nota), 1) as promedio_nota,
                COUNT(DISTINCT en.id_periodocurso) as cursos_evaluados
            ')
            ->get();

        // Combinar y deduplicar
        $riesgo = collect();

        foreach ($inasistencia as $est) {
            $riesgo->put($est->id_estudiante, [
                'id_estudiante' => $est->id_estudiante,
                'nombre' => $est->nombre_completo,
                'correo' => $est->correo,
                'documento' => $est->numero_documento,
                'motivos' => ['Alta inasistencia: ' . $est->porcentaje_inasistencia . '%'],
                'porcentaje_inasistencia' => $est->porcentaje_inasistencia,
                'promedio_nota' => null,
            ]);
        }

        foreach ($notas_bajas as $est) {
            if ($riesgo->has($est->id_estudiante)) {
                $item = $riesgo->get($est->id_estudiante);
                $item['motivos'][] = 'Nota promedio baja: ' . $est->promedio_nota;
                $item['promedio_nota'] = $est->promedio_nota;
                $riesgo->put($est->id_estudiante, $item);
            } else {
                $riesgo->put($est->id_estudiante, [
                    'id_estudiante' => $est->id_estudiante,
                    'nombre' => $est->nombre_completo,
                    'correo' => $est->correo,
                    'documento' => $est->numero_documento,
                    'motivos' => ['Nota promedio baja: ' . $est->promedio_nota],
                    'porcentaje_inasistencia' => null,
                    'promedio_nota' => $est->promedio_nota,
                ]);
            }
        }

        return response()->json($riesgo->values());
    }
}
