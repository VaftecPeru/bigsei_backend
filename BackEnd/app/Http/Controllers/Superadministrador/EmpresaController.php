<?php

namespace App\Http\Controllers\Superadministrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Empresa;
use App\Models\Archivo;
use App\Http\Controllers\Setup\ArchivoController;

class EmpresaController extends Controller
{
    public function index(Request $request)
    {
        $result = DB::table('empresa as a')
            ->leftJoin("archivo as b", "a.id_archivo", "b.id_archivo")
            ->select(
                'a.id_empresa',
                'a.razon_social',
                'a.direccion',
                DB::raw("b.nombre as archivo_nombre"),
                DB::raw("b.url as archivo_url"),
                DB::raw("b.extension as archivo_extension"),
                DB::raw("'Lunes - Viernes 7:00 - 18:00' as empresa_horario"),
                DB::raw("'' as locacion")
            );
        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(a.razon_social, a.direccion)) LIKE upper( ? )", ['%'.$texto.'%']);
        }
        $result->orderBy("razon_social", "asc");
        $result = $result->get();

        return response()->json($result);
    }

    public function show($id_empresa)
    {
        $result = DB::table("empresa as a")
            ->leftJoin("archivo as b", "a.id_archivo", "b.id_archivo")
            ->select(
                "a.id_empresa",
                "a.id_tipodocumento",
                "a.numero_documento",
                "a.razon_social",
                "a.condicion_sunat",
                "a.estado_contribuyente",
                "a.tipo_relacion",
                "a.id_vendedor",
                "a.correo",
                "a.contacto",
                "a.direccion_fiscal",
                "a.telefono",
                "a.departamento",
                "a.provincia",
                "a.distrito",
                "a.direccion",
                "a.atencion_desde",
                "a.atencion_hasta",
                "a.atencion_dias",
                "a.url_maps",
                "a.id_archivo",
                DB::raw("b.nombre as archivo_nombre"),
                DB::raw("b.url as archivo_url"),
                DB::raw("b.extension as archivo_extension")
            )
            ->where("a.id_empresa", $id_empresa)
            ->first();

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_tipodocumento' => 'required',
            'numero_documento' => 'required|string|max:50',
            'razon_social' => 'required|string|max:255',
            'condicion_sunat' => 'required|string|max:2',
            'estado_contribuyente' => 'required|string|max:1',
            'tipo_relacion' => 'required|string|max:1',
            'id_vendedor' => 'required',
            'correo' => 'required|string|max:255',
            'contacto' => 'required|string|max:255',
            'direccion_fiscal' => 'required|string|max:255',
            'telefono' => 'required|string|max:50',
            'departamento' => 'required|string|max:255',
            'provincia' => 'required|string|max:255',
            'distrito' => 'required|string|max:255',
            'direccion' => 'required|string|max:255'
        ], [
            'id_tipodocumento.required' => 'El tipo documento es requerido',
            'numero_documento.required' => 'El nùmero de documento es requerido',
            'razon_social.required' => 'La razòn social es requerido',
            'condicion_sunat.required' => 'La condiciòn sunat es requerido',
            'estado_contribuyente.required' => 'El estado del contribuyente es requerido',
            'tipo_relacion.required' => 'El tipo relaciòn es requerido',
            'id_vendedor.required' => 'El vendedor es requerido',
            'correo.required' => 'El correo es requerido',
            'contacto.required' => 'El contacto es requerido',
            'direccion_fiscal.required' => 'La direcciòn fiscal es requerido',
            'telefono.required' => 'La telèfono fiscal es requerido',
            'departamento.required' => 'La departamento es requerido',
            'provincia.required' => 'La provincia es requerido',
            'distrito.required' => 'La distrito es requerido',
            'direccion.required' => 'La direcciòn es requerido',
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $empresa = [];
        $empresa["id_tipodocumento"] = $request->id_tipodocumento;
        $empresa["numero_documento"] = $request->numero_documento;
        $empresa["razon_social"] = $request->razon_social;
        $empresa["condicion_sunat"] = $request->condicion_sunat;
        $empresa["estado_contribuyente"] = $request->estado_contribuyente;
        $empresa["tipo_relacion"] = $request->tipo_relacion;
        $empresa["id_vendedor"] = $request->id_vendedor;
        $empresa["correo"] = $request->correo;
        $empresa["contacto"] = $request->contacto;
        $empresa["direccion_fiscal"] = $request->direccion_fiscal;
        $empresa["telefono"] = $request->telefono;
        $empresa["departamento"] = $request->departamento;
        $empresa["provincia"] = $request->provincia;
        $empresa["distrito"] = $request->distrito;
        $empresa["direccion"] = $request->direccion;
        $empresa = Empresa::create($empresa);

        $result = Empresa::find($empresa->id_empresa);

        return response()->json($result);
    }

    public function update(Request $request, $id_empresa)
    {
        $validator = Validator::make($request->all(), [
            'razon_social' => 'required|string|max:255',
            'atencion_desde' => 'required',
            'atencion_hasta' => 'required',
            'atencion_dias' => 'required|string|max:50',
            'url_maps' => 'nullable|string|max:255',
        ], [
            'razon_social.required' => 'La razòn social es requerido',
            'atencion_desde.required' => 'El horario de atención desde es requerido',
            'atencion_hasta.required' => 'El horario de atención hasta es requerido',
            'atencion_dias.required' => 'El horario de atención dìas es requerido',
            // 'url_maps.required' => 'El enlace de maps es requerido',
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $empresa = Empresa::find($id_empresa);

        if(!$empresa) {
            return response()->json("La empresa no existe.", 400);
        }

        $empresaEdit = [];
        $empresaEdit["razon_social"] = $request->razon_social;
        $empresaEdit["atencion_desde"] = $request->atencion_desde;
        $empresaEdit["atencion_hasta"] = $request->atencion_hasta;
        $empresaEdit["atencion_dias"] = $request->atencion_dias;
        $empresaEdit["url_maps"] = $request->url_maps;
        $empresa->update($empresaEdit);

        $result = Empresa::find($id_empresa);

        return response()->json($result);
    }

    public function storeArchivo(Request $request, $id_empresa)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:jpeg,bmp,png,webp,avif,jpg,jfif',
        ], [
            'file.required' => 'El archivo es requerido',
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        if (!$request->hasFile("file")) {
            return response()->json("¡Atención! Archivo no encontrado.", 400);
        }

        $user = $request->sessionUser;

        $empresa = Empresa::find($id_empresa);
        if(!$empresa) {
            return response()->json("¡Atención! Empresa no encontrado.", 400);
        } else if($empresa->id_archivo) {
            ArchivoController::editarArchivo($request->file('file'), $empresa->id_archivo);
        } else {
            $archivo = ArchivoController::registrarArchivo($request->file('file'), $user->id_usuario, 3, null, null, null);
            $empresa->update(["id_archivo" => $archivo->id_archivo]);
        }

        $archivo = Archivo::find($empresa->id_archivo);

        return response()->json($archivo);
    }

    public function destroy($id_empresa)
    {
        $empresa = Empresa::find($id_empresa);
        if(!$empresa) {
            return response()->json("Estudiante no encontrado.", 400);
        }

        $empresa->delete();

        return response()->json([]);
    }
}