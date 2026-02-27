<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistorialPagoController extends Controller
{
    /**
     * GET /estudiante/historial-pagos
     * Historial de pagos del estudiante autenticado.
     */
    public function index(Request $request)
    {
        $user = $request->sessionUser;

        try {
            // Intentar obtener por id_persona (join con persona)
            $pagos = DB::table('pago as pa')
                ->join('persona as p', 'pa.id_persona', '=', 'p.id_persona')
                ->leftJoin('matricula as m', 'pa.id_matricula', '=', 'm.id_matricula')
                ->where('p.id_persona', $user->id_usuario)
                ->select(
                    'pa.id_pago',
                    'pa.monto',
                    'pa.fecha_pago',
                    'pa.metodo_pago',
                    'pa.estado',
                    'pa.concepto',
                    'm.id_periodo'
                )
                ->orderBy('pa.fecha_pago', 'desc')
                ->get();

            return response()->json([
                'data'  => $pagos,
                'total' => $pagos->sum('monto'),
                'count' => $pagos->count(),
            ]);
        } catch (\Exception $e) {
            // Fallback: buscar solo por id_usuario
            try {
                $pagos = DB::table('pago')
                    ->where('id_usuario', $user->id_usuario)
                    ->orderBy('fecha_pago', 'desc')
                    ->get();

                return response()->json([
                    'data'  => $pagos,
                    'total' => $pagos->sum('monto'),
                    'count' => $pagos->count(),
                ]);
            } catch (\Exception $e2) {
                return response()->json(['data' => [], 'total' => 0, 'count' => 0]);
            }
        }
    }

    /**
     * GET /estudiante/historial-pagos/resumen
     * Resumen de deudas vs pagos realizados.
     */
    public function resumen(Request $request)
    {
        $user = $request->sessionUser;

        try {
            $totalPagado = DB::table('pago as pa')
                ->join('persona as p', 'pa.id_persona', '=', 'p.id_persona')
                ->where('p.id_persona', $user->id_usuario)
                ->where('pa.estado', 1)
                ->sum('pa.monto');

            $totalDeuda = DB::table('deuda as d')
                ->where('d.id_usuario', $user->id_usuario)
                ->where('d.estado', 0)
                ->sum('d.monto');

            return response()->json([
                'total_pagado' => $totalPagado ?? 0,
                'total_deuda'  => $totalDeuda ?? 0,
                'saldo'        => ($totalPagado ?? 0) - ($totalDeuda ?? 0),
            ]);
        } catch (\Exception $e) {
            return response()->json(['total_pagado' => 0, 'total_deuda' => 0, 'saldo' => 0]);
        }
    }
}
