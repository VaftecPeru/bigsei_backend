<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
        $per_page = $request->per_page ?? 2;

        $result = DB::table("membresia_tipo")
            ->select(
                "id_membresiatipo",
                "nombre",
                "descripcion",
                "precio",
                "precio_mes",
                "es_anual"
            )
            ->where("estado", "1")
            ->orderBy("es_anual", "desc")
            ->orderBy("precio", "desc")
            ->paginate($per_page)
            ->through(fn ($membresiaTipo) => [
                "id_membresiatipo" => $membresiaTipo->id_membresiatipo,
                "nombre" => $membresiaTipo->nombre,
                "descripcion" => $membresiaTipo->descripcion,
                "precio" => $membresiaTipo->precio,
                "precio_mes" => $membresiaTipo->precio_mes,
                "es_anual" => $membresiaTipo->es_anual,
                "tipo_beneficios" => DB::table("tipo_beneficio as a")
                    ->join("membresia_tipo_beneficio as b", "a.id_tipobeneficio", "b.id_tipobeneficio")
                    ->select("a.descripcion", "b.orden", "b.esta_habilitado")
                    ->where("b.id_membresiatipo", $membresiaTipo->id_membresiatipo)
                    ->orderBy("orden", "asc")
                    ->get()
            ]);

        return response()->json($result);
    }

    // Nuevo método para crear tipos de membresía
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'precio' => 'required|numeric|min:0',
            'precio_mes' => 'required|numeric|min:0',
            'es_anual' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => implode(", ", $validator->messages()->all())
            ], 400);
        }

        $id = DB::table('membresia_tipo')->insertGetId([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion ?? '',
            'precio' => $request->precio,
            'precio_mes' => $request->precio_mes,
            'es_anual' => $request->es_anual,
            'estado' => 1,
            'fechareg' => now()
        ]);

        $nuevoTipo = DB::table('membresia_tipo')->where('id_membresiatipo', $id)->first();

        return response()->json($nuevoTipo, 201);
    }
}