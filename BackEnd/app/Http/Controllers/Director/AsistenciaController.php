<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsistenciaController extends Controller
{
    public function estadisticas(Request $request)
    {
        $result = DB::table('asistencia as a')
            ->join("periodo_curso as b", "a.id_periodocurso", "b.id_periodocurso")
            ->select(
                'b.id_periodociclo',
                'a.tipo',
                DB::raw("sum(1) as cant_total"),
                DB::raw("sum(case when a.estado = 'A' then 1 when a.estado = 'T' then 1 else 0 end) as cant_asistencia"),
                DB::raw("sum(case when a.estado = 'F' then 1 else 0 end) as cant_inasistencia"),
                DB::raw("case a.tipo
                    when 'E' then 'Estudiantes'
                    when 'D' then 'Docentes'
                    when 'A' then 'Administrativos'
                    end as tipo_persona")
            );

        if(isset($request->id_periodociclo)) {
            $result->where("b.id_periodociclo", $request->id_periodociclo);
        }

        $result->groupBy("b.id_periodociclo", "a.tipo");
        $result->orderBy("id_asistencia", "desc");
        $result = $result->get();

        return response()->json($result);
    }
}