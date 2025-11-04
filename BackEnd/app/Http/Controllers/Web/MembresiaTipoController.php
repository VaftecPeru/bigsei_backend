<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MembresiaTipoController extends Controller
{
    public function show($id_membresiatipo)
    {
        $result = DB::table("membresia_tipo")
            ->select(
                "id_membresiatipo",
                "nombre",
                "descripcion",
                "precio",
                "precio_mes",
                "estado",
                "es_anual"
            )
            ->where("id_membresiatipo", $id_membresiatipo)
            ->first();

        return response()->json($result);
    }

    public function activos(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 2;
        }
        $result = DB::table("membresia_tipo")
            ->select(
                "id_membresiatipo",
                "nombre",
                "descripcion",
                "precio",
                "precio_mes",
                "es_anual"
            )
            ->where("estado", "1");

        $result->orderBy("es_anual", "desc");
        $result->orderBy("precio", "desc");
        $result = $result->paginate($per_page)
            ->through(fn ($membresiaTipo) => [
                "id_membresiatipo" => $membresiaTipo->id_membresiatipo,
                "nombre" => $membresiaTipo->nombre,
                "descripcion" => $membresiaTipo->descripcion,
                "precio" => $membresiaTipo->precio,
                "precio_mes" => $membresiaTipo->precio_mes,
                "es_anual" => $membresiaTipo->es_anual,
                "tipo_beneficios" => DB::table("tipo_beneficio as a")
                    ->join("membresia_tipo_beneficio as b", "a.id_tipobeneficio", "b.id_tipobeneficio")
                    ->select("a.descripcion", "b.orden", ".esta_habilitado")
                    ->where("b.id_membresiatipo", $membresiaTipo->id_membresiatipo)
                    ->orderBy("orden", "asc")
                    ->get()
            ]);

        return response()->json($result);
    }
}