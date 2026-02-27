<?php

namespace App\Http\Controllers\Superadministrador;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/**
 * ReportesController — Reportes de crecimiento e indicadores generales del sistema.
 */
class ReportesController extends Controller
{
    public function crecimiento()
    {
        // Crecimiento de empresas (licencias) por mes, últimos 12 meses
        $empresasPorMes = DB::select("
            SELECT DATE_FORMAT(fechareg, '%Y-%m') as mes,
                   COUNT(*) as cantidad
            FROM empresa
            WHERE fechareg >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY mes ORDER BY mes ASC
        ");

        // Crecimiento de membresías por mes
        $membresiasPorMes = DB::select("
            SELECT DATE_FORMAT(fechareg, '%Y-%m') as mes,
                   COUNT(*) as cantidad
            FROM membresia
            WHERE fechareg >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY mes ORDER BY mes ASC
        ");

        // Crecimiento de usuarios por mes
        $usuariosPorMes = DB::select("
            SELECT DATE_FORMAT(fechareg, '%Y-%m') as mes,
                   COUNT(*) as cantidad
            FROM usuario
            WHERE fechareg >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY mes ORDER BY mes ASC
        ");

        return response()->json([
            'empresas_por_mes'   => $empresasPorMes,
            'membresias_por_mes' => $membresiasPorMes,
            'usuarios_por_mes'   => $usuariosPorMes,
        ]);
    }

    public function dashboard()
    {
        $totalEmpresas        = DB::table('empresa')->count();
        $totalUsuarios        = DB::table('usuario')->where('estado', '1')->count();
        $licenciasActivas     = DB::table('licencia')->where('estado', '1')->where('fecha_fin', '>=', now())->count();
        $licenciasVencidas    = DB::table('licencia')->where('fecha_fin', '<', now())->count();
        $membresíasActivas    = DB::table('membresia')->where('estado', '1')->where('fecha_fin', '>=', now())->count();
        $membresíasVencidas   = DB::table('membresia')->where('fecha_fin', '<', now())->count();
        $usuariosPendientes   = DB::table('usuario')->where('estado', '0')->count();

        // Ingresos totales estimados (licencias activas)
        $ingresoLicencias = DB::table('licencia')->where('estado', '1')->sum('precio');
        $ingresoMembresias = DB::table('membresia as a')
            ->join('membresia_tipo as b', 'a.id_membresiatipo', 'b.id_membresiatipo')
            ->where('a.estado', '1')
            ->sum('b.precio');

        return response()->json([
            'total_empresas'       => $totalEmpresas,
            'total_usuarios'       => $totalUsuarios,
            'licencias_activas'    => $licenciasActivas,
            'licencias_vencidas'   => $licenciasVencidas,
            'membresias_activas'   => $membresíasActivas,
            'membresias_vencidas'  => $membresíasVencidas,
            'usuarios_pendientes'  => $usuariosPendientes,
            'ingreso_licencias'    => $ingresoLicencias,
            'ingreso_membresias'   => $ingresoMembresias,
            'ingreso_total'        => $ingresoLicencias + $ingresoMembresias,
        ]);
    }

    public function pendientesUsuarios()
    {
        $pendientes = DB::table('usuario as a')
            ->leftJoin('persona as b', 'a.id_usuario', 'b.id_persona')
            ->leftJoin('usuario_rol as c', 'a.id_usuario', 'c.id_usuario')
            ->leftJoin('empresa as d', 'c.id_empresa', 'd.id_empresa')
            ->select(
                'a.id_usuario',
                'a.username',
                'a.email',
                DB::raw("COALESCE(b.nombre_completo, a.username) as nombre"),
                'a.fechareg',
                'd.razon_social as empresa',
            )
            ->where('a.estado', '0')
            ->orderBy('a.fechareg', 'desc')
            ->get();

        return response()->json($pendientes);
    }

    public function aprobarUsuario($id_usuario)
    {
        DB::table('usuario')->where('id_usuario', $id_usuario)->update(['estado' => '1']);
        return response()->json(['message' => 'Usuario aprobado correctamente']);
    }

    public function rechazarUsuario($id_usuario)
    {
        DB::table('usuario')->where('id_usuario', $id_usuario)->delete();
        return response()->json(['message' => 'Usuario rechazado y eliminado']);
    }
}
