<?php

namespace App\Http\Controllers\Padre;

use App\Http\Controllers\Controller;
use App\Models\PadreHijo;
use App\Models\CursoEstudiantes;
use App\Models\CarreraEstudiantes;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class PadreController extends Controller
{
    /**
     * Obtiene el idUsuario del padre autenticado mediante el token JWT.
     */
    private function getIdPadre(): int
    {
        $payload = JWTAuth::parseToken()->getPayload();
        return $payload->get('idUsuario');
    }

    /**
     * GET /padre/mis-hijos
     * Lista todos los hijos vinculados al padre autenticado.
     */
    public function misHijos(Request $request)
    {
        $idPadre = $this->getIdPadre();

        $hijos = PadreHijo::where('idPadre', $idPadre)
            ->with(['hijo' => function ($q) {
                $q->select('idUsuario', 'nombres', 'apellidoPaterno', 'apellidoMaterno', 'foto', 'dni', 'correo', 'telefono');
            }])
            ->get()
            ->map(function ($rel) {
                $hijo = $rel->hijo;
                if (!$hijo) return null;

                // Carrera del hijo
                $carreraRel = CarreraEstudiantes::where('idEstudiante', $hijo->idUsuario)
                    ->with('carrera:idCarrera,nombreCarrera')
                    ->first();

                // Cursos activos del hijo
                $totalCursos = CursoEstudiantes::where('idUsuario', $hijo->idUsuario)->count();

                return [
                    'idHijo'           => $hijo->idUsuario,
                    'nombre'           => $hijo->nombres . ' ' . $hijo->apellidoPaterno . ' ' . $hijo->apellidoMaterno,
                    'dni'              => $hijo->dni,
                    'correo'           => $hijo->correo,
                    'telefono'         => $hijo->telefono,
                    'foto'             => $hijo->foto,
                    'carrera'          => $carreraRel?->carrera?->nombreCarrera ?? 'Sin carrera',
                    'totalCursos'      => $totalCursos,
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'success' => true,
            'data'    => $hijos,
        ]);
    }

    /**
     * GET /padre/mis-hijos/{idHijo}/cursos
     * Lista los cursos matriculados del hijo.
     */
    public function cursosHijo(Request $request, $idHijo)
    {
        $idPadre = $this->getIdPadre();

        if (!$this->esMiHijo($idPadre, $idHijo)) {
            return response()->json(['success' => false, 'message' => 'No tiene permiso para ver este hijo'], 403);
        }

        $cursos = DB::table('periodo_cursos as pc')
            ->join('matricula_cursos as mc', 'pc.idPeriodoCurso', '=', 'mc.idPeriodoCurso')
            ->join('matriculas as m', 'mc.idMatricula', '=', 'm.idMatricula')
            ->join('cursos as c', 'pc.idCurso', '=', 'c.idCurso')
            ->leftJoin('docentes as d', 'pc.idDocente', '=', 'd.idDocente')
            ->leftJoin('usuario as ud', 'd.idUsuario', '=', 'ud.idUsuario')
            ->where('m.idUsuario', $idHijo)
            ->select(
                'pc.idPeriodoCurso',
                'c.nombreCurso',
                'c.codigoCurso',
                'c.creditos',
                DB::raw("CONCAT(ud.nombres, ' ', ud.apellidoPaterno) as nombreDocente")
            )
            ->get();

        return response()->json(['success' => true, 'data' => $cursos]);
    }

    /**
     * GET /padre/mis-hijos/{idHijo}/notas
     * Muestra las notas del hijo seleccionado.
     */
    public function notasHijo(Request $request, $idHijo)
    {
        $idPadre = $this->getIdPadre();

        if (!$this->esMiHijo($idPadre, $idHijo)) {
            return response()->json(['success' => false, 'message' => 'No tiene permiso para ver este hijo'], 403);
        }

        $notas = DB::table('evaluaciones_notas as en')
            ->join('periodo_cursos as pc', 'en.idPeriodoCurso', '=', 'pc.idPeriodoCurso')
            ->join('cursos as c', 'pc.idCurso', '=', 'c.idCurso')
            ->join('matricula_cursos as mc', 'pc.idPeriodoCurso', '=', 'mc.idPeriodoCurso')
            ->join('matriculas as m', 'mc.idMatricula', '=', 'm.idMatricula')
            ->where('m.idUsuario', $idHijo)
            ->where('en.idUsuario', $idHijo)
            ->select(
                'c.nombreCurso',
                'c.codigoCurso',
                'en.nota',
                'en.descripcion',
                'en.created_at as fecha'
            )
            ->orderBy('c.nombreCurso')
            ->get();

        return response()->json(['success' => true, 'data' => $notas]);
    }

    /**
     * GET /padre/mis-hijos/{idHijo}/asistencia
     * Muestra la asistencia del hijo por curso.
     */
    public function asistenciaHijo(Request $request, $idHijo)
    {
        $idPadre = $this->getIdPadre();

        if (!$this->esMiHijo($idPadre, $idHijo)) {
            return response()->json(['success' => false, 'message' => 'No tiene permiso para ver este hijo'], 403);
        }

        $asistencia = DB::table('curso_asistencia as ca')
            ->join('curso_estudiantes as ce', 'ca.idCursoEstudiante', '=', 'ce.idCursoEstudiante')
            ->join('curso as c', 'ce.idCurso', '=', 'c.idCurso')
            ->where('ce.idUsuario', $idHijo)
            ->select(
                'c.nombreCurso',
                DB::raw('COUNT(*) as total_registros'),
                DB::raw('SUM(CASE WHEN ca.estado = "presente" THEN 1 ELSE 0 END) as asistencias'),
                DB::raw('SUM(CASE WHEN ca.estado = "ausente" THEN 1 ELSE 0 END) as ausencias'),
                DB::raw('SUM(CASE WHEN ca.estado = "falta_justificada" THEN 1 ELSE 0 END) as justificadas'),
                DB::raw('ROUND(SUM(CASE WHEN ca.estado = "presente" THEN 1 ELSE 0 END) / COUNT(*) * 100, 1) as porcentaje_asistencia')
            )
            ->groupBy('c.idCurso', 'c.nombreCurso')
            ->get();

        return response()->json(['success' => true, 'data' => $asistencia]);
    }

    /**
     * GET /padre/mis-hijos/{idHijo}/pagos
     * Muestra los pagos y deudas del hijo.
     */
    public function pagosHijo(Request $request, $idHijo)
    {
        $idPadre = $this->getIdPadre();

        if (!$this->esMiHijo($idPadre, $idHijo)) {
            return response()->json(['success' => false, 'message' => 'No tiene permiso para ver este hijo'], 403);
        }

        // Pagos realizados
        $pagos = DB::table('pago')
            ->join('metodo_pago', 'pago.idMetodoPago', '=', 'metodo_pago.idMetodoPago')
            ->where('pago.idUsuario', $idHijo)
            ->select(
                'pago.idPago',
                'pago.descripcion',
                'pago.importe',
                'pago.igv',
                'pago.total',
                'pago.fechaPago',
                'metodo_pago.nombre as metodoPago'
            )
            ->orderBy('pago.fechaPago', 'desc')
            ->get();

        // Deudas pendientes
        $deudas = DB::table('deudas')
            ->where('idUsuario', $idHijo)
            ->where('estado', 'pendiente')
            ->select('idDeuda', 'descripcion', 'monto', 'fechaVencimiento', 'estado')
            ->orderBy('fechaVencimiento')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'pagos'  => $pagos,
                'deudas' => $deudas,
                'totales' => [
                    'totalPagado' => $pagos->sum('total'),
                    'totalDeuda'  => $deudas->sum('monto'),
                ],
            ],
        ]);
    }

    /**
     * GET /padre/dashboard
     * Resumen de todos los hijos del padre.
     */
    public function dashboard(Request $request)
    {
        $idPadre = $this->getIdPadre();

        $relaciones = PadreHijo::where('idPadre', $idPadre)
            ->pluck('idHijo')
            ->toArray();

        $hijos = [];
        foreach ($relaciones as $idHijo) {
            $hijo = Usuario::find($idHijo);
            if (!$hijo) continue;

            $carreraRel = CarreraEstudiantes::where('idEstudiante', $idHijo)
                ->with('carrera:idCarrera,nombreCarrera')
                ->first();

            // Promedio de notas
            $promedioNota = DB::table('evaluaciones_notas')
                ->where('idUsuario', $idHijo)
                ->avg('nota');

            // Porcentaje asistencia global
            $totalSes = DB::table('curso_asistencia as ca')
                ->join('curso_estudiantes as ce', 'ca.idCursoEstudiante', '=', 'ce.idCursoEstudiante')
                ->where('ce.idUsuario', $idHijo)
                ->count();

            $totalPres = DB::table('curso_asistencia as ca')
                ->join('curso_estudiantes as ce', 'ca.idCursoEstudiante', '=', 'ce.idCursoEstudiante')
                ->where('ce.idUsuario', $idHijo)
                ->where('ca.estado', 'presente')
                ->count();

            $porcentajeAsistencia = $totalSes > 0 ? round($totalPres / $totalSes * 100, 1) : 0;

            // Deuda pendiente
            $totalDeuda = DB::table('deudas')
                ->where('idUsuario', $idHijo)
                ->where('estado', 'pendiente')
                ->sum('monto');

            $hijos[] = [
                'idHijo'              => $idHijo,
                'nombre'              => $hijo->nombres . ' ' . $hijo->apellidoPaterno . ' ' . $hijo->apellidoMaterno,
                'foto'                => $hijo->foto,
                'carrera'             => $carreraRel?->carrera?->nombreCarrera ?? 'Sin carrera',
                'promedioNotas'       => round($promedioNota ?? 0, 2),
                'porcentajeAsistencia'=> $porcentajeAsistencia,
                'totalDeuda'          => $totalDeuda ?? 0,
            ];
        }

        return response()->json([
            'success'      => true,
            'totalHijos'   => count($hijos),
            'data'         => $hijos,
        ]);
    }

    /**
     * Verifica que el hijo pertenezca al padre.
     */
    private function esMiHijo(int $idPadre, int $idHijo): bool
    {
        return PadreHijo::where('idPadre', $idPadre)
            ->where('idHijo', $idHijo)
            ->exists();
    }
}
