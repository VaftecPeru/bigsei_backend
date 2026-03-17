<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movimiento;
use App\Models\Pago;
use Barryvdh\DomPDF\PDF;
use App\Models\Usuario;
use App\Models\Deuda;

class ContadorController extends Controller
{
    public function reporteIngresosEgresos(Request $request)
    {
        $validated = $request->validate([
            'id_anho' => 'required|integer',
            'tipo' => 'required|string|in:I,E',
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
                ->orderBy('fecha', 'asc') 
                ->get();

            return response()->json([
                'success' => true,
                'data' => $movimientos,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el reporte.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function listarUsuarios()
    {
        try {
            $usuarios = Usuario::select(
                'id_usuario',
                'nombres',
                'apellidoPaterno',
                'apellidoMaterno',
                'correo'
            )
            ->where('estado', 1)
            ->get();

            return response()->json($usuarios);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudieron obtener los usuarios',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerPagos(Request $request)
    {
        $request->validate([
            'id_anho' => 'required|integer',
            'texto_buscar' => 'nullable|string|max:255',
        ]);

        $id_anho = $request->input('id_anho');
        $texto_buscar = $request->input('texto_buscar');

        $query = Pago::with(['usuario', 'grado.nivel'])
            ->whereYear('fechaPago', $id_anho); 

        if (!empty($texto_buscar)) {
            $query->whereHas('usuario', function ($q) use ($texto_buscar) {
                $q->where('nombre', 'like', '%' . $texto_buscar . '%');
            });
        }

        $pagos = $query->get()->map(function ($pago) {

            return [
                'estudiante_nombre' => optional($pago->usuario)->nombre,
                'nivel_nombre' => optional(optional($pago->grado)->nivel)->nombre,
                'grado_nombre' => optional($pago->grado)->nombre,
                'tipo' => $pago->descripcion,
                'total' => $pago->total,
                'fecha' => $pago->fechaPago,
                'pago_estado' => 'Pagado'
            ];
        });

        return response()->json([
            'data' => $pagos,
        ]);
    }

    public function obtenerDeudasPendientes(Request $request)
    {
        $request->validate([
            'id_anho' => 'required|integer',
            'texto_buscar' => 'nullable|string',
        ]);

        $idAnho = $request->input('id_anho');
        $textoBuscar = $request->input('texto_buscar');

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

            return $pdf->download('deudas-pendientes-' . $idAnho . '.pdf');
        }
    }
}
