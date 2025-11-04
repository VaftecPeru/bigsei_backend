<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\EvaluacionCriterio;

class EvaluacionCriterioController extends Controller
{
    public function index(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $paginate = DB::table("evaluacion_criterio as a")
            ->select(
                "a.id_evaluacioncriterio",
                "a.titulo",
                "a.descripcion",
                "a.estado"
            )
            ->where("a.id_empresa", $user->id_empresa);

        if(isset($request->id_periodocurso)) {
            $paginate->where("a.id_periodocurso", $request->id_periodocurso);
        }

        $paginate = $paginate->paginate($per_page);

        return response()->json($paginate);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_periodocurso" => "required",
            "titulo" => "required|max:100",
            "descripcion" => "required|max:255",
            "estado" => "required|max:1",
        ], [
            "id_periodocurso.required" => "El curso es requerido",
            "titulo.required" => "El título es requerido",
            "titulo.max" => "El título tiene un máximo 20 caracteres",
            "descripcion.required" => "La descripción es requerida",
            "descripcion.max" => "La descripción tiene un máximo 20 caracteres",
            "estado.required" => "El estado es requerido",
            "estado.max" => "El estado tiene un máximo 1 caracteres",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        $evaluacionCriterio = [];
        $evaluacionCriterio["id_empresa"] = $user->id_empresa;
        $evaluacionCriterio["id_periodocurso"] = $request->id_periodocurso;
        $evaluacionCriterio["titulo"] = $request->titulo;
        $evaluacionCriterio["descripcion"] = $request->descripcion;
        $evaluacionCriterio["estado"] = $request->estado;
        $evaluacionCriterio["id_usuarioreg"] = $user->id_usuario;
        $evaluacionCriterio["fechareg"] = now();
        $evaluacionCriterio = EvaluacionCriterio::create($evaluacionCriterio);

        $evaluacionCriterio = EvaluacionCriterio::find($evaluacionCriterio->id_evaluacioncriterio);

        return response()->json($evaluacionCriterio);
    }

    public function update(Request $request, $id_evaluacioncriterio)
    {
        $validator = Validator::make($request->all(), [
            "id_periodocurso" => "required",
            "titulo" => "required|max:100",
            "descripcion" => "required|max:255",
            "estado" => "required|max:1",
        ], [
            "id_periodocurso.required" => "El curso es requerido",
            "titulo.required" => "El título es requerido",
            "titulo.max" => "El título tiene un máximo 20 caracteres",
            "descripcion.required" => "La descripción es requerida",
            "descripcion.max" => "La descripción tiene un máximo 20 caracteres",
            "estado.required" => "El estado es requerido",
            "estado.max" => "El estado tiene un máximo 1 caracteres",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $evaluacionCriterio = EvaluacionCriterio::find($id_evaluacioncriterio);
        if(!$evaluacionCriterio){
            return response()->json("¡Atención! El criterio no existe.", 400);
        }

        $evaluacionCriterioEdit = [];
        $evaluacionCriterioEdit["id_periodocurso"] = $request->id_periodocurso;
        $evaluacionCriterioEdit["titulo"] = $request->titulo;
        $evaluacionCriterioEdit["descripcion"] = $request->descripcion;
        $evaluacionCriterioEdit["estado"] = $request->estado;
        $evaluacionCriterio->update($evaluacionCriterioEdit);

        $evaluacionCriterio = EvaluacionCriterio::find($id_evaluacioncriterio);

        return response()->json($evaluacionCriterio);
    }
}