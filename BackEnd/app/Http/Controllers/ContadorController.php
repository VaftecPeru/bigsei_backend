<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movimiento;
use App\Models\Pago;
use Barryvdh\DomPDF\PDF;
use App\Models\Deuda;

class ContadorController extends Controller
{
    public function reporteIngresosEgresos(Request $request)
    {
        $validated = $request->validate([
            'id_anho' => 'required|integer',
            'tipo' => 'required|string|in:I,E', // "I" para ingresos, "E" para egresos
        ]);

        try {
            // Obtener los movimientos filtrados por el año y el tipo
            $movimientos = Movimiento::whereYear('fecha', $validated['id_anho'])
                ->where('tipo', $validated['tipo'])
                ->select(
                    'id_mes',
                    'mes_nombre',
                    'fecha',
                    'monto',
                    'metodopago_descripcion',
                    'tipo',
                    'usuario_nombre',
                    'rol_nombre',
                    'descripcion'
                )
                ->orderBy('fecha', 'asc') // Ordenar por fecha
                ->get();

            // Retornar la respuesta en formato JSON
            return response()->json([
                'success' => true,
                'data' => $movimientos,
            ], 200);
        } catch (\Exception $e) {
            // Manejo de errores y devolver una respuesta con el error
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el reporte.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function obtenerPagos(Request $request)
    {
        // Validar parámetros de entrada
        $request->validate([
            'id_anho' => 'required|integer',
            'texto_buscar' => 'nullable|string|max:255',
        ]);

        // Obtener parámetros de entrada
        $id_anho = $request->input('id_anho');
        $texto_buscar = $request->input('texto_buscar');

        // Construir la consulta
        $query = Pago::with(['usuario', 'grado.nivel'])
            ->whereYear('fechaPago', $id_anho); // Filtrar por año de pago

        // Aplicar filtro de búsqueda si se proporciona
        if (!empty($texto_buscar)) {
            $query->whereHas('usuario', function ($q) use ($texto_buscar) {
                $q->where('nombre', 'like', '%' . $texto_buscar . '%');
            });
        }

        // Obtener los resultados
        $pagos = $query->get()->map(function ($pago) {
            return [
                'estudiante_nombre' => $pago->usuario->nombre,
                'nivel_nombre' => $pago->grado->nivel->nombre ?? null,
                'grado_nombre' => $pago->grado->nombre ?? null,
                'tipo' => $pago->descripcion,
                'monto' => $pago->importe,
                'fecha' => $pago->fechaPago,
                'pago_estado' => $pago->total > 0 ? 'Pagado' : 'Pendiente',
            ];
        });

        // Devolver la respuesta en formato JSON
        return response()->json([
            'data' => $pagos,
        ]);
    }

    // Método para obtener deudas pendientes
    public function obtenerDeudasPendientes(Request $request)
    {
        $request->validate([
            'id_anho' => 'required|integer',
            'texto_buscar' => 'nullable|string',
        ]);

        $idAnho = $request->input('id_anho');
        $textoBuscar = $request->input('texto_buscar');

        // Filtrar las deudas pendientes
        $deudas = Deuda::where('estado', 'pendiente')
            ->whereYear('fecha_a_pagar', $idAnho)
            ->when($textoBuscar, function ($query, $textoBuscar) {
                return $query->where(function ($q) use ($textoBuscar) {
                    $q->where('descripcion', 'like', "%$textoBuscar%")
                        ->orWhereHas('usuario', function ($q) use ($textoBuscar) {
                            $q->where('nombre', 'like', "%$textoBuscar%");
                        });
                });
            })
            ->with('usuario:id,nombre')
            ->select('descripcion', 'importe', 'fecha_a_pagar', 'estado', 'observacion')
            ->get();

        // Si el cliente acepta PDF (puedes cambiar esta condición según tus necesidades)
        if ($request->wantsJson()) {
            return response()->json([
                'data' => $deudas,
            ], 200);
        } else {
            $pdf = app(PDF::class)->loadView('exports.deudas-pendientes', [
                'deudas' => $deudas,
                'anho' => $idAnho,
                'textoBuscar' => $textoBuscar
            ]);

            // Descargar el PDF con un nombre específico
            return $pdf->download('deudas-pendientes-' . $idAnho . '.pdf');
        }
    }
}
