<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Archivo;

class ArchivoController extends Controller
{
    public function index(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }
        $archivos = DB::table("archivo")
            ->select(
                "id_archivo",
                "nombre",
                "extension"
            );

        if(isset($request->id_periodotarea)){
            $archivos->where("id_periodotarea", $request->id_periodotarea);
        }
        if(isset($request->id_periodotema)){
            $archivos->where("id_periodotema", $request->id_periodotema);
        }
        $archivos->orderBy("id_archivo", "asc");
        $archivos = $archivos->paginate($per_page);

        return response()->json($archivos);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id_periodotarea" => "nullable",
            "id_periodotema" => "nullable",
            "tipo" => "required|max:1",
            // "file" => "required|mimes:jpeg,bmp,png|size:16000", // 16 MB (16000 kB). maximo 16 MegaBytes
            "file" => "required|mimes:jpeg,bmp,png,doc,docx,xls,xlsx,pdf,pptx,ppt", // 16 MB (16000 kB). maximo 16 MegaBytes
        ], [
            "tipo.required" => "El tipo es requerido",
            "tipo.max" => "El tipo tiene un máximo 1 caracter",
            "file.required" => "El archivo es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        if (!$request->hasFile("file")) {
            return response()->json("¡Atención! Archivo no encontrado.", 400);
        }

        $user = $request->sessionUser;
        $name = $request->file('file')->getClientOriginalName();
        $path = $request->file("file")->getRealPath();
        $ext = $request->file->extension();
        $doc = file_get_contents($path);
        $base64 = base64_encode($doc);

        $archivo = [];
        $archivo["nombre"] = $name;
        $archivo["url"] = $base64;
        $archivo["extension"] = $ext;
        $archivo["tipo"] = $request->tipo;
        $archivo["id_periodotarea"] = $request->id_periodotarea;
        $archivo["id_periodotema"] = $request->id_periodotema;
        $archivo["id_usuarioreg"] = $user->id_usuario;
        $archivo["fechareg"] = now();
        $archivo = Archivo::create($archivo);

        return response()->json([]);
    }

    public function destroy($id_archivo)
    {
        $archivo = Archivo::find($id_archivo);
        if (!$archivo) {
            return response()->json("¡Atención! Archivo no encontrado.", 400);
        }

        $archivo->delete();

        return response()->json([]);
    }

    public static function registrarVideo($file, $id_usuario, $tipo, $id_periodotarea = null, $id_periodotema = null, $id_persona = null)
    {
        $name = $file->getClientOriginalName();
        $path = $file->getRealPath();
        $ext = $file->extension();
        $doc = file_get_contents($path);
        $base64 = base64_encode($doc);

        $archivo = [];
        $archivo["nombre"] = $name;
        $archivo["url"] = $base64;
        $archivo["extension"] = $ext;
        $archivo["tipo"] = $tipo;
        $archivo["id_periodotarea"] = $id_periodotarea;
        $archivo["id_periodotema"] = $id_periodotema;
        $archivo["id_persona"] = $id_persona;
        $archivo["id_usuarioreg"] = $id_usuario;
        $archivo["fechareg"] = now();
        $archivo = Archivo::create($archivo);

        return $archivo;
    }

    public static function editarVideo($file, $id_archivo)
    {
        $name = $file->getClientOriginalName();
        $path = $file->getRealPath();
        $ext = $file->extension();
        $doc = file_get_contents($path);
        $base64 = base64_encode($doc);

        $archivo = Archivo::find($id_archivo);
        $archivoEdit = [];
        $archivoEdit["nombre"] = $name;
        $archivoEdit["url"] = $base64;
        $archivoEdit["extension"] = $ext;
        $archivo->update($archivoEdit);
    }
}