<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Asistencia;

class MiAsistenciaController extends Controller
{
    public function index(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }

        $user = $request->sessionUser;

        $paginate = DB::table("asistencia as a")
            ->join("persona as b", "a.id_persona", "b.id_persona")
            ->join("docente as c", "b.id_persona", "c.id_docente")
            ->select(
                "a.id_asistencia",
                "a.estado",
                "b.id_persona",
                DB::raw("date_format(a.fecha, '%d-%m-%Y') as fecha_ff"),
                DB::raw("date_format(a.fecha, '%h:%i %p') as hora_ff"),
                DB::raw("case when a.estado = 'A' then 'Asistió' when a.estado = 'T' then 'Tarde' when a.estado = 'F' then 'Falta' else 'Falta' end as estado_descripcion"),
                DB::raw("b.nombre_completo as docente_nombre"),
                DB::raw("b.foto as docente_foto"),
                DB::raw("c.codigo as docente_codigo")
            )
            ->where("a.tipo", "D")
            ->where("a.id_persona", $user->id_usuario)
            ->where("a.id_empresa", $user->id_empresa)
            ->orderBy("id_asistencia", "desc")
            ->paginate($per_page);

        return response()->json($paginate);
    }

    public function store(Request $request)
    {
        $user = $request->sessionUser;
        $fecha = now()->format('Y-m-d');

        $asistencia = DB::table("asistencia as a")
            ->join("persona as b", "a.id_persona", "b.id_persona")
            ->join("docente as c", "b.id_persona", "c.id_docente")
            ->select(
                "a.id_asistencia",
                "a.fecha"
            )
            ->where("a.tipo", "D")
            ->where("a.id_persona", $user->id_usuario)
            ->where("a.id_empresa", $user->id_empresa)
            ->whereRaw("cast(a.fecha as date) = '$fecha'")
            ->first();

        if($asistencia) {
            return response()->json(
                "¡Atención! Ya marco asistencia.", 400);
        }

        $asistencia = [];
        $asistencia["id_empresa"] = $user->id_empresa;
        $asistencia["id_persona"] = $user->id_usuario;
        $asistencia["fecha"] = now();
        $asistencia["estado"] = "A";
        $asistencia["tipo"] = "D";
        $asistencia["id_usuarioreg"] = $user->id_usuario;
        $asistencia["fechareg"] = now();
        $asistencia = Asistencia::create($asistencia);

        return response()->json($asistencia);
    }

    public function porcentajes(Request $request)
    {
        $user = $request->sessionUser;

        $asistencias = DB::table("asistencia as a")
            ->join("persona as b", "a.id_persona", "b.id_persona")
            ->join("docente as c", "b.id_persona", "c.id_docente")
            ->select(
                DB::raw("count(1) as total"),
                DB::raw("sum(case when a.estado = 'A' then 1 else 0 end) as asistencia_total"),
                DB::raw("sum(case when a.estado = 'T' then 1 else 0 end) as tarde_total"),
                DB::raw("sum(case when a.estado = 'F' then 1 else 0 end) as falta_total")
            )
            ->where("a.tipo", "D")
            ->where("a.id_persona", $user->id_usuario)
            ->where("a.id_empresa", $user->id_empresa)
            ->get();

        if(count($asistencias) > 0){
            $result = [
                "total" => $asistencias[0]->total,
                "asistencia_total" => $asistencias[0]->asistencia_total,
                "tarde_total" => $asistencias[0]->tarde_total,
                "falta_total" => $asistencias[0]->falta_total,
            ];
        } else {
            $result = [
                "total" => 0,
                "asistencia_total" => 0,
                "tarde_total" => 0,
                "falta_total" => 0,
            ];
        }

        return response()->json($result);
    }
}