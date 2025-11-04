<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\MensajeriaMensaje;

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
            )
            ->where("a.estado", "1");

        if (isset($request->id_periodocurso)) {
            $paginate->where("a.id_periodocurso", $request->id_periodocurso);
        }
        if (isset($request->tipo)) {
            $paginate->where("a.tipo", $request->tipo);
        }

        $paginate->orderBy("nombre", "asc");
        $paginate = $paginate->paginate($per_page);

        return response()->json($paginate);
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
            ->leftJoin("mensajeria_mensaje as c", "a.id_mensajeriagrupo", DB::raw("c.id_mensajeriagrupo and a.id_persona = c.id_persona"))
            ->select(
                "a.id_mensajeriapersona",
                "a.id_persona",
                DB::raw("b.nombre_completo as persona_nombre"),
                DB::raw("max(date_format(c.fecha, '%h:%i %p')) as mensaje_hora_ff")
            )
            ->where("a.estado", "1");

        if (isset($request->id_mensajeriagrupo)) {
            $paginate->where("a.id_mensajeriagrupo", $request->id_mensajeriagrupo);
        }

        $paginate->groupBy("a.id_mensajeriapersona", "a.id_persona", "b.nombre_completo");
        $paginate->orderBy("persona_nombre", "asc");
        $paginate = $paginate->paginate($per_page);

        return response()->json($paginate);
    }

    public function indexMensaje(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }

        DB::statement("SET lc_time_names = 'es_ES'");
        $paginate = DB::table("mensajeria_mensaje as a")
            ->join("persona as b", "a.id_persona", "b.id_persona")
            ->select(
                "a.id_mensajeriamensaje",
                "a.id_mensajeriagrupo",
                "a.id_persona",
                "a.texto",
                "a.fecha",
                DB::raw("b.nombre_completo as persona_nombre"),
                DB::raw("date_format(a.fecha, '%h:%i %p') as mensaje_hora_ff"),
                DB::raw("dayname(a.fecha) as mensaje_dia_ff")
            )
            ->where("a.id_mensajeriagrupo", $request->id_mensajeriagrupo);

        if (isset($request->mayor_a_id_mensajeriamensaje)) {
            $paginate->where("a.id_mensajeriamensaje", ">", $request->mayor_a_id_mensajeriamensaje);
        }

        $paginate->orderBy("id_mensajeriamensaje", "asc");
        $paginate = $paginate->paginate($per_page);

        return response()->json($paginate);
    }

    public function storeMensaje(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_mensajeriagrupo" => "required",
            "texto" => "required|max:255",
        ], [
            "id_mensajeriagrupo.required" => "El grupo es requerido",
            "texto.required" => "El texto es requerido",
            "texto.max" => "El texto tiene un máximo 255 caracteres",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        $mensajeriaMensaje = [];
        $mensajeriaMensaje["id_mensajeriagrupo"] = $request->id_mensajeriagrupo;
        $mensajeriaMensaje["id_persona"] = $user->id_usuario;
        $mensajeriaMensaje["texto"] = $request->texto;
        $mensajeriaMensaje["fecha"] = now();
        $mensajeriaMensaje = MensajeriaMensaje::create($mensajeriaMensaje);

        $mensajeriaMensaje = MensajeriaMensaje::find($mensajeriaMensaje->id_mensajeriamensaje);

        return response()->json($mensajeriaMensaje);
    }
}