<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Asistencia;

class AsistenciaController extends Controller
{
    public function indexEstudiante(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $paginate = DB::table("periodo_curso as b")
            ->join("matricula_curso as c", "b.id_periodocurso", "c.id_periodocurso")
            ->join("matricula as d", "c.id_matricula", "d.id_matricula")
            ->join("estudiante as e", "d.id_estudiante", "e.id_estudiante")
            ->join("persona as f", "e.id_estudiante", "f.id_persona");

        if(isset($request->fecha)) {
            $paginate->leftJoin("asistencia as g", "b.id_periodocurso", DB::raw("g.id_periodocurso and f.id_persona = g.id_persona and g.tipo = 'E' and g.fecha = '".$request->fecha."'"));
        } else {
            $paginate->leftJoin("asistencia as g", "b.id_periodocurso", DB::raw("g.id_periodocurso and f.id_persona = g.id_persona and g.tipo = 'E'"));
        }

        $paginate->select(
                "g.id_asistencia",
                "g.estado",
                "f.id_persona",
                DB::raw("date_format(g.fecha, '%d-%m-%Y') as asistencia_fecha_ff"),
                DB::raw("date_format(g.fecha, '%h:%i %p') as asistencia_hora_ff"),
                DB::raw("case when g.estado = 'A' then 'Asistió' when g.estado = 'T' then 'Tarde' when g.estado = 'F' then 'Falta' else 'Falta' end as estado_descripcion"),
                DB::raw("f.nombre_completo as estudiante_nombre"),
                DB::raw("f.foto as estudiante_foto"),
                DB::raw("e.codigo as estudiante_codigo")

            )
            // ->where("b.tipo", "E")
            ->where("b.id_periodocurso", $request->id_periodocurso);

        if(isset($request->id_periodociclo)) {
            $paginate->where("b.id_periodociclo", $request->id_periodociclo);
        }

        $paginate->orderBy("nombre", "asc");
        $paginate->orderBy("id_asistencia", "asc");
        $paginate = $paginate->paginate($per_page);

        return response()->json($paginate);
    }

    public function storeEstudiante(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_asistencia" => "nullable",
            "id_periodocurso" => "required",
            "id_persona" => "required",
            "fecha" => "required|date",
            "tipo" => "required|max:1",
            "estado" => "required|max:1",
        ], [
            "id_periodocurso.required" => "El curso es requerido",
            "id_persona.required" => "La persona es requerida",
            "fecha.required" => "La fecha es requerida",
            "tipo.required" => "El tipo es requerido",
            "estado.required" => "El estado es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        $matricula = DB::table("periodo_curso as a")
            ->join("matricula_curso as b", "a.id_periodocurso", "b.id_periodocurso")
            ->join("matricula as c", "b.id_matricula", "c.id_matricula")
            ->leftJoin("asistencia as d", "a.id_periodocurso", DB::raw("d.id_periodocurso and c.id_estudiante = d.id_persona and d.fecha = '".$request->fecha."'"))
            ->select(
                "c.id_empresa",
                "d.id_asistencia"

            )
            ->where("a.id_periodocurso", $request->id_periodocurso)
            ->where("c.estado", "1")
            ->where("c.id_estudiante", $request->id_persona)
            ->first();

        if (!$matricula) {
            return response()->json(
                "¡Atención! La matrícula no existe.", 400);
        }

        if ($matricula->id_asistencia) {
            $asistencia = Asistencia::find($matricula->id_asistencia);
            $asistenciaEdit = [];
            $asistenciaEdit["id_periodocurso"] = $request->id_periodocurso;
            $asistenciaEdit["id_persona"] = $request->id_persona;
            $asistenciaEdit["fecha"] = $request->fecha;
            $asistenciaEdit["estado"] = $request->estado;
            $asistenciaEdit["tipo"] = $request->tipo;
            $asistencia->update($asistenciaEdit);
        } else {
            $asistencia = [];
            $asistencia["id_empresa"] = $matricula->id_empresa;
            $asistencia["id_periodocurso"] = $request->id_periodocurso;
            $asistencia["id_persona"] = $request->id_persona;
            $asistencia["fecha"] = $request->fecha;
            $asistencia["estado"] = $request->estado;
            $asistencia["tipo"] = $request->tipo;
            $asistencia["id_usuarioreg"] = $user->id_usuario;
            $asistencia["fechareg"] = now();
            $asistencia = Asistencia::create($asistencia);
        }

        return response()->json([]);
    }

    public function storeEstudianteTodos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_periodocurso" => "required",
            "fecha" => "required|date",
            "tipo" => "required|max:1",
            "estado" => "required|max:1",
        ], [
            "id_periodocurso.required" => "El curso es requerido",
            "fecha.required" => "La fecha es requerida",
            "tipo.required" => "El tipo es requerido",
            "estado.required" => "El estado es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        $matriculas = DB::table("periodo_curso as a")
            ->join("matricula_curso as b", "a.id_periodocurso", "b.id_periodocurso")
            ->join("matricula as c", "b.id_matricula", "c.id_matricula")
            ->leftJoin("asistencia as d", "a.id_periodocurso", DB::raw("d.id_periodocurso and c.id_estudiante = d.id_persona and d.fecha = '".$request->fecha."'"))
            ->select(
                "c.id_estudiante",
                "c.id_empresa"

            )
            ->where("a.id_periodocurso", $request->id_periodocurso)
            ->where("c.estado", "1")
            ->whereNull("d.id_asistencia")
            ->get();

        $inserts = [];
        foreach($matriculas as $matricula){
            $asistencia = [];
            $asistencia["id_empresa"] = $matricula->id_empresa;
            $asistencia["id_periodocurso"] = $request->id_periodocurso;
            $asistencia["id_persona"] = $matricula->id_estudiante;
            $asistencia["fecha"] = $request->fecha;
            $asistencia["estado"] = $request->estado;
            $asistencia["tipo"] = $request->tipo;
            $asistencia["id_usuarioreg"] = $user->id_usuario;
            $asistencia["fechareg"] = now();
            $inserts[] = $asistencia;
        }

        if(count($inserts) > 0){
            Asistencia::insert($inserts);
        }

        return response()->json([]);
    }
}