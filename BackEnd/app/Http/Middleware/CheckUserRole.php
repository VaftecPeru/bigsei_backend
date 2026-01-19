<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Models\UsuarioSesion;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        try {
            $authorization = $request->header('Authorization') ?? "";
            $token = str_replace("Bearer ", "", $authorization);
            $allowedRoles = [];
            foreach ($roles as $role) {
                $allowedRoles = array_merge($allowedRoles, explode(',', $role));
            }
            $allowedRoles = array_map('trim', $allowedRoles);
            
            \Illuminate\Support\Facades\Log::info("CheckUserRole: Checking access for roles: " . implode(', ', $allowedRoles));

            $usuarioSesion = UsuarioSesion::select("usuario_sesion.*", "b.codigo")
                ->join("rol as b", "usuario_sesion.id_rol", "b.id_rol")
                ->where("usuario_sesion.token", $token)
                ->where("usuario_sesion.estado", "1")
                ->first();

            if(!$usuarioSesion) {
                \Illuminate\Support\Facades\Log::warning("CheckUserRole: Session not found or inactive for token.");
                return response()->json(['error' => ' Acceso denegado: No esta autorizado.'], 403);
            }

            \Illuminate\Support\Facades\Log::info("CheckUserRole: User role found: " . $usuarioSesion->codigo);

            // Verificar si el rol del usuario está en la lista de roles permitidos
            if(!in_array($usuarioSesion->codigo, $allowedRoles)) {
                \Illuminate\Support\Facades\Log::warning("CheckUserRole: Role mismatch. Expected one of: " . implode(', ', $allowedRoles) . ", Found: " . $usuarioSesion->codigo);
                return response()->json(['error' => ' Acceso denegado: No tienes el rol.'], 403);
            }

            $request->merge(["sessionUser" => $usuarioSesion]);
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error("CheckUserRole Error: " . $e->getMessage());
            return response()->json(['error' => 'Token inválido o no proporcionado.'], 401);
        }

        return $next($request);
    }
}
