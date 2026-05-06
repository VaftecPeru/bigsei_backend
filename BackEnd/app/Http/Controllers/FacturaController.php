<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Factura;
use Barryvdh\DomPDF\Facade\Pdf;

class FacturaController extends Controller
{
    // VER FACTURA JSON
    public function mostrar($idFactura)
    {
        $factura = Factura::with('pago.usuario')->find($idFactura);

        if (!$factura) {
            return response()->json([
                "message" => "Factura no encontrada"
            ], 404);
        }

        return response()->json($factura);
    }

    // VER PDF (ESTILO SUNAT)
    public function descargar($idFactura)
    {
        $factura = Factura::with('pago.usuario')->find($idFactura);

        if (!$factura) {
            return response()->json([
                "message" => "Factura no encontrada"
            ], 404);
        }

        $pdf = Pdf::loadView('pdf.factura', [
            'factura' => $factura
        ])->setPaper('A4', 'portrait');

        // 🔥 CAMBIO AQUÍ
        return $pdf->stream('FACTURA-' . $factura->numeroFactura . '.pdf');
    }
}