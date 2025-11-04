<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Persona;
use App\Models\Usuario;
use App\Http\Controllers\Setup\ArchivoController;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Admin\UsuarioMailable;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }

        $paginate = DB::table("usuario as a")
            ->join("persona as b", "a.id_usuario", "b.id_persona")
            ->select(
                "a.id_usuario",
                "b.nombre_completo",
                "b.telefono",
                "b.correo",
                "b.numero_documento",
                "b.id_archivo_foto",
                DB::raw("case when a.estado = '1' then 'Activo' else 'Desactivo' end estado_descripcion")
            );

        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $paginate->whereRaw("upper(concat(b.nombre_completo, b.telefono, b.correo)) LIKE upper( ? )", ['%'.$texto.'%']);
        }

        $paginate->orderBy("nombre_completo", "asc");
        $paginate = $paginate->paginate($per_page);

        return response()->json($paginate);
    }

    public function show($id_usuario)
    {
        $result = DB::table("usuario as a")
            ->join("persona as b", "a.id_usuario", "b.id_persona")
            ->join("tipo_documento as z", "b.id_tipodocumento", "z.id_tipodocumento")
            ->leftJoin("archivo as c", "b.id_archivo_foto", "c.id_archivo")
            ->select(
                "a.id_usuario",
                "a.estado",
                "a.username",
                "a.email",
                "a.correo",
                "b.nombre_completo",
                "b.telefono",
                "b.correo",
                "b.fecha_nacimiento",
                "b.sexo",
                "b.direccion",
                "b.id_tipodocumento",
                DB::raw("z.siglas as tipodocumento_siglas"),
                DB::raw("c.url as archivo_foto_url"),
                DB::raw("b.numero_documento"),
                DB::raw("date_format(b.fecha_nacimiento, '%Y-%m-%d') as fecha_nacimiento_date")
            )
            ->where("a.id_usuario", $id_usuario)
            ->first();

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "nombre_completo" => "required|string|max:450",
            "fecha_nacimiento" => "required|date",
            "telefono" => "required|integer|digits:9",
            "correo" => "required|string|email|max:255",
            "direccion" => "required|string|max:60",
            "sexo" => "required|string|max:1",
            "estado" => "required|string|max:1",
            "id_tipodocumento" => "required",
            "numero_documento" => "required|max:20",
            "username" => "required|max:60|unique:usuario",
            "email" => "required|string|email|max:255|unique:usuario",
            "file" => "nullable|mimes:jpeg,bmp,png,webp,avif,jpg,jfif", // 16 MB (16000 kB). maximo 16 MegaBytes
        ], [
            "nombre_completo.required" => "El nombre es requerido",
            "fecha_nacimiento.required" => "La fecha de nacimiento es requerida",
            "telefono.required" => "El teléfono es requerido",
            "telefono.digits" => "El teléfono debe tener 9 dígitos",
            "correo.required" => "El correo es requerido",
            "correo.email" => "El correo no es válido",
            "direccion.required" => "La dirección es requerida",
            "sexo.required" => "El campo sexo es requerida",
            "estado.required" => "El estado es requerida",
            "id_tipodocumento.required" => "El documento es requerida",
            "numero_documento.required" => "El número de documento es requerida",
            "numero_documento.max" => "El número de documento tiene un máximo 20 caracteres",
            "username.required" => "El usuario es requerido",
            "username.max" => "El usuario tiene un máximo 60 caracteres",
            "username.unique" => "El usuario ya existe",
            "email.required" => "El correo de usuario es requerido",
            "email.max" => "El correo de usuario tiene un máximo 255 caracteres",
            "email.unique" => "El correo de usuario ya existe",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $correoUsuario = Usuario::where("email", $request->email)->first();
        if ($correoUsuario) {
            return response()->json('¡Atención! El correo de usuario en esta registrado por otra persona.', 400);
        }

        $numeroDocumentoPersona = Persona::where("numero_documento", $request->numero_documento)
            ->where("id_tipodocumento", $request->id_tipodocumento)->first();
        if ($numeroDocumentoPersona) {
            return response()->json('¡Atención! El número de documento existe.', 400);
        }

        $usernameUsuario = Usuario::where("username", $request->username)->first();
        if ($usernameUsuario) {
            return response()->json('¡Atención! El usuario existe.', 400);
        }

        $user = $request->sessionUser;

        $archivo = null;
        if ($request->hasFile("file")) {
            $archivo = ArchivoController::registrarArchivo($request->file, $user->id_usuario, "3", null, null, null);
        }

        $persona = [];
        $persona["nombre"] = $request->nombre_completo;
        $persona["nombre_completo"] = $request->nombre_completo;
        $persona["fecha_nacimiento"] = $request->fecha_nacimiento;
        $persona["telefono"] = $request->telefono;
        $persona["correo"] = $request->correo;
        $persona["direccion"] = $request->direccion;
        $persona["sexo"] = $request->sexo;
        $persona["id_tipodocumento"] = $request->id_tipodocumento;
        $persona["numero_documento"] = $request->numero_documento;
        $persona["fechareg"] = now();
        $persona["estado"] = '1';
        if($archivo) {
            $persona["id_archivo_foto"] = $archivo->id_archivo;
        }
        $persona = Persona::create($persona);

        $usuario = [];
        $usuario["id_usuario"] = $persona->id_persona;
        $usuario["username"] = $request->username;
        $usuario["email"] = $request->email;
        $usuario["estado"] = $request->estado;
        $usuario["id_usuarioreg"] = $user->id_usuario;
        $usuario["fechareg"] = now();
        $usuario = Usuario::create($usuario);

        $result = Persona::find($persona->id_persona);

        return response()->json($result);
    }

    public function update(Request $request, $id_usuario)
    {
        $validator = Validator::make($request->all(), [
            "nombre_completo" => "required|string|max:450",
            "fecha_nacimiento" => "required|date",
            "telefono" => "required|integer|digits:9",
            "correo" => "required|string|email|max:255",
            "direccion" => "required|string|max:60",
            "sexo" => "required|string|max:1",
            "estado" => "required|string|max:1",
            "id_tipodocumento" => "required",
            "numero_documento" => "required|max:20",
            "username" => "required|max:60",
            "email" => "required|string|email|max:255",
            "file" => "nullable|mimes:jpeg,bmp,png,webp,avif,jpg,jfif", // 16 MB (16000 kB). maximo 16 MegaBytes
        ], [
            "nombre_completo.required" => "El nombre es requerido",
            "fecha_nacimiento.required" => "La fecha de nacimiento es requerida",
            "telefono.required" => "El teléfono es requerido",
            "telefono.digits" => "El teléfono debe tener 9 dígitos",
            "correo.required" => "El correo es requerido",
            "correo.email" => "El correo no es válido",
            "direccion.required" => "La dirección es requerida",
            "sexo.required" => "El campo sexo es requerida",
            "estado.required" => "El estado es requerida",
            "id_tipodocumento.required" => "El documento es requerida",
            "numero_documento.required" => "El número de documento es requerida",
            "numero_documento.max" => "El número de documento tiene un máximo 20 caracteres",
            "username.required" => "El usuario es requerido",
            "username.max" => "El usuario tiene un máximo 60 caracteres",
            "email.required" => "El correo de usuario es requerido",
            "email.max" => "El correo de usuario tiene un máximo 255 caracteres",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $usuario = Usuario::find($id_usuario);
        if(!$usuario) {
            return response()->json("El usuario no existe.", 400);
        }
        $persona = Persona::find($id_usuario);

        $correoUsuario = Usuario::where("email", $request->email)
            ->where("id_usuario", "!=", $id_usuario)
            ->first();
        if ($correoUsuario) {
            return response()->json('¡Atención! El correo de usuario en esta registrado por otra persona.', 400);
        }

        $numeroDocumentoPersona = Persona::where("numero_documento", $request->numero_documento)
            ->where("id_tipodocumento", $request->id_tipodocumento)
            ->where("id_persona", "!=", $id_usuario)
            ->first();
        if ($numeroDocumentoPersona) {
            return response()->json('¡Atención! El número de documento existe.', 400);
        }

        $usernameUsuario = Usuario::where("username", $request->username)
            ->where("id_usuario", "!=", $id_usuario)
            ->first();
        if($usernameUsuario) {
            return response()->json('¡Atención! El usuario existe.', 400);
        }

        $user = $request->sessionUser;

        $archivo = null;
        if($request->hasFile("file")) {
            if($persona->id_archivo_foto) {
                $archivo = ArchivoController::editarArchivo($request->file, $persona->id_archivo_foto);
            } else {
                $archivo = ArchivoController::registrarArchivo($request->file, $user->id_usuario, "3", null, null, null);
            }
        }

        $personaEdit = [];
        $personaEdit["nombre"] = $request->nombre_completo;
        $personaEdit["nombre_completo"] = $request->nombre_completo;
        $personaEdit["fecha_nacimiento"] = $request->fecha_nacimiento;
        $personaEdit["telefono"] = $request->telefono;
        $personaEdit["correo"] = $request->correo;
        $personaEdit["direccion"] = $request->direccion;
        $personaEdit["sexo"] = $request->sexo;
        $personaEdit["id_tipodocumento"] = $request->id_tipodocumento;
        $personaEdit["numero_documento"] = $request->numero_documento;
        if($archivo) {
            $personaEdit["id_archivo_foto"] = $archivo->id_archivo;
        }
        $persona->update($personaEdit);

        $usuarioEdit = [];
        $usuarioEdit["username"] = $request->username;
        $usuarioEdit["email"] = $request->email;
        $usuarioEdit["estado"] = $request->estado;
        $usuarioEdit["id_usuariomod"] = $user->id_usuario;
        $usuarioEdit["fechamod"] = now();
        $usuario->update($usuarioEdit);

        $result = Persona::find($persona->id_persona);

        return response()->json($result);
    }

    public function destroy($id_usuario)
    {
        $usuario = Usuario::find($id_usuario);
        if(!$usuario) {
            return response()->json("Usuario no encontrado.", 400);
        }
        $persona = Persona::find($id_usuario);
        if(!$persona) {
            return response()->json("Persona no encontrada.", 400);
        }

        $usuario->delete();
        $persona->delete();

        return response()->json([]);
    }

    public function buscarPersona(Request $request)
    {
        $result = DB::table("usuario as a")
            ->rightJoin("persona as b", "a.id_usuario", "b.id_persona")
            ->select(
                "a.id_usuario",
                "a.estado",
                "a.username",
                "a.email",
                "a.correo",
                "b.id_persona",
                "b.nombre_completo",
                "b.telefono",
                "b.correo",
                "b.fecha_nacimiento",
                "b.sexo",
                "b.direccion",
                "b.id_tipodocumento",
                DB::raw("b.numero_documento"),
                DB::raw("date_format(b.fecha_nacimiento, '%Y-%m-%d') as fecha_nacimiento_date")
            )
            ->where("b.id_tipodocumento", $request->id_tipodocumento)
            ->where("b.numero_documento", $request->numero_documento)
            ->first();

        if($result) {
            return response()->json($result);
        } else {
            return response()->json('¡Atención! Persona no encontrada.', 400);
        }
    }

    public function completarPersona(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_persona" => "required",
            "username" => "required|max:60|unique:usuario",
            "email" => "required|string|email|max:255|unique:usuario",
        ], [
            "id_persona.required" => "La persona es requerida",
            "username.required" => "El usuario es requerido",
            "username.max" => "El usuario tiene un máximo 60 caracteres",
            "username.unique" => "El usuario ya existe",
            "email.required" => "El correo de usuario es requerido",
            "email.max" => "El correo de usuario tiene un máximo 255 caracteres",
            "email.unique" => "El correo de usuario ya existe",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }
        $correoUsuario = Usuario::where("email", $request->email)->first();
        if ($correoUsuario) {
            return response()->json('¡Atención! El correo de usuario en esta registrado por otra persona.', 400);
        }
        $usernameUsuario = Usuario::where("username", $request->username)->first();
        if ($usernameUsuario) {
            return response()->json('¡Atención! El usuario existe.', 400);
        }

        $user = $request->sessionUser;

        $usuario = [];
        $usuario["id_usuario"] = $request->id_persona;
        $usuario["username"] = $request->username;
        $usuario["email"] = $request->email;
        $usuario["estado"] = "1";
        $usuario["id_usuarioreg"] = $user->id_usuario;
        $usuario["fechareg"] = now();
        $usuario = Usuario::create($usuario);
        $result = ["id_usuario" => $request->id_persona];

        return response()->json($result);
    }

    public function generarPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_usuario" => "required",
        ], [
            "id_usuario.required" => "El usuario es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }
        $usuario = Usuario::find($request->id_usuario);
        if (!$usuario) {
            return response()->json('¡Atención! Usuario no encontrado.', 400);
        }

        $cadena = self::generarCadena();
        $usuarioEdit = [];
        $usuarioEdit["password"] = Hash::make($cadena);
        $usuario->update($usuarioEdit);
        $siEnvio = self::envioMail($cadena, $usuario->email, "support-bigsei@gmail.com");
        
        if($siEnvio) {
            $mensaje = "Correo enviado. ".$cadena;
        } else {
            $mensaje = "El correo no fue enviado.";
        }

        return response()->json(["mensaje" => $mensaje]);
    }

    private function generarCadena()
    {
        // $cadena = substr(md5(uniqid(rand(), true)), 0, 16);
        $cadena = substr(md5(uniqid(time(), true)), 0, 10);
        return $cadena;
    }

    private function envioMail($cadena, $to, $from)
    {
        Mail::to($to)->send(new UsuarioMailable(["cadena" => $cadena]));

        return true;
    }
}