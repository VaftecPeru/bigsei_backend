<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MiNotaController extends Controller
{
    public function matriculas(Request $request)
    {
        $user = $request->sessionUser;

        $rows = DB::table("matricula as a")
            ->join("periodo as b", "a.id_periodo", "b.id_periodo")
            ->select(
                "a.id_matricula",
                "a.id_estudiante",
                DB::raw("b.nombre as periodo_nombre")
            )
            ->where("a.id_estudiante", $user->id_usuario)
            ->where("a.id_empresa", $user->id_empresa)
            ->orderBy("nombre", "asc")
            ->get()
            ->each(function($matricula) {
                $cursos = DB::table("matricula_curso as a")
                    ->join("curso as b", "a.id_curso", "b.id_curso")
                    ->join("periodo_curso as c", "a.id_periodocurso", "c.id_periodocurso")
                    ->join("persona as d", "c.id_docente", "d.id_persona")
                    ->join("periodo_ciclo as e", "c.id_periodociclo", "e.id_periodociclo")
                    ->join("ciclo as f", "e.id_ciclo", "f.id_ciclo")
                    ->join("plan_estudio_ciclo as z", "e.id_planestudiociclo", "z.id_planestudiociclo")
                    ->join("plan_estudio as x", "z.id_planestudio", "x.id_planestudio")
                    ->join("evaluacion_criterio as g", "c.id_periodocurso", "g.id_periodocurso")
                    ->leftJoin("plan_estudio_curso as y", "c.id_planestudiocurso", "y.id_planestudiocurso")
                    ->leftJoin("evaluacion_nota as h", "g.id_evaluacioncriterio", DB::raw("h.id_evaluacioncriterio and a.id_periodocurso = h.id_periodocurso and h.id_estudiante = ".$matricula->id_estudiante))
                    ->select(
                        DB::raw("b.nombre as curso_nombre"),
                        DB::raw("d.nombre_completo as docente_nombre"),
                        DB::raw("f.nombre as ciclo_nombre"),
                        DB::raw("h.nota as nota"),
                        DB::raw("year(x.fecha_inicio) as anho_ff"),
                        DB::raw("y.creditos")
                    )
                    ->where("a.id_matricula", $matricula->id_matricula)
                    ->get();

                $matricula->cursos = $cursos;
            });

        return response()->json($rows);
    }

    public function historialAcademico(Request $request)
    {
        $user = $request->sessionUser;

        $notas = DB::table("mes as a")
            ->leftJoin("evaluacion_nota as b", "a.id_mes", DB::raw("month(b.fechareg) and b.id_estudiante = ".$user->id_usuario))
            ->select(
                "a.id_mes",
                DB::raw("a.nombre as mes_nombre"),
                DB::raw("
                    case count(case when b.nota is not null then 1 else 0 end)
                        when 0 then 0
                        else (
                            sum(case when b.nota is not null then b.nota else 0 end)
                            /
                            count(case when b.nota is not null then 1 else 0 end)
                        )
                    end as nota
                ")
            )
            ->groupBy("a.id_mes", "a.nombre")
            ->orderBy("id_mes", "asc")
            ->get();

        return response()->json($notas);
    }

    public function promedio(Request $request)
    {
        $user = $request->sessionUser;

        $notas = DB::table("mes as a")
            ->join("evaluacion_nota as b", "a.id_mes", DB::raw("month(b.fechareg) and b.id_estudiante = ".$user->id_usuario))
            ->select(
                DB::raw("
                    case count(case when b.nota is not null then 1 else 0 end)
                        when 0 then 0
                        else (
                            sum(case when b.nota is not null then b.nota else 0 end)
                            /
                            count(case when b.nota is not null then 1 else 0 end)
                        )
                    end as nota
                ")
            )
            ->get();

        return response()->json($notas);
    }
}