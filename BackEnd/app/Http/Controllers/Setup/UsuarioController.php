<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\UsuarioRol;
use App\Models\UsuarioSesion;

class UsuarioController extends Controller
{
    public function userLogin(Request $request)
    {
        $user = $request->get("sessionUser");
        $usuario = DB::table("usuario as a")
            ->join("persona as b", "a.id_usuario", "b.id_persona")
            ->join("usuario_rol as c", "a.id_usuario", "c.id_usuario")
            ->join("rol as d", "c.id_rol", "d.id_rol")
            ->select(
                "a.id_usuario",
                "b.nombre_completo as nombre",
                "b.correo",
                "b.telefono",
                "b.id_tipodocumento",
                "b.numero_documento",
                "b.direccion",
                "b.id_archivo_foto",
                DB::raw("d.nombre as rol_nombre")
            )
            ->where("a.id_usuario", $user->id_usuario)
            ->where("c.es_principal", "1")
            ->first();

        $empresa = DB::table("empresa")
            ->select(
                "razon_social"
            )
            ->where("id_empresa", $user->id_empresa)
            ->first();

        $userData = [
            "id_usuario" => $usuario->id_usuario ?? "",
            "nombre" => $usuario->nombre ?? "",
            "correo" => $usuario->correo ?? "",
            "telefono" => $usuario->telefono ?? "",
            "id_tipodocumento" => $usuario->id_tipodocumento ?? "",
            "numero_documento" => $usuario->numero_documento ?? "",
            "direccion" => $usuario->direccion ?? "",
            "id_archivo_foto" => $usuario->id_archivo_foto ?? "",
            "rol_nombre" => $usuario->rol_nombre ?? "",
            "razon_social" => $empresa->razon_social ?? "",
        ];

        return response()->json($userData);
    }

    public function userModulos(Request $request)
    {
        $user = $request->get('sessionUser');
        $result = DB::table("usuario_rol as a")
            ->join("rol_modulo as b", "a.id_rol", "b.id_rol")
            ->join("modulo as c", "b.id_modulo", "c.id_modulo")
            ->select(
                "c.id_modulo",
                "c.nombre",
                "c.url",
                "c.url_activa",
                "c.icon",
                "c.orden",
                "a.id_usuario"
            )
            ->where("a.id_usuario", $user->id_usuario)
            ->where("a.es_principal", "1")
            ->where("c.estado", "1")
            ->whereNull("c.id_modulosup")
            ->orderBy("orden", "asc")
            ->get()
            ->map(function ($modulo) {
                $modulos = DB::table("usuario_rol as a")
                    ->join("rol_modulo as b", "a.id_rol", "b.id_rol")
                    ->join("modulo as c", "b.id_modulo", "c.id_modulo")
                    ->select(
                        "c.nombre",
                        "c.url",
                        "c.url_activa",
                        "c.icon"
                    )
                    ->where("a.id_usuario", $modulo->id_usuario)
                    ->where("c.id_modulosup", $modulo->id_modulo)
                    ->get();
                return [
                    "nombre" => $modulo->nombre,
                    "url" => $modulo->url,
                    "url_activa" => $modulo->url_activa,
                    "icon" => $modulo->icon,
                    "modulos" => $modulos
                ];
            });

        return response()->json($result);
    }

    public function userRoles(Request $request)
    {
        $user = $request->get('sessionUser');
        $result = DB::table("usuario_rol as a")
            ->join("rol as b", "a.id_rol", "b.id_rol")
            ->select(
                "a.id_usuariorol",
                "a.id_rol",
                "b.nombre"
            )
            ->where("a.id_usuario", $user->id_usuario);

        if (isset($request->id_empresa)) {
            $result->where("a.id_empresa", $request->id_empresa);
        } else {
            $result->whereNull("a.id_empresa");
        }

        $result = $result->get();

        return response()->json($result);
    }

    public function updateEsPrincipal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_empresa' => 'nullable',
            'id_rol' => 'required',
        ], [
            'id_rol.required' => 'El rol es requerido',
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",", $validator->messages()->all()),
                400
            );
        }

        $user = $request->sessionUser;

        if (isset($request->id_empresa)) {
            $usuarioRol = UsuarioRol::where("id_usuario", $user->id_usuario)
                ->where("id_rol", $request->id_rol)
                ->where("id_empresa", $request->id_empresa)
                ->first();
        } else {
            $usuarioRol = UsuarioRol::where("id_usuario", $user->id_usuario)
                ->where("id_rol", $request->id_rol)
                ->whereNull("id_empresa")
                ->first();
        }

        if (!$usuarioRol) {
            return response()->json("No se encontro el rol.", 400);
        } else if ($usuarioRol->es_principal == "1") {
            return response()->json("Atenciòn! Es su rol actual.", 400);
        }

        $usuarioRolEdit = [];
        $usuarioRolEdit["es_principal"] = "0";
        UsuarioRol::where("id_usuario", $user->id_usuario)
            ->update($usuarioRolEdit);

        $usuarioRolEdit = [];
        $usuarioRolEdit["es_principal"] = "1";
        $usuarioRol->update($usuarioRolEdit);

        $usuarioSesion = UsuarioSesion::find($user->id_usuariosesion);
        $usuarioSesionEdit = [];
        $usuarioSesionEdit["id_rol"] = $request->id_rol;
        $usuarioSesionEdit["id_empresa"] = $request->id_empresa;
        $usuarioSesion->update($usuarioSesionEdit);

        $modulo = DB::table("usuario as a")
            ->join('usuario_rol as b', 'a.id_usuario', 'b.id_usuario')
            ->join('rol as c', 'b.id_rol', 'c.id_rol')
            ->join('rol_modulo as d', 'c.id_rol', 'd.id_rol')
            ->join('modulo as e', 'd.id_modulo', 'e.id_modulo')
            ->select(
                "e.id_modulo",
                "e.url"
            )
            ->where('a.id_usuario', $user->id_usuario)
            ->where("b.id_rol", $request->id_rol)
            ->where('a.estado', '1')
            ->where('b.es_principal', '1')
            ->where('e.url_activa', '1')
            ->orderBy("id_modulo", "asc")
            ->first();

        return response()->json([
            "url" => $modulo ? $modulo->url : "",
        ]);
    }

    public function userEmpresas(Request $request)
    {
        $user = $request->sessionUser;
        $result = DB::table("usuario_rol as a")
            ->join("empresa as b", "a.id_empresa", "b.id_empresa")
            ->select(
                "a.id_empresa",
                "b.razon_social"
            )
            ->where("a.id_usuario", $user->id_usuario)
            ->groupBy("a.id_empresa", "b.razon_social")
            ->get();

        return response()->json($result);
    }
}
