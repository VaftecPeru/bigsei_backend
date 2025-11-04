<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Solicitud;

class SolicitudController extends Controller
{
    public function storeContacto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "nombre" => "required|max:150",
            "telefono" => "required|max:50",
            "correo" => "required|max:255",
        ], [
            "nombre" => "El nombre es requerido",
            "telefono" => "El teléfono es requerido",
            "correo" => "El correo es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $solicitud = [];
        $solicitud["nombre"] = $request->nombre;
        $solicitud["telefono"] = $request->telefono;
        $solicitud["correo"] = $request->correo;
        $solicitud["tipo"] = "2";
        $solicitud = Solicitud::create($solicitud);

        $solicitud = Solicitud::find($solicitud->id_solicitud);

        return response()->json($solicitud);
    }

    public function storeEmpresa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "nombre" => "required|max:150",
            "apellido" => "required|max:150",
            "telefono" => "required|max:50",
            "correo" => "required|max:255",
            "razon_social" => "required|max:255",
            "numero_trabajadores" => "required|numeric|min:0|not_in:0",
            "pais" => "required|max:255",
            "cargo" => "required|max:255",
        ], [
            "nombre" => "El nombre es requerido",
            "apellido" => "El apellido es requerido",
            "telefono" => "El teléfono es requerido",
            "correo" => "El correo es requerido",
            "razon_social" => "La razón social es requerida",
            "numero_trabajadores" => "El número de trabajadores es requerido",
            "pais" => "El país es requerido",
            "cargo" => "El cargo es requerido",
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $solicitud = [];
        $solicitud["nombre"] = $request->nombre;
        $solicitud["apellido"] = $request->apellido;
        $solicitud["telefono"] = $request->telefono;
        $solicitud["correo"] = $request->correo;
        $solicitud["razon_social"] = $request->razon_social;
        $solicitud["numero_trabajadores"] = $request->numero_trabajadores;
        $solicitud["pais"] = $request->pais;
        $solicitud["cargo"] = $request->cargo;
        $solicitud["tipo"] = "1";
        $solicitud = Solicitud::create($solicitud);

        $solicitud = Solicitud::find($solicitud->id_solicitud);

        return response()->json($solicitud);
    }
}