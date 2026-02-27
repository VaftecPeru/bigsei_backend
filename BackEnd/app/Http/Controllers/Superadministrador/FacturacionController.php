<?php

namespace App\Http\Controllers\Superadministrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * FacturacionController — Panel de facturación e ingresos para el Superadministrador.
 */
class FacturacionController extends Controller
{
    public function index(Request $request)
    {
        // Ingresos por membresías
        $membresiasQuery = DB::table('membresia as a')
            ->join('membresia_tipo as b', 'a.id_membresiatipo', 'b.id_membresiatipo')
            ->join('persona as c', 'a.id_persona', 'c.id_persona')
            ->select(
                DB::raw("'membresia' as tipo"),
                'a.id_membresia as id',
                'c.nombre_completo as cliente',
                'b.nombre as concepto',
                'b.precio as monto',
                'a.fecha_inicio as fecha',
                'a.estado',
            );

        // Ingresos por licencias
        $licenciasQuery = DB::table('licencia as a')
            ->join('licencia_tipo as b', 'a.id_licenciatipo', 'b.id_licenciatipo')
            ->join('empresa as c', 'a.id_empresa', 'c.id_empresa')
            ->select(
                DB::raw("'licencia' as tipo"),
                'a.id_licencia as id',
                'c.razon_social as cliente',
                'b.nombre as concepto',
                'a.precio as monto',
                'a.fecha_inicio as fecha',
                'a.estado',
            );

        if (isset($request->text_search) && strlen($request->text_search) > 0) {
            $texto = '%' . str_replace(' ', '%', $request->text_search) . '%';
            $membresiasQuery->whereRaw("upper(concat(c.nombre_completo, b.nombre)) LIKE upper(?)", [$texto]);
            $licenciasQuery->whereRaw("upper(concat(c.razon_social, b.nombre)) LIKE upper(?)", [$texto]);
        }

        $membresias = $membresiasQuery->get();
        $licencias  = $licenciasQuery->get();
        $todos       = $membresias->merge($licencias)->sortByDesc('fecha')->values();

        return response()->json($todos);
    }

    public function resumen()
    {
        $ingresoMembresias = DB::table('membresia as a')
            ->join('membresia_tipo as b', 'a.id_membresiatipo', 'b.id_membresiatipo')
            ->where('a.estado', '1')
            ->sum('b.precio');

        $ingresoLicencias = DB::table('licencia')
            ->where('estado', '1')
            ->sum('precio');

        $totalMembresias = DB::table('membresia')->where('estado', '1')->count();
        $totalLicencias  = DB::table('licencia')->where('estado', '1')->count();

        // Ingresos por mes (últimos 6 meses)
        $ingresosPorMes = DB::select("
            SELECT DATE_FORMAT(fecha_inicio, '%Y-%m') as mes,
                   SUM(precio) as total,
                   COUNT(*) as cantidad
            FROM licencia
            WHERE estado = '1' AND fecha_inicio >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY mes
            ORDER BY mes ASC
        ");

        return response()->json([
            'ingreso_membresias' => $ingresoMembresias,
            'ingreso_licencias'  => $ingresoLicencias,
            'total_membresias'   => $totalMembresias,
            'total_licencias'    => $totalLicencias,
            'ingresos_por_mes'   => $ingresosPorMes,
        ]);
    }
}
