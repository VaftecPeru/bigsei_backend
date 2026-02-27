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
            $usuarioSesion = UsuarioSesion::select("usuario_sesion.*", "b.codigo")
                ->join("rol as b", "usuario_sesion.id_rol", "b.id_rol")
                ->where("usuario_sesion.token", $token)
                ->where("usuario_sesion.estado", "1")
                ->first();

            if(!$usuarioSesion) {
                return response()->json(['error' => ' Acceso denegado: No esta autorizado.'], 403);
            }

            if($usuarioSesion->codigo != $role) {
                return response()->json(['error' => ' Acceso denegado: No tienes el rol.'], 403);
            }

            $request->merge(["sessionUser" => $usuarioSesion]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Token inválido o no proporcionado.'], 401);
        }

        return $next($request);
    }
}
