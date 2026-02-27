<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Models\UsuarioSesion;

class CheckUser
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
            // Leer token: primero desde el header Authorization, luego desde la cookie HttpOnly
            $authorization = $request->header('Authorization') ?? "";
            $token = str_replace("Bearer ", "", $authorization);

            // #2 XSS: Si no viene en el header, intentar desde la cookie HttpOnly
            if (empty($token)) {
                $token = $request->cookie('token') ?? "";
            }
            $usuarioSesion = UsuarioSesion::where("token", $token)
                ->where("estado", "1")
                ->first();

            if (!$usuarioSesion) {
                return response()->json(['error' => ' Acceso denegado: No esta autorizado.'], 403);
            }

            // $request->attributes->set('sessionUser', $usuarioSesion);
            $request->merge(["sessionUser" => $usuarioSesion]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Token inválido o no proporcionado.'], 401);
        }

        return $next($request);
    }
}
