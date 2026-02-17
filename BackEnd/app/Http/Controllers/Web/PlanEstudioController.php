<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanEstudioController extends Controller
{
    /**
     * Lista los planes de estudio publicados de empresas con licencia activa.
     * Se usa en la portada pública para mostrar planes académicos disponibles.
     */
    public function publicados(Request $request)
    {
        if (isset($request->per_page)) {
            $per_page = $request->per_page;
        } else {
            $per_page = 6;
        }

        $result = DB::table("plan_estudio as a")
            ->join("empresa as b", "a.id_empresa", "b.id_empresa")
            ->join("carrera as c", "a.id_carrera", "c.id_carrera")
            ->join("licencia as d", "a.id_empresa", "d.id_empresa")
            ->leftJoin("licencia_tipo as e", "d.id_licenciatipo", "e.id_licenciatipo")
            ->leftJoin("archivo as f", "b.id_archivo", "f.id_archivo")
            ->select(
                "a.id_planestudio",
                "a.nombre",
                "a.fecha_inicio",
                DB::raw("c.nombre as carrera_nombre"),
                DB::raw("b.razon_social as empresa_nombre"),
                DB::raw("b.id_archivo as empresa_id_archivo"),
                DB::raw("e.nombre as licencia_tipo_nombre"),
                DB::raw("(SELECT count(1) FROM plan_estudio_ciclo WHERE id_planestudio = a.id_planestudio) as total_ciclos"),
                DB::raw("(SELECT count(1) FROM plan_estudio_curso WHERE id_planestudio = a.id_planestudio) as total_cursos"),
                DB::raw("(SELECT sum(creditos) FROM plan_estudio_curso WHERE id_planestudio = a.id_planestudio) as total_creditos")
            )
            ->where("a.esta_publicado", "1")
            ->where("a.estado", "1")
            ->where("d.estado", "1")
            ->whereRaw("d.fecha_fin >= NOW()");

        if (isset($request->id_carrera)) {
            $result->where("a.id_carrera", $request->id_carrera);
        }

        if (isset($request->text_search)) {
            $texto = str_replace(' ', '%', $request->text_search);
            $result->whereRaw("upper(concat(a.nombre, c.nombre, b.razon_social)) LIKE upper( ? )", ['%' . $texto . '%']);
        }

        $result->groupBy(
            "a.id_planestudio",
            "a.nombre",
            "a.fecha_inicio",
            "c.nombre",
            "b.razon_social",
            "b.id_archivo",
            "e.nombre"
        );
        $result->orderBy("a.nombre", "asc");
        $result = $result->paginate($per_page);

        return response()->json($result);
    }

    /**
     * Detalle de un plan de estudio publicado con sus ciclos y cursos.
     */
    public function showPublicado($id_planestudio)
    {
        $planEstudio = DB::table("plan_estudio as a")
            ->join("empresa as b", "a.id_empresa", "b.id_empresa")
            ->join("carrera as c", "a.id_carrera", "c.id_carrera")
            ->join("licencia as d", "a.id_empresa", "d.id_empresa")
            ->select(
                "a.id_planestudio",
                "a.nombre",
                "a.fecha_inicio",
                DB::raw("c.nombre as carrera_nombre"),
                DB::raw("b.razon_social as empresa_nombre"),
                DB::raw("b.id_archivo as empresa_id_archivo")
            )
            ->where("a.id_planestudio", $id_planestudio)
            ->where("a.esta_publicado", "1")
            ->where("a.estado", "1")
            ->where("d.estado", "1")
            ->whereRaw("d.fecha_fin >= NOW()")
            ->first();

        if (!$planEstudio) {
            return response()->json("Plan de estudio no encontrado o no disponible.", 404);
        }

        // Obtener ciclos con sus cursos
        $ciclos = DB::table("plan_estudio_ciclo as a")
            ->join("ciclo as b", "a.id_ciclo", "b.id_ciclo")
            ->select(
                "a.id_planestudiociclo",
                "b.nombre",
                "b.orden"
            )
            ->where("a.id_planestudio", $id_planestudio)
            ->orderBy("b.orden", "asc")
            ->get()
            ->map(function ($ciclo) {
                $ciclo->cursos = DB::table("plan_estudio_curso as a")
                    ->join("curso as b", "a.id_curso", "b.id_curso")
                    ->leftJoin("archivo as c", "b.id_archivo", "c.id_archivo")
                    ->select(
                        "a.id_planestudiocurso",
                        "b.nombre",
                        "b.codigo",
                        "a.creditos",
                        "a.horas_semanal",
                        DB::raw("case when a.tipo = 'O' then 'Obligatorio' when a.tipo = 'E' then 'Electivo' else '' end as tipo_descripcion"),
                        DB::raw("c.url as archivo_url")
                    )
                    ->where("a.id_planestudiociclo", $ciclo->id_planestudiociclo)
                    ->orderBy("b.nombre", "asc")
                    ->get();
                return $ciclo;
            });

        $planEstudio->ciclos = $ciclos;

        return response()->json($planEstudio);
    }
}
