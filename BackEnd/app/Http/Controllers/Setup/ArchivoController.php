<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\Archivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                "extension",
                "tamanho"
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

    public function visualizar($id_archivo)
    {
        $archivo = Archivo::find($id_archivo);
        if($archivo) {
            $result = [
                "url" => $archivo->url,
                "extension" => $archivo->extension
            ];
        } else {
            $result = [
                "url" => null,
                "extension" => null
            ];
        }

        return response()->json($result);
    }

    public function descargar($id_archivo)
    {
        $archivo = Archivo::select("url", "tamanho", "nombre", "extension")->find($id_archivo);

        return response()->json($archivo);
    }

    public function imagen($id_archivo)
    {
        $archivo = Archivo::find($id_archivo);
        if($archivo) {
            $url = $archivo->url;
        } else {
            $url = "";
        }

        $image = base64_decode($url);
        return response($image)->header('Content-Type', 'image/png');
    }

    /**
     * Método público para visualizar imágenes sin autenticación
     * Usado para mostrar imágenes de cursos en el catálogo público
     */
    public function imagenPublica($id_archivo)
    {
        $archivo = Archivo::find($id_archivo);
        if(!$archivo) {
            return response()->json(['error' => 'Archivo no encontrado'], 404);
        }
        
        // Determinar el tipo de contenido basado en la extensión
        $contentType = 'image/png';
        $extension = strtolower($archivo->extension ?? 'png');
        switch($extension) {
            case 'jpg':
            case 'jpeg':
                $contentType = 'image/jpeg';
                break;
            case 'gif':
                $contentType = 'image/gif';
                break;
            case 'webp':
                $contentType = 'image/webp';
                break;
            case 'svg':
                $contentType = 'image/svg+xml';
                break;
        }

        $image = base64_decode($archivo->url);
        return response($image)->header('Content-Type', $contentType);
    }

    public static function registrarArchivo($file, $id_usuario, $tipo, $id_periodotarea = null, $id_periodotema = null, $id_persona = null)
    {
        $name = $file->getClientOriginalName();
        $path = $file->getRealPath();
        $ext = $file->getClientOriginalExtension();
        $doc = file_get_contents($path);
        $base64 = base64_encode($doc);
        $tamanho = filesize($path);

        $archivo = [];
        $archivo["nombre"] = $name;
        $archivo["url"] = $base64;
        $archivo["extension"] = $ext;
        $archivo["tipo"] = $tipo;
        $archivo["tamanho"] = $tamanho;
        $archivo["id_periodotarea"] = $id_periodotarea;
        $archivo["id_periodotema"] = $id_periodotema;
        $archivo["id_persona"] = $id_persona;
        $archivo["id_usuarioreg"] = $id_usuario;
        $archivo["fechareg"] = now();
        $archivo = Archivo::create($archivo);

        return $archivo;
    }

    public static function editarArchivo($file, $id_archivo)
    {
        $name = $file->getClientOriginalName();
        $path = $file->getRealPath();
        $ext = $file->getClientOriginalExtension();
        $doc = file_get_contents($path);
        $base64 = base64_encode($doc);
        $tamanho = filesize($path);

        $archivo = Archivo::find($id_archivo);
        $archivoEdit = [];
        $archivoEdit["nombre"] = $name;
        $archivoEdit["url"] = $base64;
        $archivoEdit["extension"] = $ext;
        $archivoEdit["tamanho"] = $tamanho;
        $archivo->update($archivoEdit);

        return $archivo;
    }
}