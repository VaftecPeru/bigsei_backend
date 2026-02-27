<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FacturacionAdminController extends Controller
{
    /**
     * Listar ingresos de la sede (membresías activas de la empresa actual).
     */
    public function index(Request $request)
    {
        $user = $request->sessionUser;
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        try {
            $id_empresa = $user->id_empresa;

            $ingresos = DB::table('membresia as m')
                ->join('persona as p', 'm.id_persona', '=', 'p.id_persona')
                ->join('membresia_tipo as mt', 'm.id_membresiatipo', '=', 'mt.id_membresiatipo')
                ->select(
                    'm.id_membresia',
                    DB::raw("COALESCE(NULLIF(CONCAT_WS(' ', p.nombre, p.apellido_paterno, p.apellido_materno), ''), p.nombre_completo) as usuario"),
                    'mt.nombre as tipo',
                    'mt.precio',
                    'm.fecha_inicio',
                    'm.fecha_fin',
                    'm.estado'
                )
                ->where('p.id_empresa', $id_empresa)
                ->orderBy('m.fecha_inicio', 'desc')
                ->get();

            $total_ingresos = $ingresos->where('estado', 1)->sum('precio');

            return response()->json([
                'data' => $ingresos,
                'total_ingresos' => $total_ingresos,
                'total_membresias' => $ingresos->count(),
                'activas' => $ingresos->where('estado', 1)->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al cargar facturación',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resumen mensual de ingresos de la sede.
     */
    public function resumen(Request $request)
    {
        $user = $request->sessionUser;
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        try {
            $id_empresa = $user->id_empresa;

            $resumenMensual = DB::table('membresia as m')
                ->join('persona as p', 'm.id_persona', '=', 'p.id_persona')
                ->join('membresia_tipo as mt', 'm.id_membresiatipo', '=', 'mt.id_membresiatipo')
                ->select(
                    DB::raw("DATE_FORMAT(m.fecha_inicio, '%Y-%m') as mes"),
                    DB::raw('COUNT(*) as cantidad'),
                    DB::raw('SUM(mt.precio) as total')
                )
                ->where('p.id_empresa', $id_empresa)
                ->where('m.estado', 1)
                ->groupBy(DB::raw("DATE_FORMAT(m.fecha_inicio, '%Y-%m')"))
                ->orderBy('mes', 'desc')
                ->limit(12)
                ->get();

            return response()->json($resumenMensual);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al cargar resumen',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}
