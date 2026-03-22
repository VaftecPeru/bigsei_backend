<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TutorController extends Controller
{
    /**
     * Helper: obtener id_tutor del usuario autenticado
     */
    private function getTutorId(Request $request)
    {
        $user = $request->user();
        return $user ? $user->id_persona : null;
    }

    /**
     * Dashboard del tutor: estadísticas principales
     */
    public function dashboard(Request $request)
    {
        $idTutor = $this->getTutorId($request);
        $idEmpresa = $request->user()->id_empresa ?? null;

        // Total estudiantes asignados
        $totalEstudiantes = DB::table('tutor_estudiante')
            ->where('id_tutor', $idTutor)
            ->where('estado', '1')
            ->when($idEmpresa, fn($q) => $q->where('id_empresa', $idEmpresa))
            ->count();

        // Sesiones programadas (futuras)
        $sesionesProgramadas = DB::table('tutoria_sesion')
            ->where('id_tutor', $idTutor)
            ->where('estado', 'programada')
            ->where('fecha', '>=', Carbon::today())
            ->when($idEmpresa, fn($q) => $q->where('id_empresa', $idEmpresa))
            ->count();

        // Sesiones completadas este mes
        $sesionesCompletadasMes = DB::table('tutoria_sesion')
            ->where('id_tutor', $idTutor)
            ->where('estado', 'completada')
            ->whereMonth('fecha', Carbon::now()->month)
            ->whereYear('fecha', Carbon::now()->year)
            ->when($idEmpresa, fn($q) => $q->where('id_empresa', $idEmpresa))
            ->count();

        // Alertas de riesgo
        $alertas = $this->getAlertasRiesgoData($idTutor, $idEmpresa);
        $totalAlertas = count($alertas);

        // Próximas sesiones (5)
        $proximasSesiones = DB::table('tutoria_sesion as ts')
            ->join('persona as p', 'ts.id_estudiante', '=', 'p.id_persona')
            ->where('ts.id_tutor', $idTutor)
            ->where('ts.estado', 'programada')
            ->where('ts.fecha', '>=', Carbon::today())
            ->select('ts.id', 'p.nombre_completo', 'ts.fecha', 'ts.hora_inicio', 'ts.tema')
            ->orderBy('ts.fecha', 'asc')
            ->orderBy('ts.hora_inicio', 'asc')
            ->limit(5)
            ->get();

        return response()->json([
            'total_estudiantes'       => $totalEstudiantes,
            'sesiones_programadas'    => $sesionesProgramadas,
            'sesiones_completadas_mes' => $sesionesCompletadasMes,
            'total_alertas'           => $totalAlertas,
            'proximas_sesiones'       => $proximasSesiones,
        ]);
    }

    /**
     * Mis estudiantes: lista con indicadores de progreso
     */
    public function misEstudiantes(Request $request)
    {
        $idTutor = $this->getTutorId($request);
        $idEmpresa = $request->user()->id_empresa ?? null;

        $query = DB::table('tutor_estudiante as te')
            ->join('persona as p', 'te.id_estudiante', '=', 'p.id_persona')
            ->leftJoin('usuario as u', 'p.id_persona', '=', 'u.id_usuario')
            ->where('te.id_tutor', $idTutor)
            ->where('te.estado', '1')
            ->when($idEmpresa, fn($q) => $q->where('te.id_empresa', $idEmpresa))
            ->select(
                'te.id',
                'te.id_estudiante',
                'p.nombre_completo',
                'p.correo',
                'p.telefono',
                'te.fecha_asignacion',
                'u.username'
            );

        if ($request->text_search) {
            $texto = str_replace(' ', '%', $request->text_search);
            $query->whereRaw("UPPER(CONCAT(COALESCE(p.nombre_completo,''), COALESCE(p.correo,''))) LIKE UPPER(?)", ['%' . $texto . '%']);
        }

        $estudiantes = $query->orderBy('p.nombre_completo', 'asc')->get();

        // Enriquecer con datos académicos
        foreach ($estudiantes as &$est) {
            // Contar sesiones
            $est->total_sesiones = DB::table('tutoria_sesion')
                ->where('id_tutor', $idTutor)
                ->where('id_estudiante', $est->id_estudiante)
                ->count();

            // Contar observaciones
            $est->total_observaciones = DB::table('tutoria_observacion')
                ->where('id_tutor', $idTutor)
                ->where('id_estudiante', $est->id_estudiante)
                ->count();

            // Promedio de notas (si hay evaluaciones)
            $promedioNotas = DB::table('evaluaciones_notas')
                ->where('idUsuario', $est->id_estudiante)
                ->avg('nota');
            $est->promedio_notas = $promedioNotas ? round($promedioNotas, 2) : null;

            // Porcentaje de asistencia
            $totalAsistencias = DB::table('curso_asistencia as ca')
                ->join('curso_estudiante as ce', 'ca.idCursoEstudiante', '=', 'ce.idCursoEstudiante')
                ->join('estudiante as est2', 'ce.id_estudiante', '=', 'est2.id_estudiante')
                ->where('est2.id_estudiante', $est->id_estudiante)
                ->count();
            $asistio = DB::table('curso_asistencia as ca')
                ->join('curso_estudiante as ce', 'ca.idCursoEstudiante', '=', 'ce.idCursoEstudiante')
                ->join('estudiante as est2', 'ce.id_estudiante', '=', 'est2.id_estudiante')
                ->where('est2.id_estudiante', $est->id_estudiante)
                ->where('ca.estado', 'A')
                ->count();
            $est->porcentaje_asistencia = $totalAsistencias > 0
                ? round(($asistio / $totalAsistencias) * 100, 1)
                : null;
        }

        return response()->json($estudiantes);
    }

    /**
     * Agenda de tutorías: listar sesiones
     */
    public function agendaSesiones(Request $request)
    {
        $idTutor = $this->getTutorId($request);
        $idEmpresa = $request->user()->id_empresa ?? null;

        $query = DB::table('tutoria_sesion as ts')
            ->join('persona as p', 'ts.id_estudiante', '=', 'p.id_persona')
            ->where('ts.id_tutor', $idTutor)
            ->when($idEmpresa, fn($q) => $q->where('ts.id_empresa', $idEmpresa))
            ->select(
                'ts.id',
                'ts.id_estudiante',
                'p.nombre_completo',
                'ts.fecha',
                'ts.hora_inicio',
                'ts.hora_fin',
                'ts.tema',
                'ts.estado'
            );

        if ($request->estado) {
            $query->where('ts.estado', $request->estado);
        }

        if ($request->fecha_desde && $request->fecha_hasta) {
            $query->whereBetween('ts.fecha', [$request->fecha_desde, $request->fecha_hasta]);
        }

        $query->orderBy('ts.fecha', 'desc')->orderBy('ts.hora_inicio', 'desc');

        return response()->json($query->get());
    }

    /**
     * Crear sesión de tutoría
     */
    public function storeSesion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_estudiante' => 'required|integer',
            'fecha'         => 'required|date',
            'hora_inicio'   => 'required',
            'hora_fin'      => 'nullable',
            'tema'          => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $idTutor = $this->getTutorId($request);
        $idEmpresa = $request->user()->id_empresa ?? null;

        DB::beginTransaction();
        try {
            $id = DB::table('tutoria_sesion')->insertGetId([
                'id_tutor'      => $idTutor,
                'id_estudiante' => $request->id_estudiante,
                'id_empresa'    => $idEmpresa,
                'fecha'         => $request->fecha,
                'hora_inicio'   => $request->hora_inicio,
                'hora_fin'      => $request->hora_fin,
                'tema'          => $request->tema,
                'estado'        => 'programada',
            ]);
            DB::commit();
            return response()->json(['message' => 'Sesión programada correctamente', 'id' => $id], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al crear sesión: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar sesión de tutoría
     */
    public function updateSesion(Request $request, $id)
    {
        $sesion = DB::table('tutoria_sesion')->where('id', $id)->first();
        if (!$sesion) {
            return response()->json(['error' => 'Sesión no encontrada'], 404);
        }

        $data = array_filter([
            'fecha'       => $request->fecha,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin'    => $request->hora_fin,
            'tema'        => $request->tema,
            'estado'      => $request->estado,
        ], fn($v) => !is_null($v));

        DB::table('tutoria_sesion')->where('id', $id)->update($data);

        return response()->json(['message' => 'Sesión actualizada correctamente']);
    }

    /**
     * Listar observaciones de un estudiante
     */
    public function observaciones(Request $request)
    {
        $idTutor = $this->getTutorId($request);

        $query = DB::table('tutoria_observacion as to2')
            ->join('persona as p', 'to2.id_estudiante', '=', 'p.id_persona')
            ->leftJoin('tutoria_sesion as ts', 'to2.id_sesion', '=', 'ts.id')
            ->where('to2.id_tutor', $idTutor)
            ->select(
                'to2.id',
                'to2.id_estudiante',
                'p.nombre_completo',
                'to2.observacion',
                'to2.tipo',
                'to2.created_at',
                'ts.fecha as fecha_sesion',
                'ts.tema as tema_sesion'
            );

        if ($request->id_estudiante) {
            $query->where('to2.id_estudiante', $request->id_estudiante);
        }

        $query->orderBy('to2.created_at', 'desc');

        return response()->json($query->get());
    }

    /**
     * Crear observación
     */
    public function storeObservacion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_estudiante' => 'required|integer',
            'observacion'   => 'required|string',
            'tipo'          => 'nullable|string|in:general,academico,conductual,emocional',
            'id_sesion'     => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $idTutor = $this->getTutorId($request);
        $idEmpresa = $request->user()->id_empresa ?? null;

        DB::beginTransaction();
        try {
            $id = DB::table('tutoria_observacion')->insertGetId([
                'id_tutor'      => $idTutor,
                'id_estudiante' => $request->id_estudiante,
                'id_empresa'    => $idEmpresa,
                'observacion'   => $request->observacion,
                'tipo'          => $request->tipo ?? 'general',
                'id_sesion'     => $request->id_sesion,
            ]);
            DB::commit();
            return response()->json(['message' => 'Observación registrada correctamente', 'id' => $id], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al registrar observación: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Alertas de riesgo: estudiantes tutorados con notas bajas o faltas altas
     */
    public function alertasRiesgo(Request $request)
    {
        $idTutor = $this->getTutorId($request);
        $idEmpresa = $request->user()->id_empresa ?? null;
        $alertas = $this->getAlertasRiesgoData($idTutor, $idEmpresa);

        return response()->json($alertas);
    }

    /**
     * Helper: obtener datos de alertas de riesgo
     */
    private function getAlertasRiesgoData($idTutor, $idEmpresa = null)
    {
        $estudiantes = DB::table('tutor_estudiante as te')
            ->join('persona as p', 'te.id_estudiante', '=', 'p.id_persona')
            ->where('te.id_tutor', $idTutor)
            ->where('te.estado', '1')
            ->when($idEmpresa, fn($q) => $q->where('te.id_empresa', $idEmpresa))
            ->select('te.id_estudiante', 'p.nombre_completo', 'p.correo')
            ->get();

        $alertas = [];

        foreach ($estudiantes as $est) {
            $motivos = [];

            // Notas bajas (promedio < 11)
            $promedio = DB::table('evaluaciones_notas')
                ->where('idUsuario', $est->id_estudiante)
                ->avg('nota');

            if ($promedio !== null && $promedio < 11) {
                $motivos[] = 'Promedio bajo: ' . round($promedio, 2);
            }

            // Baja asistencia (< 70%)
            $totalAsistencias = DB::table('curso_asistencia as ca')
                ->join('curso_estudiante as ce', 'ca.idCursoEstudiante', '=', 'ce.idCursoEstudiante')
                ->join('estudiante as est2', 'ce.id_estudiante', '=', 'est2.id_estudiante')
                ->where('est2.id_estudiante', $est->id_estudiante)
                ->count();
            $asistio = DB::table('curso_asistencia as ca')
                ->join('curso_estudiante as ce', 'ca.idCursoEstudiante', '=', 'ce.idCursoEstudiante')
                ->join('estudiante as est2', 'ce.id_estudiante', '=', 'est2.id_estudiante')
                ->where('est2.id_estudiante', $est->id_estudiante)
                ->where('ca.estado', 'A')
                ->count();

            if ($totalAsistencias > 0) {
                $porcentaje = ($asistio / $totalAsistencias) * 100;
                if ($porcentaje < 70) {
                    $motivos[] = 'Asistencia baja: ' . round($porcentaje, 1) . '%';
                }
            }

            if (!empty($motivos)) {
                $alertas[] = [
                    'id_estudiante'   => $est->id_estudiante,
                    'nombre_completo' => $est->nombre_completo,
                    'correo'          => $est->correo,
                    'motivos'         => $motivos,
                ];
            }
        }

        return $alertas;
    }
}
