<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deuda;

class DeudaController extends Controller
{
    //Listar deudas
    public function ListarDeuda()
    {
        try {
            $deudas = Deuda::with(['usuario' => function ($query) {
                $query->select('id_usuario', 'nombres', 'apellidoPaterno', 'apellidoMaterno');
            }])->get();

            return response()->json($deudas, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener deudas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //Registrar nueva deuda
    public function RegistrarDeuda(Request $request)
    {
        $request->validate([
            'idUsuario' => 'required|integer',
            'descripcion' => 'required|string|max:255',
            'importe' => 'required|numeric',
            'fecha_a_pagar' => 'required|date',
        ]);

        $deuda = Deuda::create([
            'idUsuario' => $request->idUsuario,
            'descripcion' => $request->descripcion,
            'importe' => $request->importe,
            'fecha_a_pagar' => $request->fecha_a_pagar,
            'estado' => $request->estado ?? 'Pendiente',
            'observacion' => $request->observacion
        ]);

        return response()->json([
            "message" => "Deuda registrada correctamente",
            "data" => $deuda
        ]);
    }

    //Mostrar una deuda especifica
    public function MostrarDeuda($id)
    {
        try {
            $deuda = Deuda::with(['usuario' => function ($query) {
                $query->select('id_usuario', 'nombres', 'apellidoPaterno', 'apellidoMaterno');
            }])->find($id);

            if (!$deuda) {
                return response()->json([
                    'message' => 'Deuda no encontrada'
                ], 404);
            }

            return response()->json($deuda, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener la deuda',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //Actualizar deuda
    public function ActualizarDeuda(Request $request, $id)
    {
        $deuda = Deuda::find($id);

        if (!$deuda) {
            return response()->json([
                "message" => "Deuda no encontrada"
            ], 404);
        }

        $deuda->update($request->all());

        return response()->json([
            "message" => "Deuda actualizada correctamente",
            "data" => $deuda
        ]);
    }

    //Eliminar deduda
    public function EliminarDeuda($id)
    {
        $deuda = Deuda::find($id);

        if (!$deuda) {
            return response()->json([
                "message" => "Deuda no encontrada"
            ], 404);
        }

        $deuda->delete();

        return response()->json([
            "message" => "Deuda eliminada correctamente"
        ]);
    }
}