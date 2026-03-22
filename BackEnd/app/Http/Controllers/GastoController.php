<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gasto;
use Barryvdh\DomPDF\PDF;

class GastoController extends Controller
{
    // Obtener todos los gastos
    public function index()
    {
        try {
            $gastos = Gasto::with('usuario')->orderBy('fecha_registro', 'desc')->get();
            return response()->json([
                'success' => true,
                'data' => $gastos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los gastos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Obtener un gasto por ID
    public function show($id_gasto)
    {
        try {
            $gasto = Gasto::with('usuario')->findOrFail($id_gasto);
            return response()->json([
                'success' => true,
                'data' => $gasto
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gasto no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    // Crear un nuevo gasto
    public function store(Request $request)
    {
        $request->validate([
            'nro_operacion' => 'required|string|max:50',
            'nombre_destinatario' => 'required|string|max:255',
            'id_usuario' => 'required|integer|exists:usuarios,id',
            'monto' => 'required|numeric|min:0',
            'fecha_registro' => 'required|date',
            'fecha_pago' => 'nullable|date',
            'estado_pago' => 'required|string|in:Pendiente,Pagado',
            'estado_sunat' => 'nullable|string|in:No Enviado,Enviado',
            'nota' => 'nullable|string|max:500',
        ]);

        try {
            $gasto = Gasto::create($request->all());
            return response()->json([
                'success' => true,
                'data' => $gasto
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el gasto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Actualizar un gasto
    public function update(Request $request, $id_gasto)
    {
        try {
            $gasto = Gasto::findOrFail($id_gasto);
            $gasto->update($request->all());
            return response()->json([
                'success' => true,
                'data' => $gasto
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el gasto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Eliminar un gasto
    public function destroy($id_gasto)
    {
        try {
            $gasto = Gasto::findOrFail($id_gasto);
            $gasto->delete();
            return response()->json([
                'success' => true,
                'message' => 'Gasto eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el gasto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Reporte de gastos por rango de fechas
    public function reportePorFecha(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $gastos = Gasto::with('usuario')
            ->whereBetween('fecha_registro', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_registro', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $gastos
        ], 200);
    }

    // Descargar PDF de gastos
    public function descargarPdfGastos(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $gastos = Gasto::with('usuario')
            ->whereBetween('fecha_registro', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_registro', 'asc')
            ->get();

        $pdf = app(PDF::class)->loadView('exports.gastos', [
            'gastos' => $gastos,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin
        ]);

        return $pdf->download('gastos-' . $fechaInicio . '-a-' . $fechaFin . '.pdf');
    }
}