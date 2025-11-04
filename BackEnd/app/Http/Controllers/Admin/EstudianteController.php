<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Persona;
use App\Models\Estudiante;
use App\Http\Controllers\Setup\ArchivoController;

class EstudianteController extends Controller
{
    public function index(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }

        $paginate = DB::table("estudiante as a")
            ->join("persona as b", "a.id_estudiante", "b.id_persona")
            ->select(
                "a.id_estudiante",
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

    public function show($id_estudiante)
    {
        $result = DB::table("estudiante as a")
            ->join("persona as b", "a.id_estudiante", "b.id_persona")
            ->leftJoin("archivo as c", "b.id_archivo_foto", "c.id_archivo")
            ->select(
                "a.id_estudiante",
                "a.estado",
                "b.nombre_completo",
                "b.telefono",
                "b.correo",
                "b.fecha_nacimiento",
                "b.sexo",
                "b.direccion",
                "b.id_tipodocumento",
                "b.numero_documento",
                "a.codigo",
                DB::raw("c.url as archivo_foto_url"),
                DB::raw("date_format(b.fecha_nacimiento, '%Y-%m-%d') as fecha_nacimiento_date")
            )
            ->where("a.id_estudiante", $id_estudiante)
            ->first();

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "nombre_completo" => "required|string|max:450",
            "fecha_nacimiento" => "required|date",
            "telefono" => "required|integer|digits:9",
            "correo" => "required|string|email|max:50|unique:usuario",
            "direccion" => "required|string|max:60",
            "sexo" => "required|string|max:1",
            "estado" => "required|string|max:1",
            "id_tipodocumento" => "required",
            "numero_documento" => "required|max:20",
            "codigo" => "required|max:50",
            "file" => "nullable|mimes:jpeg,bmp,png,webp,avif,jpg,jfif", // 16 MB (16000 kB). maximo 16 MegaBytes
        ], [
            "nombre_completo.required" => "El nombre es requerido",
            "fecha_nacimiento.required" => "La fecha de nacimiento es requerida",
            "telefono.required" => "El teléfono es requerido",
            "telefono.digits" => "El teléfono debe tener 9 dígitos",
            "correo.required" => "El correo es requerido",
            "correo.email" => "El correo no es válido",
            "correo.unique" => "El correo ya existe",
            "direccion.required" => "La dirección es requerida",
            "sexo.required" => "El campo sexo es requerida",
            "estado.required" => "El estado es requerida",
            "id_tipodocumento.required" => "El documento es requerido",
            "numero_documento.required" => "El número de documento es requerido",
            "codigo.required" => "El código es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $numeroDocumentoPersona = Persona::where("numero_documento", $request->numero_documento)
            ->where("id_tipodocumento", $request->id_tipodocumento)->first();
        if ($numeroDocumentoPersona) {
            return response()->json('¡Atención! El número de documento existe.', 400);
        }

        $codigoEstudiante = Estudiante::where("codigo", $request->codigo)
            ->first();
        if ($codigoEstudiante) {
            return response()->json('¡Atención! El código existe.', 400);
        }

        $user = $request->sessionUser;

        $archivo = null;
        if ($request->hasFile("file")) {
            $archivo = ArchivoController::registrarArchivo($request->file, $user->id_usuario, "3", null, null, null);
        }

        $persona = [];
        $persona["nombre_completo"] = $request->nombre_completo;
        $persona["id_tipodocumento"] = $request->id_tipodocumento;
        $persona["numero_documento"] = $request->numero_documento;
        $persona["fecha_nacimiento"] = $request->fecha_nacimiento;
        $persona["telefono"] = $request->telefono;
        $persona["correo"] = $request->correo;
        $persona["direccion"] = $request->direccion;
        $persona["sexo"] = $request->sexo;
        $persona["fechareg"] = now();
        $persona["estado"] = "1";
        if($archivo) {
            $persona["id_archivo_foto"] = $archivo->id_archivo;
        }
        $persona = Persona::create($persona);

        $estudiante = [];
        $estudiante["id_estudiante"] = $persona->id_persona;
        $estudiante["codigo"] = $request->codigo;
        $estudiante["estado"] = $request->estado;
        $estudiante["id_usuarioreg"] = $user->id_usuario;
        $estudiante["fechareg"] = now();
        $estudiante = Estudiante::create($estudiante);

        $result = Persona::find($persona->id_persona);

        return response()->json($result);
    }

    public function update(Request $request, $id_estudiante)
    {
        $validator = Validator::make($request->all(), [
            "nombre_completo" => "required|string|max:450",
            "fecha_nacimiento" => "required|date",
            "telefono" => "required|integer|digits:9",
            "correo" => "required|string|email|max:50|unique:usuario",
            "direccion" => "required|string|max:60",
            "sexo" => "required|string|max:1",
            "estado" => "required|string|max:1",
            "id_tipodocumento" => "required",
            "numero_documento" => "required|max:20",
            "codigo" => "required|max:50",
            "file" => "nullable|mimes:jpeg,bmp,png,webp,avif,jpg,jfif", // 16 MB (16000 kB). maximo 16 MegaBytes
        ], [
            "nombre_completo.required" => "El nombre es requerido",
            "fecha_nacimiento.required" => "La fecha de nacimiento es requerida",
            "telefono.required" => "El teléfono es requerido",
            "telefono.digits" => "El teléfono debe tener 9 dígitos",
            "correo.required" => "El correo es requerido",
            "correo.email" => "El correo no es válido",
            "correo.unique" => "El correo ya existe",
            "direccion.required" => "La dirección es requerida",
            "sexo.required" => "El campo sexo es requerida",
            "estado.required" => "El estado es requerida",
            "id_tipodocumento.required" => "El documento es requerido",
            "numero_documento.required" => "El número de documento es requerido",
            "codigo.required" => "El código es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $numeroDocumentoPersona = Persona::where("numero_documento", $request->numero_documento)
            ->where("id_tipodocumento", $request->id_tipodocumento)
            ->where("id_persona", "!=", $id_estudiante)
            ->first();
        if ($numeroDocumentoPersona) {
            return response()->json('¡Atención! El número de documento existe.', 400);
        }

        $codigoEstudiante = Estudiante::where("codigo", $request->codigo)
            ->where("id_estudiante", "!=", $id_estudiante)
            ->first();
        if ($codigoEstudiante) {
            return response()->json('¡Atención! El código existe.', 400);
        }

        $estudiante = Estudiante::find($id_estudiante);
        if(!$estudiante) {
            return response()->json("El estudiante no existe.", 400);
        }
        $persona = Persona::find($id_estudiante);

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
        $personaEdit["nombre_completo"] = $request->nombre_completo;
        $personaEdit["id_tipodocumento"] = $request->id_tipodocumento;
        $personaEdit["numero_documento"] = $request->numero_documento;
        $personaEdit["fecha_nacimiento"] = $request->fecha_nacimiento;
        $personaEdit["telefono"] = $request->telefono;
        $personaEdit["correo"] = $request->correo;
        $personaEdit["direccion"] = $request->direccion;
        $personaEdit["sexo"] = $request->sexo;
        if($archivo) {
            $personaEdit["id_archivo_foto"] = $archivo->id_archivo;
        }
        $persona->update($personaEdit);

        $estudianteEdit = [];
        $estudianteEdit["codigo"] = $request->codigo;
        $estudianteEdit["estado"] = $request->estado;
        $estudiante->update($estudianteEdit);

        $result = Persona::find($persona->id_persona);

        return response()->json($result);
    }

    public function destroy($id_estudiante)
    {
        $estudiante = Estudiante::find($id_estudiante);
        if(!$estudiante) {
            return response()->json("Estudiante no encontrado.", 400);
        }
        $persona = Persona::find($id_estudiante);
        if(!$persona) {
            return response()->json("Persona no encontrada.", 400);
        }

        $estudiante->delete();
        $persona->delete();

        return response()->json([]);
    }
}