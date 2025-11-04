<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArchivoController extends Controller
{
    public function indexTema(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }

        DB::statement("SET lc_time_names = 'es_ES'");
        $archivos = DB::table("archivo")
            ->select(
                "id_archivo",
                "nombre",
                "extension",
                "tamanho",
                DB::raw("concat(dayname(fechareg), ' ', date_format(fechareg, '%d/%m/%Y %h:%i %p')) as fecha_full")
            )
            ->where("id_periodotema", $request->id_periodotema)
            ->orderBy("id_archivo", "asc")
            ->paginate($per_page);

        return response()->json($archivos);
    }
}