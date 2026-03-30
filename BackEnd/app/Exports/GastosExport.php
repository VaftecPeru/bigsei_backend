<?php

namespace App\Exports;

use App\Models\Gasto;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class GastosExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($fechaInicio = null, $fechaFin = null)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function collection()
    {
        $query = Gasto::with('usuario');

        if ($this->fechaInicio && $this->fechaFin) {
            $query->whereBetween('fecha_registro', [$this->fechaInicio, $this->fechaFin]);
        }

        return $query->orderBy('fecha_registro', 'desc')->get()->map(function ($gasto) {
            return [
                'N° Operación' => $gasto->nro_operacion,
                'Destinatario' => $gasto->nombre_destinatario,
                'Usuario' => $gasto->usuario
                    ? $gasto->usuario->nombres . ' ' . $gasto->usuario->apellidoPaterno . ' ' . $gasto->usuario->apellidoMaterno
                    : 'Sin usuario',
                'Monto' => $gasto->monto,
                'Fecha Registro' => Carbon::parse($gasto->fecha_registro)->format('Y-m-d'),
                'Fecha Pago' => $gasto->fecha_pago,
                'Estado Pago' => $gasto->estado_pago,
                'Nota' => $gasto->nota,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'N° Operación',
            'Destinatario',
            'Usuario',
            'Monto',
            'Fecha Registro',
            'Fecha Pago',
            'Estado Pago',
            'Nota',
        ];
    }
}
