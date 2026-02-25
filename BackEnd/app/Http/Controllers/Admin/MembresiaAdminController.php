<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class MembresiaAdminController extends Controller
{
    public function listarPorSede(Request $request)
    {
        $user = $request->sessionUser;

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        try {
            $id_empresa = $user->id_empresa;

            $membresias = DB::table('membresia as m')
                ->join('persona as p', 'm.id_persona', '=', 'p.id_persona')
                ->join('membresia_tipo as mt', 'm.id_membresiatipo', '=', 'mt.id_membresiatipo')
                ->select(
                    'm.id_membresia',
                    DB::raw("COALESCE(
                    NULLIF(CONCAT_WS(' ', p.nombre, p.apellido_paterno, p.apellido_materno), ''),
                    p.nombre_completo
                ) as usuario"),
                    'mt.nombre as tipo',
                    'mt.descripcion',
                    'mt.precio',
                    'm.fecha_inicio',
                    'm.fecha_fin',
                    'm.estado'
                )
                ->where('m.estado', 1) 
                ->where('p.id_empresa', $id_empresa) 
                ->get();

            return response()->json($membresias);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al cargar membresías',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}
