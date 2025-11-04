<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function notas(Request $request)
    {
        $user = $request->sessionUser;
        $id_matricula = $request->id_matricula;
        $id_estudiante = $user->id_usuario;

        $periodoCiclos = DB::table("matricula as a")
            ->join("matricula_curso as b", "a.id_matricula", "b.id_matricula")
            ->join("periodo_curso as c", "b.id_periodocurso", "c.id_periodocurso")
            ->join("periodo_ciclo as d", "c.id_periodociclo", "d.id_periodociclo")
            ->join("ciclo as e", "d.id_ciclo", "e.id_ciclo")
            ->join("carrera as f", "d.id_carrera", "f.id_carrera")
            ->select(
                "c.id_periodociclo",
                "e.nombre",
                DB::raw("f.nombre as carrera_nombre")
            )
            ->where("a.id_estudiante", $id_estudiante)
            ->where("a.id_empresa", $user->id_empresa)
            ->where("a.id_matricula", $id_matricula)
            ->groupBy("c.id_periodociclo", "e.nombre", "f.nombre")
            ->get()
            ->map(function($periodoCiclo) use($id_matricula, $id_estudiante) {
                $cursos = DB::table("matricula_curso as a")
                    ->join("curso as b", "a.id_curso", "b.id_curso")
                    ->join("periodo_curso as c", "a.id_periodocurso", "c.id_periodocurso")
                    ->select(
                        "a.id_periodocurso",
                        DB::raw("b.nombre")
                    )
                    ->where("c.id_periodociclo", $periodoCiclo->id_periodociclo)
                    ->where("a.id_matricula", $id_matricula)
                    ->get()
                    ->map(function($curso) use($id_estudiante) {
                        $evaluaciones = DB::table("evaluacion_criterio as a")
                            ->leftJoin("evaluacion_nota as b", "a.id_periodocurso",
                                DB::raw("b.id_periodocurso and $id_estudiante = b.id_estudiante and a.id_evaluacioncriterio = b.id_evaluacioncriterio"))
                            ->select(
                                "a.titulo",
                                "b.nota"
                            )
                            ->where("a.id_periodocurso", $curso->id_periodocurso)
                            ->get();

                        if(count($evaluaciones) == 0) {
                            $evaluaciones = [
                                [
                                    "titulo" => "No definido.",
                                    "nota" => "-",
                                ],
                            ];
                            $formula = "No definido.";
                        } else {
                            $formula = "No definido.";
                        }

                        return [
                            "curso_nombre" => $curso->nombre,
                            "evaluaciones" => $evaluaciones,
                            "formula" => $formula,
                        ];
                    });

                return [
                    "ciclo_nombre" => $periodoCiclo->nombre,
                    "carrera_nombre" => $periodoCiclo->carrera_nombre,
                    "cursos" => $cursos,
                ];
            });

        return response()->json($periodoCiclos);
    }

    public function matriculas(Request $request)
    {
        $user = $request->sessionUser;
        $id_matricula = $request->id_matricula;
        $id_estudiante = $user->id_usuario;

        $periodoCiclos = DB::table("matricula as a")
            ->join("matricula_curso as b", "a.id_matricula", "b.id_matricula")
            ->join("periodo_curso as c", "b.id_periodocurso", "c.id_periodocurso")
            ->join("periodo_ciclo as d", "c.id_periodociclo", "d.id_periodociclo")
            ->join("ciclo as e", "d.id_ciclo", "e.id_ciclo")
            ->join("carrera as f", "d.id_carrera", "f.id_carrera")
            ->select(
                "c.id_periodociclo",
                "e.nombre",
                DB::raw("f.nombre as carrera_nombre")
            )
            ->where("a.id_estudiante", $id_estudiante)
            ->where("a.id_empresa", $user->id_empresa)
            ->where("a.id_matricula", $id_matricula)
            ->groupBy("c.id_periodociclo", "e.nombre", "f.nombre")
            ->get()
            ->map(function($periodoCiclo) use($id_matricula) {
                $cursos = DB::table("matricula_curso as a")
                    ->join("curso as b", "a.id_curso", "b.id_curso")
                    ->join("periodo_curso as c", "a.id_periodocurso", "c.id_periodocurso")
                    ->join("plan_estudio_curso as d", "c.id_planestudiocurso", "d.id_planestudiocurso")
                    ->join("persona as e", "c.id_docente", "e.id_persona")
                    ->select(
                        DB::raw("b.nombre as curso_nombre"),
                        DB::raw("e.nombre_completo as docente_nombre"),
                        "d.creditos"
                    )
                    ->where("c.id_periodociclo", $periodoCiclo->id_periodociclo)
                    ->where("a.id_matricula", $id_matricula)
                    ->get();

                return [
                    "ciclo_nombre" => $periodoCiclo->nombre,
                    "carrera_nombre" => $periodoCiclo->carrera_nombre,
                    "cursos" => $cursos,
                ];
            });

        return response()->json($periodoCiclos);
    }
}