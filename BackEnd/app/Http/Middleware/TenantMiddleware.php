<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Bug 4: Filtro automático por empresa.
     * Inyecta el id_empresa del usuario autenticado en el request
     * para que los controllers filtren datos por empresa.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json('No autenticado.', 401);
        }

        // Obtener el id_empresa del usuario desde usuario_rol
        $usuarioRol = DB::table('usuario_rol')
            ->where('id_usuario', $user->id_usuario)
            ->where('es_principal', '1')
            ->first();

        if (!$usuarioRol || !$usuarioRol->id_empresa) {
            // Superadministradores pueden no tener empresa
            $esSuperAdmin = DB::table('usuario_rol')
                ->where('id_usuario', $user->id_usuario)
                ->where('id_rol', 1) // rol superadmin
                ->exists();

            if ($esSuperAdmin) {
                return $next($request);
            }

            return response()->json('No se encontró empresa asociada al usuario.', 403);
        }

        // Inyectar el id_empresa en el request para que los controllers puedan usarlo
        $request->merge(['tenant_empresa_id' => $usuarioRol->id_empresa]);

        return $next($request);
    }
}
