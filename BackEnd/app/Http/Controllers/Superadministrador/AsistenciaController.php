<?php

namespace App\Http\Controllers\Superadministrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Asistencia;

class AsistenciaController extends Controller
{
    public function index(Request $request)
    {
        $result = DB::table('asistencia as a')
            ->join("persona as b", "a.id_persona", "b.id_persona")
            ->join("periodo_curso as c", "a.id_periodocurso", "c.id_periodocurso")
            ->join("curso as d", "c.id_curso", "d.id_curso")
            ->select(
                'a.id_asistencia',
                'a.id_periodocurso',
                'a.id_persona',
                'a.justificacion',
                'a.estado',
                'a.tipo',
                'b.numero_documento',
                DB::raw("d.nombre as curso_nombre"),
                DB::raw("b.nombre_completo")
            );
        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(b.nombre_completo)) LIKE upper( ? )", ['%'.$texto.'%']);
        }
        if(isset($request->tipo)) {
            $result->where("tipo", $request->tipo);
        }
        $result->orderBy("id_asistencia", "desc");
        $result = $result->get();

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_periodocurso' => 'required',
            'id_persona' => 'required',
            'justificacion' => 'nullable|string|max:255',
            'estado' => 'required|string|max:1',
            'tipo' => 'required|string|max:1',
        ], [
            'id_periodocurso.required' => 'El periodo curso es requerido',
            'id_persona.required' => 'El persona es requerido',
            'justificacion.max' => 'La justificaciòn solo debe tener 255 caracteres',
            'estado.required' => 'El estado es requerido',
            'tipo.required' => 'El tipo es requerido',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $asistencia = [];
        $asistencia["id_empresa"] = 1;
        $asistencia["id_periodocurso"] = $request->id_periodocurso;
        $asistencia["id_persona"] = $request->id_persona;
        $asistencia["justificacion"] = $request->justificacion;
        $asistencia["estado"] = $request->estado;
        $asistencia["id_usuarioreg"] = 1;
        $asistencia["fechareg"] = now();
        $asistencia["tipo"] = $request->tipo;
        $asistencia = Asistencia::create($asistencia);

        $result = Asistencia::find($asistencia->id_asistencia);

        return response()->json($result);
    }
}