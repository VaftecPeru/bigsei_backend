<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DiaController extends Controller
{
    public function activos()
    {
        $dias = DB::table("dia")
            ->select(
                "id_dia",
                "nombre",
                "orden"
            )
            ->where("estado", "1")
            ->orderBy("orden", "asc")
            ->orderBy("id_dia", "asc")
            ->get();

        return response()->json($dias);
    }
}