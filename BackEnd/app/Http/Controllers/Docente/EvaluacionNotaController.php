<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\EvaluacionNota;

class EvaluacionNotaController extends Controller
{
    public function index(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $paginate = DB::table("matricula as a")
            ->join("persona as b", "a.id_estudiante", "b.id_persona")
            ->join("estudiante as c", "b.id_persona", "c.id_estudiante")
            ->join("matricula_curso as d", "a.id_matricula", "d.id_matricula")
            ->join("periodo_curso as z", "d.id_periodocurso", "z.id_periodocurso")
            ->join("evaluacion_criterio as f", "d.id_periodocurso", "f.id_periodocurso")
            ->leftJoin("evaluacion_nota as e", "d.id_periodocurso",
                DB::raw("e.id_periodocurso and c.id_estudiante = e.id_estudiante and f.id_evaluacioncriterio = e.id_evaluacioncriterio"))
            ->select(
                "e.id_evaluacionnota",
                "e.nota",
                "f.id_evaluacioncriterio",
                "c.id_estudiante",
                "d.id_periodocurso",
                DB::raw("f.titulo as criterio_titulo"),
                DB::raw("b.nombre_completo as estudiante_nombre"),
                DB::raw("b.foto as estudiante_foto"),
                DB::raw("c.codigo as estudiante_codigo")
            )
            ->where("z.id_docente", $user->id_usuario)
            ->where("a.id_empresa", $user->id_empresa)
            ->where("d.id_periodocurso", $request->id_periodocurso)
            ->orderBy("id_evaluacioncriterio", "asc")
            ->orderBy("estudiante_nombre", "asc")
            ->paginate($per_page);

        return response()->json($paginate);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_evaluacioncriterio" => "required",
            "id_estudiante" => "required",
            "id_periodocurso" => "required",
            "nota" => "required|max:20",
        ], [
            "id_evaluacioncriterio.required" => "El criterio es requerido",
            "id_estudiante.required" => "El estudiante es requerido",
            "id_periodocurso.required" => "El curso es requerido",
            "nota.required" => "La nota es requerida",
            'nota.max' => 'La nota tiene un máximo 20 caracteres',
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        $matricula = DB::table("periodo_curso as a")
            ->join("matricula_curso as b", "a.id_periodocurso", "b.id_periodocurso")
            ->join("matricula as c", "b.id_matricula", "c.id_matricula")
            ->leftJoin("evaluacion_nota as d", "b.id_periodocurso",
                DB::raw("d.id_periodocurso and c.id_estudiante = d.id_estudiante and d.id_evaluacioncriterio = ".$request->id_evaluacioncriterio))
            ->select(
                "c.id_empresa",
                "d.id_evaluacionnota",
                "a.id_tiponota"
            )
            ->where("a.id_periodocurso", $request->id_periodocurso)
            ->where("c.estado", "1")
            ->where("c.id_estudiante", $request->id_estudiante)
            ->first();

        if (!$matricula) {
            return response()->json(
                "¡Atención! La matrícula no existe.", 400);
        }

        if ($matricula->id_evaluacionnota) {
            $evaluacionNota = EvaluacionNota::find($matricula->id_evaluacionnota);
            $evaluacionNotaEdit = [];
            $evaluacionNotaEdit["id_evaluacioncriterio"] = $request->id_evaluacioncriterio;
            $evaluacionNotaEdit["id_periodocurso"] = $request->id_periodocurso;
            $evaluacionNotaEdit["id_estudiante"] = $request->id_estudiante;
            $evaluacionNotaEdit["nota"] = $request->nota;
            $evaluacionNotaEdit["id_tiponota"] = $matricula->id_tiponota;
            $evaluacionNota->update($evaluacionNotaEdit);
        } else {
            $evaluacionNota = [];
            $evaluacionNota["id_evaluacioncriterio"] = $request->id_evaluacioncriterio;
            $evaluacionNota["id_periodocurso"] = $request->id_periodocurso;
            $evaluacionNota["id_estudiante"] = $request->id_estudiante;
            $evaluacionNota["id_docente"] = $user->id_usuario;
            $evaluacionNota["nota"] = $request->nota;
            $evaluacionNota["id_tiponota"] = $matricula->id_tiponota;
            $evaluacionNota["id_usuarioreg"] = $user->id_usuario;
            $evaluacionNota["fechareg"] = now();
            $evaluacionNota = EvaluacionNota::create($evaluacionNota);
        }

        return response()->json([]);
    }
}