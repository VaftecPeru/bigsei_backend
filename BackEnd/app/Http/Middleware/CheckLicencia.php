<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckLicencia
{
    /**
     * Bug 5: Verificar que la empresa tiene licencia vigente.
     * Si la licencia está vencida, bloquea el acceso.
     * Superadministradores están excluidos.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json('No autenticado.', 401);
        }

        // Verificar si es superadministrador (excluir del check)
        $esSuperAdmin = DB::table('usuario_rol')
            ->where('id_usuario', $user->id_usuario)
            ->where('id_rol', 1) // rol superadmin
            ->exists();

        if ($esSuperAdmin) {
            return $next($request);
        }

        // Obtener la empresa del usuario
        $usuarioRol = DB::table('usuario_rol')
            ->where('id_usuario', $user->id_usuario)
            ->where('es_principal', '1')
            ->first();

        if (!$usuarioRol || !$usuarioRol->id_empresa) {
            return $next($request); // Usuarios sin empresa (ej: estudiantes web)
        }

        // Verificar si la empresa tiene licencia vigente
        $licenciaVigente = DB::table('licencia')
            ->where('id_empresa', $usuarioRol->id_empresa)
            ->where('estado', '1')
            ->where('fecha_fin', '>=', now())
            ->exists();

        if (!$licenciaVigente) {
            return response()->json([
                'message' => 'La licencia de su empresa ha vencido. Contacte al administrador para renovarla.',
                'licencia_vencida' => true,
            ], 403);
        }

        return $next($request);
    }
}
