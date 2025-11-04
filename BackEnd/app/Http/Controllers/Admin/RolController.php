<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\UsuarioRol;

class RolController extends Controller
{
    public function index(Request $request)
    {
        if(!isset($request->id_usuario)) {
            return response()->json('¡Atención! El usuario es requerido.', 400);
        }
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $user = $request->sessionUser;

        $paginate = DB::table("rol as a")
            ->leftJoin("usuario_rol as b", "a.id_rol", DB::raw("b.id_rol and b.id_empresa = ".$user->id_empresa." and b.id_usuario = ".$request->id_usuario))
            ->select(
                "a.id_rol",
                "a.nombre",
                "a.codigo",
                "b.id_usuariorol",
                "b.es_principal",
                DB::raw("case when b.id_usuario is not null then '1' else '0' end as rol_esta_habilitado")
            )
            ->where("es_administrador", "0");

        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $paginate->whereRaw("upper(concat(a.nombre, a.codigo)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $paginate->orderBy("id_rol", "asc");
        $paginate = $paginate->paginate($per_page);

        return response()->json($paginate);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_usuario" => "required",
            "id_rol" => "required",
        ], [
            "id_usuario.required" => "El usuario es requerido",
            "id_rol.required" => "El rol es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;

        $usuarioRol = UsuarioRol::where("id_usuario", $request->id_usuario)
            ->where("id_rol", $request->id_rol)
            ->where("id_empresa", $user->id_empresa)
            ->first();
        if ($usuarioRol) {
            return response()->json("¡Atención! El rol esta registrado.", 400);
        }

        $usuarioRol = [];
        $usuarioRol["id_empresa"] = $user->id_empresa;
        $usuarioRol["id_usuario"] = $request->id_usuario;
        $usuarioRol["id_rol"] = $request->id_rol;
        $usuarioRol["es_principal"] = "0";
        $usuarioRol = UsuarioRol::create($usuarioRol);

        return response()->json($usuarioRol);
    }

    public function destroy($id_usuariorol)
    {
        $usuarioRol = UsuarioRol::find($id_usuariorol);
        if(!$usuarioRol) {
            return response()->json("¡Atención! Usuario y rol no encontrado.", 400);
        }

        $usuarioRol->delete();

        return response()->json([]);
    }

    public function elegirPrincipal(Request $request, $id_usuariorol)
    {
        $user = $request->sessionUser;

        $usuarioRol = UsuarioRol::where("id_usuariorol", $id_usuariorol)
            ->where("id_empresa", $user->id_empresa)
            ->first();
        if (!$usuarioRol) {
            return response()->json("¡Atención! Rol no encontrado.", 400);
        }

        $usuarioRolEdit = [];
        $usuarioRolEdit["es_principal"] = "1";
        $usuarioRol->update($usuarioRolEdit);

        $usuarioRolEdit = [];
        $usuarioRolEdit["es_principal"] = "0";
        UsuarioRol::where("id_usuario", $usuarioRol->id_usuario)
            ->where("id_empresa", $user->id_empresa)
            ->where("id_usuariorol", "!=", $id_usuariorol)
            ->update($usuarioRolEdit);

        return response()->json([]);
    }
}