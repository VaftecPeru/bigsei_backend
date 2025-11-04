<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Persona;
use App\Models\Docente;
use App\Http\Controllers\Setup\ArchivoController;

class DocenteController extends Controller
{
    public function index(Request $request)
    {
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        } else {
            $per_page = 15;
        }

        $paginate = DB::table("docente as a")
            ->join("persona as b", "a.id_docente", "b.id_persona")
            ->select(
                "a.id_docente",
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

    public function show($id_docente)
    {
        $result = DB::table("docente as a")
            ->join("persona as b", "a.id_docente", "b.id_persona")
            ->leftJoin("archivo as c", "b.id_archivo_foto", "c.id_archivo")
            ->select(
                "a.id_docente",
                "a.estado",
                "a.codigo",
                "b.nombre_completo",
                "b.telefono",
                "b.correo",
                "b.fecha_nacimiento",
                "b.sexo",
                "b.direccion",
                "b.id_tipodocumento",
                "b.numero_documento",
                "a.id_tipoespecializacion",
                "a.anhos_de_experiencia",
                "a.id_tiponiveleducativo",
                DB::raw("c.url as archivo_foto_url"),
                DB::raw("date_format(b.fecha_nacimiento, '%Y-%m-%d') as fecha_nacimiento_date")
            )
            ->where('a.id_docente', $id_docente)
            ->first();

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_completo' => 'required|string|max:450',
            'fecha_nacimiento' => 'required|date',
            'telefono' => 'required|integer|digits:9',
            'correo' => 'required|string|email|max:50|unique:usuario',
            'direccion' => 'required|string|max:60',
            'sexo' => 'required|string|max:1',
            'estado' => 'required|string|max:1',
            'id_tipoespecializacion' => 'required',
            'anhos_de_experiencia' => 'required',
            'id_tiponiveleducativo' => 'required',
            "id_tipodocumento" => "required",
            "numero_documento" => "required|max:20",
            "codigo" => "required|max:50",
            "file" => "nullable|mimes:jpeg,bmp,png,webp,avif,jpg,jfif", // 16 MB (16000 kB). maximo 16 MegaBytes
        ], [
            'nombre_completo.required' => 'El nombre es requerido',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es requerida',
            'telefono.required' => 'El teléfono es requerido',
            'telefono.digits' => 'El teléfono debe tener 9 dígitos',
            'correo.required' => 'El correo es requerido',
            'correo.email' => 'El correo no es válido',
            'correo.unique' => 'El correo ya existe',
            'direccion.required' => 'La dirección es requerida',
            'sexo.required' => 'El campo sexo es requerida',
            'estado.required' => 'El estado es requerida',
            'id_tipoespecializacion.required' => 'La especializaciòn es requerida',
            'anhos_de_experiencia.required' => 'Los años de experiencia es requerido',
            'id_tiponiveleducativo.required' => 'El nivel educativo es requerido',
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

        $codigoDocente = Docente::where("codigo", $request->codigo)
            ->first();
        if ($codigoDocente) {
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
        $persona["estado"] = '1';
        if($archivo) {
            $persona["id_archivo_foto"] = $archivo->id_archivo;
        }
        $persona = Persona::create($persona);

        $docente = [];
        $docente["id_docente"] = $persona->id_persona;
        $docente["codigo"] = $request->codigo;
        $docente["estado"] = $request->estado;
        $docente["id_usuarioreg"] = $user->id_usuario;
        $docente["fechareg"] = now();
        $docente['id_tipoespecializacion'] = $request->id_tipoespecializacion;
        $docente['anhos_de_experiencia'] = $request->anhos_de_experiencia;
        $docente['id_tiponiveleducativo'] = $request->id_tiponiveleducativo;
        $docente = Docente::create($docente);

        $result = Persona::find($persona->id_docente);

        return response()->json($result);
    }

    public function update(Request $request, $id_docente)
    {
        $validator = Validator::make($request->all(), [
            'nombre_completo' => 'required|string|max:450',
            'fecha_nacimiento' => 'required|date',
            'telefono' => 'required|integer|digits:9',
            'correo' => 'required|string|email|max:50|unique:usuario',
            'direccion' => 'required|string|max:60',
            'sexo' => 'required|string|max:1',
            'estado' => 'required|string|max:1',
            'id_tipoespecializacion' => 'required',
            'anhos_de_experiencia' => 'required',
            'id_tiponiveleducativo' => 'required',
            "id_tipodocumento" => "required",
            "numero_documento" => "required|max:20",
            "codigo" => "required|max:50",
            "file" => "nullable|mimes:jpeg,bmp,png,webp,avif,jpg,jfif", // 16 MB (16000 kB). maximo 16 MegaBytes
        ], [
            'nombre_completo.required' => 'El nombre es requerido',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es requerida',
            'telefono.required' => 'El teléfono es requerido',
            'telefono.digits' => 'El teléfono debe tener 9 dígitos',
            'correo.required' => 'El correo es requerido',
            'correo.email' => 'El correo no es válido',
            'correo.unique' => 'El correo ya existe',
            'direccion.required' => 'La dirección es requerida',
            'sexo.required' => 'El campo sexo es requerida',
            'estado.required' => 'El estado es requerida',
            'id_tipoespecializacion.required' => 'La especializaciòn es requerida',
            'anhos_de_experiencia.required' => 'Los años de experiencia es requerido',
            'id_tiponiveleducativo.required' => 'El nivel educativo es requerido',
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
            ->where("id_persona", "!=", $id_docente)
            ->first();
        if ($numeroDocumentoPersona) {
            return response()->json('¡Atención! El número de documento existe.', 400);
        }

        $codigoDocente = Docente::where("codigo", $request->codigo)
            ->where("id_docente", "!=", $id_docente)
            ->first();
        if ($codigoDocente) {
            return response()->json('¡Atención! El código existe.', 400);
        }

        $docente = Docente::find($id_docente);
        if(!$docente) {
            return response()->json("El estudiante no existe.", 400);
        }
        $persona = Persona::find($id_docente);

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

        $docenteEdit = [];
        $docenteEdit["codigo"] = $request->codigo;
        $docenteEdit["estado"] = $request->estado;
        $docenteEdit["id_tipoespecializacion"] = $request->id_tipoespecializacion;
        $docenteEdit["anhos_de_experiencia"] = $request->anhos_de_experiencia;
        $docenteEdit["id_tiponiveleducativo"] = $request->id_tiponiveleducativo;
        $docente->update($docenteEdit);

        $result = Persona::find($persona->id_persona);

        return response()->json($result);
    }

    public function destroy($id_docente)
    {
        $docente = Docente::find($id_docente);
        if(!$docente) {
            return response()->json("Docente no encontrado.", 400);
        }
        $persona = Persona::find($id_docente);
        if(!$persona) {
            return response()->json("Persona no encontrada.", 400);
        }

        $docente->delete();
        $persona->delete();

        return response()->json([]);
    }
}