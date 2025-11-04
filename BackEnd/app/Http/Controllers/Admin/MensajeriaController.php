<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\MensajeriaGrupo;
use App\Models\MensajeriaPersona;
use App\Models\PeriodoCurso;

class MensajeriaController extends Controller
{
    public function indexGrupo(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }

        $paginate = DB::table("mensajeria_grupo as a")
            ->select(
                "a.id_mensajeriagrupo",
                "a.nombre"
            );

        if (isset($request->id_periodocurso)) {
            $paginate->where("a.id_periodocurso", $request->id_periodocurso);
        }
        if (isset($request->tipo)) {
            $paginate->where("a.tipo", $request->tipo);
        }

        $paginate->orderBy("nombre", "asc");
        $paginate->orderBy("id_mensajeriagrupo", "asc");
        $paginate = $paginate->paginate($per_page);

        return response()->json($paginate);
    }

    public function storeGrupo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_periodocurso" => "required",
            "nombre" => "required|max:80",
        ], [
            "id_periodocurso.required" => "El curso es requerido",
            "nombre.required" => "El nombre es requerido",
            "nombre.max" => "El nombre tiene un máximo 80 caracteres",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $mensajeriaGrupo = MensajeriaGrupo::where("id_periodocurso", $request->id_periodocurso)
            ->where("tipo", $request->tipo)
            ->first();
        if($mensajeriaGrupo){
            return response()->json("¡Atención! El grupo existe.", 400);
        }

        $user = $request->sessionUser;

        $mensajeriaGrupo = [];
        $mensajeriaGrupo["nombre"] = $request->nombre;
        $mensajeriaGrupo["tipo"] = $request->tipo;
        $mensajeriaGrupo["id_periodocurso"] = $request->id_periodocurso;
        $mensajeriaGrupo["estado"] = "1";
        $mensajeriaGrupo["id_usuarioreg"] = $user->id_usuario;
        $mensajeriaGrupo["fechareg"] = now();
        $mensajeriaGrupo = MensajeriaGrupo::create($mensajeriaGrupo);

        $mensajeriaGrupo = MensajeriaGrupo::find($mensajeriaGrupo->id_mensajeriagrupo);

        return response()->json($mensajeriaGrupo);
    }

    public function indexPersona(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }

        $paginate = DB::table("mensajeria_persona as a")
            ->join("persona as b", "a.id_persona", "b.id_persona")
            ->select(
                "a.id_mensajeriapersona",
                "a.id_persona",
                DB::raw("b.nombre_completo as persona_nombre")
            )
            ->where("a.estado", "1");

        if (isset($request->id_mensajeriagrupo)) {
            $paginate->where("a.id_mensajeriagrupo", $request->id_mensajeriagrupo);
        }

        $paginate->orderBy("persona_nombre", "asc");
        $paginate = $paginate->paginate($per_page);

        return response()->json($paginate);
    }

    public function storeEstudianteTodos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_mensajeriagrupo" => "required",
        ], [
            "id_mensajeriagrupo.required" => "El grupo es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $mensajeriaGrupo = MensajeriaGrupo::find($request->id_mensajeriagrupo);
        if(!$mensajeriaGrupo){
            return response()->json("¡Atención! El grupo no existe.", 400);
        }

        $user = $request->sessionUser;

        $estudiantes = DB::table("matricula as a")
            ->join("matricula_curso as b", "a.id_matricula", "b.id_matricula")
            ->leftJoin("mensajeria_persona as c", "a.id_estudiante", DB::raw("c.id_persona and c.id_mensajeriagrupo = ".$mensajeriaGrupo->id_mensajeriagrupo))
            ->select(
                "a.id_estudiante"
            )
            ->where("id_periodocurso", $mensajeriaGrupo->id_periodocurso)
            ->whereNull("c.id_persona")
            ->get();

        DB::beginTransaction();

        foreach($estudiantes as $value) {
            $mensajeriaPersona = [];
            $mensajeriaPersona["id_mensajeriagrupo"] = $mensajeriaGrupo->id_mensajeriagrupo;
            $mensajeriaPersona["id_persona"] = $value->id_estudiante;
            $mensajeriaPersona["estado"] = "1";
            $mensajeriaPersona["id_usuarioreg"] = $user->id_usuario;
            $mensajeriaPersona["fechareg"] = now();
            $mensajeriaPersona = MensajeriaPersona::create($mensajeriaPersona);
        }

        DB::commit();

        return response()->json([]);
    }

    public function storeDocente(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_mensajeriagrupo" => "required",
        ], [
            "id_mensajeriagrupo.required" => "El grupo es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $mensajeriaGrupo = MensajeriaGrupo::find($request->id_mensajeriagrupo);
        if(!$mensajeriaGrupo){
            return response()->json("¡Atención! El grupo no existe.", 400);
        }

        $periodoCurso = PeriodoCurso::find($mensajeriaGrupo->id_periodocurso);
        if(!$periodoCurso){
            return response()->json("¡Atención! El curso no existe.", 400);
        }
        if(!$periodoCurso->id_docente){
            return response()->json("¡Atención! Falta asignar docente.", 400);
        }

        $mensajeriaPersona = MensajeriaPersona::where("id_persona", $periodoCurso->id_docente)
            ->where("id_mensajeriagrupo", $request->id_mensajeriagrupo)
            ->first();
        if($mensajeriaPersona){
            return response()->json("¡Atención! El docente esta registrado.", 400);
        }

        $user = $request->sessionUser;

        $mensajeriaPersona = [];
        $mensajeriaPersona["id_mensajeriagrupo"] = $mensajeriaGrupo->id_mensajeriagrupo;
        $mensajeriaPersona["id_persona"] = $periodoCurso->id_docente;
        $mensajeriaPersona["estado"] = "1";
        $mensajeriaPersona["id_usuarioreg"] = $user->id_usuario;
        $mensajeriaPersona["fechareg"] = now();
        $mensajeriaPersona = MensajeriaPersona::create($mensajeriaPersona);

        return response()->json([]);
    }
}