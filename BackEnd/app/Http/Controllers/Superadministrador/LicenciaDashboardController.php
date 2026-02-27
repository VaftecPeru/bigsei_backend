<?php

namespace App\Http\Controllers\Superadministrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * LicenciaDashboardController — Dashboard de uso, alertas, módulos y onboarding de licencias.
 */
class LicenciaDashboardController extends Controller
{
    /**
     * GET /superadmin/licencias/dashboard-uso
     * Datos de uso global de todas las licencias (usuarios activos, módulos usados, etc.).
     */
    public function dashboardUso(Request $request)
    {
        try {
            $datos = DB::table('licencia as l')
                ->join('empresa as e', 'l.id_empresa', '=', 'e.id_empresa')
                ->join('licencia_tipo as lt', 'l.id_licenciatipo', '=', 'lt.id_licenciatipo')
                ->where('l.estado', '1')
                ->where('l.fecha_fin', '>=', now())
                ->select(
                    'e.id_empresa',
                    'e.razon_social',
                    'lt.nombre as tipo_licencia',
                    'l.fecha_fin',
                    DB::raw("DATEDIFF(l.fecha_fin, NOW()) as dias_restantes"),
                    DB::raw("(SELECT COUNT(*) FROM persona p WHERE p.id_empresa = e.id_empresa AND p.estado = '1') as usuarios_activos"),
                    DB::raw("(SELECT COUNT(*) FROM matricula m WHERE m.id_empresa = e.id_empresa) as total_matriculas")
                )
                ->orderBy('dias_restantes', 'asc')
                ->get();

            // Estadísticas globales
            $stats = [
                'total_licencias_activas'  => DB::table('licencia')->where('estado', '1')->where('fecha_fin', '>=', now())->count(),
                'total_licencias_vencidas' => DB::table('licencia')->where('fecha_fin', '<', now())->count(),
                'total_empresas'           => DB::table('empresa')->where('estado', '1')->count(),
                'vencen_este_mes'          => DB::table('licencia')
                    ->where('estado', '1')
                    ->whereBetween('fecha_fin', [now(), now()->endOfMonth()])
                    ->count(),
            ];

            return response()->json([
                'stats'    => $stats,
                'empresas' => $datos,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /superadmin/licencias/alertas
     * Empresas cuya licencia vence en los próximos N días.
     */
    public function alertas(Request $request)
    {
        $dias = $request->input('dias', 30); // Por defecto 30 días

        try {
            $porVencer = DB::table('licencia as l')
                ->join('empresa as e', 'l.id_empresa', '=', 'e.id_empresa')
                ->join('licencia_tipo as lt', 'l.id_licenciatipo', '=', 'lt.id_licenciatipo')
                ->where('l.estado', '1')
                ->whereBetween('l.fecha_fin', [now(), now()->addDays($dias)])
                ->select(
                    'l.id_licencia',
                    'e.razon_social',
                    'e.correo',
                    'lt.nombre as tipo_licencia',
                    'l.fecha_fin',
                    DB::raw("DATEDIFF(l.fecha_fin, NOW()) as dias_restantes")
                )
                ->orderBy('l.fecha_fin', 'asc')
                ->get();

            $vencidas = DB::table('licencia as l')
                ->join('empresa as e', 'l.id_empresa', '=', 'e.id_empresa')
                ->join('licencia_tipo as lt', 'l.id_licenciatipo', '=', 'lt.id_licenciatipo')
                ->where('l.fecha_fin', '<', now())
                ->select(
                    'l.id_licencia',
                    'e.razon_social',
                    'e.correo',
                    'lt.nombre as tipo_licencia',
                    'l.fecha_fin',
                    DB::raw("DATEDIFF(NOW(), l.fecha_fin) as dias_vencida")
                )
                ->orderBy('l.fecha_fin', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'por_vencer' => $porVencer,
                'vencidas'   => $vencidas,
                'resumen'    => [
                    'por_vencer_count' => $porVencer->count(),
                    'vencidas_count'   => $vencidas->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /superadmin/licencias/modulos
     * Módulos disponibles y cuáles están activados por empresa.
     */
    public function modulos(Request $request)
    {
        try {
            $modulos = DB::table('modulo as m')
                ->select('m.id_modulo', 'm.nombre', 'm.descripcion', 'm.estado')
                ->get();

            $modulosPorEmpresa = null;
            if ($request->has('id_empresa')) {
                $modulosPorEmpresa = DB::table('empresa_modulo as em')
                    ->join('modulo as m', 'em.id_modulo', '=', 'm.id_modulo')
                    ->where('em.id_empresa', $request->id_empresa)
                    ->where('em.activo', 1)
                    ->select('m.id_modulo', 'm.nombre', 'm.descripcion')
                    ->get();
            }

            return response()->json([
                'modulos_disponibles' => $modulos,
                'modulos_empresa'     => $modulosPorEmpresa,
            ]);
        } catch (\Exception $e) {
            return response()->json(['modulos_disponibles' => [], 'modulos_empresa' => null]);
        }
    }

    /**
     * POST /superadmin/licencias/{id}/modulos
     * Activar/desactivar módulos para una empresa.
     */
    public function toggleModulo(Request $request, $id_licencia)
    {
        $licencia = DB::table('licencia')->where('id_licencia', $id_licencia)->first();
        if (!$licencia) {
            return response()->json(['error' => 'Licencia no encontrada'], 404);
        }

        $idModulo = $request->id_modulo;
        $activo   = $request->activo ? 1 : 0;

        try {
            DB::table('empresa_modulo')->updateOrInsert(
                ['id_empresa' => $licencia->id_empresa, 'id_modulo' => $idModulo],
                ['activo' => $activo, 'updated_at' => now()]
            );

            return response()->json([
                'success' => true,
                'message' => $activo ? 'Módulo activado' : 'Módulo desactivado',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /superadmin/licencias/{id}/onboarding
     * Estado del onboarding de la empresa (primeros pasos completados).
     */
    public function onboarding(Request $request, $id_licencia)
    {
        $licencia = DB::table('licencia')->where('id_licencia', $id_licencia)->first();
        if (!$licencia) {
            return response()->json(['error' => 'Licencia no encontrada'], 404);
        }

        $idEmpresa = $licencia->id_empresa;

        try {
            $pasos = [
                'empresa_configurada' => DB::table('empresa')
                    ->where('id_empresa', $idEmpresa)
                    ->whereNotNull('direccion')
                    ->whereNotNull('correo')
                    ->exists(),

                'sede_creada'         => DB::table('sede')
                    ->where('id_empresa', $idEmpresa)
                    ->exists(),

                'admin_creado'        => DB::table('persona')
                    ->where('id_empresa', $idEmpresa)
                    ->where('id_rol', 2) // rol admin
                    ->exists(),

                'estudiante_creado'   => DB::table('persona')
                    ->where('id_empresa', $idEmpresa)
                    ->where('id_rol', 5) // rol estudiante (ejemplo)
                    ->exists(),

                'carrera_creada'      => DB::table('carrera')
                    ->where('id_empresa', $idEmpresa)
                    ->exists(),

                'periodo_creado'      => DB::table('periodo')
                    ->where('id_empresa', $idEmpresa)
                    ->exists(),
            ];

            $completados = count(array_filter($pasos));
            $total       = count($pasos);

            return response()->json([
                'pasos'         => $pasos,
                'completados'   => $completados,
                'total'         => $total,
                'porcentaje'    => round($completados / $total * 100),
                'onboarding_ok' => $completados === $total,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /superadmin/licencias/{id}/limites
     * Límites de usuarios/estudiantes/cursos según tipo de licencia.
     */
    public function limites(Request $request, $id_licencia)
    {
        $licencia = DB::table('licencia as l')
            ->join('licencia_tipo as lt', 'l.id_licenciatipo', '=', 'lt.id_licenciatipo')
            ->join('empresa as e', 'l.id_empresa', '=', 'e.id_empresa')
            ->where('l.id_licencia', $id_licencia)
            ->select('l.*', 'lt.nombre as tipo', 'lt.max_usuarios', 'lt.max_estudiantes', 'lt.max_cursos', 'e.razon_social')
            ->first();

        if (!$licencia) {
            return response()->json(['error' => 'Licencia no encontrada'], 404);
        }

        try {
            $usuariosActuales    = DB::table('persona')->where('id_empresa', $licencia->id_empresa)->where('estado', '1')->count();
            $estudiantesActuales = DB::table('persona')->where('id_empresa', $licencia->id_empresa)->where('id_rol', 5)->count();
            $cursosActuales      = DB::table('curso')->where('id_empresa', $licencia->id_empresa)->count();

            return response()->json([
                'empresa'     => $licencia->razon_social,
                'tipo'        => $licencia->tipo,
                'limites'     => [
                    'max_usuarios'    => $licencia->max_usuarios ?? 'ilimitado',
                    'max_estudiantes' => $licencia->max_estudiantes ?? 'ilimitado',
                    'max_cursos'      => $licencia->max_cursos ?? 'ilimitado',
                ],
                'uso_actual'  => [
                    'usuarios'    => $usuariosActuales,
                    'estudiantes' => $estudiantesActuales,
                    'cursos'      => $cursosActuales,
                ],
                'alertas'     => [
                    'usuarios_al_limite'    => ($licencia->max_usuarios && $usuariosActuales >= $licencia->max_usuarios),
                    'estudiantes_al_limite' => ($licencia->max_estudiantes && $estudiantesActuales >= $licencia->max_estudiantes),
                    'cursos_al_limite'      => ($licencia->max_cursos && $cursosActuales >= $licencia->max_cursos),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
