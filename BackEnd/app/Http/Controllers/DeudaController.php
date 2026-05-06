<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deuda;
use App\Models\Factura;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

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

    //Marcar deuda como pagada
    public function marcarPagada(Request $request, $id)
    {
        $request->validate([
            'idMetodoPago' => 'required|exists:metodo_pago,idMetodoPago'
        ]);

        $deuda = Deuda::find($id);

        if (!$deuda) {
            return response()->json(["message" => "Deuda no encontrada"], 404);
        }

        if ($deuda->estado === 'pagado') {
            return response()->json(["message" => "Ya está pagada"], 400);
        }

        DB::beginTransaction();

        try {

            $importeTotal = $deuda->importe;

            $base = $importeTotal / 1.18;
            $igv = $importeTotal - $base;

            // 1. CREAR PAGO
            $pago = Pago::create([
                'idUsuario' => $deuda->idUsuario,
                'idMetodoPago' => $request->idMetodoPago,
                'descripcion' => $deuda->descripcion,
                'importe' => $base,
                'igv' => $igv,
                'total' => $importeTotal,
                'fechaPago' => now(),
                'conciliado' => 1
            ]);

            // 2. CREAR FACTURA AUTOMÁTICA
            $factura = Factura::create([
                'idPago' => $pago->idPago,
                'numeroFactura' => 'F-' . time(),
                'cliente' => $deuda->usuario->nombres ?? 'Cliente',
                'documento' => '00000000',
                'subtotal' => $base,
                'igv' => $igv,
                'total' => $importeTotal,
                'estado' => 'EMITIDA',
                'fecha' => now()
            ]);

            // 3. ACTUALIZAR DEUDA
            $deuda->estado = "pagado";
            $deuda->save();

            DB::commit();

            return response()->json([
                "message" => "Pago y factura generados correctamente",
                "pago" => $pago,
                "factura" => $factura
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "message" => "Error en pago",
                "error" => $e->getMessage()
            ], 500);
        }
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
