<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Membresia;
use App\Models\MembresiaTipo;

class MiMembresiaController extends Controller
{
    public function activas(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 2;
        }
        $user = $request->sessionUser;

        $paginate = DB::table("membresia as a")
            ->join("membresia_tipo as b", "a.id_membresiatipo", "b.id_membresiatipo")
            ->select(
                "a.id_membresia",
                "b.id_membresiatipo",
                "b.nombre",
                "b.descripcion",
                "b.es_anual"
            )
            ->where("a.estado", "1")
            ->where("a.id_persona", $user->id_usuario);

        $paginate->orderBy("id_membresia", "asc");
        $paginate = $paginate->paginate($per_page);

        return response()->json($paginate);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_membresiatipo" => "required",
            "precio" => "required|numeric|min:0|not_in:0",
            "numero_operacion" => "required",
            "importe_operacion" => "required|numeric|min:0|not_in:0",
        ], [
            "id_membresiatipo" => "La membresia es requerida",
            "precio" => "El precio es requerido",
            "numero_operacion" => "El número operación es requerido",
            "importe_operacion" => "El importe operación es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $membresiaTipo = MembresiaTipo::find($request->id_membresiatipo);
        if (!$membresiaTipo) {
            return response()->json('¡Atención! El tipo de membresía no existe.', 400);
        }

        $user = $request->sessionUser;

        $membresia = [];
        $membresia["id_persona"] = $user->id_usuario;
        $membresia["id_membresiatipo"] = $request->id_membresiatipo;
        $membresia["precio"] = $request->precio;
        $membresia["fecha_inicio"] = now();
        $membresia["fecha_fin"] = now();
        $membresia["estado"] = "1";
        $membresia["id_usuarioreg"] = $user->id_usuario;
        $membresia["fechareg"] = now();
        $membresia = Membresia::create($membresia);

        return response()->json($membresia);
    }
}