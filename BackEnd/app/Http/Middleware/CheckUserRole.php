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
     * @param  string  $role  // Este es el tercer parámetro que es el rol que pasas desde la ruta
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        try {
            $authorization = $request->header('Authorization') ?? "";
            $token = str_replace("Bearer ", "", $authorization);
            
            \Illuminate\Support\Facades\Log::info("CheckUserRole: Checking access for role: " . $role);
            // \Illuminate\Support\Facades\Log::info("Token received: " . substr($token, 0, 10) . "...");

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

            if($usuarioSesion->codigo != $role) {
                \Illuminate\Support\Facades\Log::warning("CheckUserRole: Role mismatch. Expected: $role, Found: " . $usuarioSesion->codigo);
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
