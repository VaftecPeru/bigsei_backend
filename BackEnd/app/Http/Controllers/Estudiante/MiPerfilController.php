<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Persona;
use App\Models\Archivo;
use App\Http\Controllers\Setup\ArchivoController;

class MiPerfilController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->sessionUser;

        $estudiante = DB::table("estudiante as a")
            ->join("persona as b", "a.id_estudiante", "b.id_persona")
            ->select(
                "a.id_estudiante",
                "b.nombre_completo",
                DB::raw("b.numero_documento"),
                "b.telefono",
                "b.correo",
                "b.id_archivo_foto",
                "b.id_archivo_baner",
                DB::raw("date_format(b.fecha_nacimiento, '%d / %m / %Y') as fecha_nacimiento"),
                DB::raw("case when b.sexo = 1 then 'Masculino' else 'Femenito' end as sexo_ff")
            )
            ->where("a.id_estudiante", $user->id_usuario)
            ->first();

        return response()->json($estudiante);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "telefono" => "required|max:20",
            "correo" => "required|max:255",
        ], [
            "telefono.required" => "El teléfono es requerido",
            "telefono.max" => "El teléfono tiene un máximo 20 caracteres",
            "correo.required" => "El correo es requerido",
            "correo.max" => "El correo tiene un máximo 255 caracteres",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $user = $request->sessionUser;
        $persona = Persona::find($user->id_usuario);
        if(!$persona) {
            return response()->json("¡Atención! Persona no encontrada.", 400);
        }

        $personaEdit = [];
        $personaEdit["telefono"] = $request->telefono;
        $personaEdit["correo"] = $request->correo;
        $persona->update($personaEdit);

        $persona = Persona::find($user->id_usuario);

        return response()->json($persona);
    }

    public function storeFoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "file" => "required|mimes:jpeg,bmp,png,webp,avif,jpg,jfif", // 16 MB (16000 kB). maximo 16 MegaBytes
        ], [
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
        $persona = Persona::find($user->id_usuario);

        if($persona->id_archivo_foto) {
            ArchivoController::editarArchivo($request->file('file'), $persona->id_archivo_foto);
        } else {
            $archivo = ArchivoController::registrarArchivo($request->file('file'), $user->id_usuario, 3, null, null, $user->id_usuario);
            $persona->update(["id_archivo_foto" => $archivo->id_archivo]);
        }

        $archivo = Archivo::find($persona->id_archivo_foto);

        return response()->json($archivo);
    }

    public function storeBaner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "file" => "required|mimes:jpeg,bmp,png", // 16 MB (16000 kB). maximo 16 MegaBytes
        ], [
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
        $persona = Persona::find($user->id_usuario);

        if($persona->id_archivo_baner) {
            ArchivoController::editarArchivo($request->file('file'), $persona->id_archivo_baner);
        } else {
            $archivo = ArchivoController::registrarArchivo($request->file('file'), $user->id_usuario, 3, null, null, $user->id_usuario);
            $persona->update(["id_archivo_baner" => $archivo->id_archivo]);
        }

        $archivo = Archivo::find($persona->id_archivo_baner);

        return response()->json($archivo);
    }
}