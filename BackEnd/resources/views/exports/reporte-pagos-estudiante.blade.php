<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Pagos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
        }
        .student-info {
            margin-bottom: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .footer {
            text-align: right;
            margin-top: 30px;
            font-size: 10px;
            color: #666;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            margin-top: 20px;
            font-weight: bold;
        }
        .no-pagos {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Pagos Realizados</h1>
        <p>Bigsei</p>
    </div>

    <div class="student-info">
        <p><strong>Estudiante:</strong> {{ $estudiante->nombre }} {{ $estudiante->apellido }}</p>
        <p><strong>Código:</strong> {{ $estudiante->codigo ?? 'N/A' }}</p>
        <p><strong>Fecha de emisión:</strong> {{ $fecha }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Descripción</th>
                <th>Método de Pago</th>
                <th class="text-right">Importe (S/)</th>
                <th class="text-right">IGV (S/)</th>
                <th class="text-right">Total (S/)</th>
                <th class="text-center">Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pagos as $index => $pago)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $pago->descripcion }}</td>
                    <td>{{ $pago->metodoPago }}</td>
                    <td class="text-right">{{ number_format($pago->importe, 2) }}</td>
                    <td class="text-right">{{ number_format($pago->igv, 2) }}</td>
                    <td class="text-right">{{ number_format($pago->total, 2) }}</td>
                    <td class="text-center">{{ date('d/m/Y', strtotime($pago->fechaPago)) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="no-pagos">No se encontraron registros de pago</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($pagos) > 0)
        <tfoot>
            <tr class="totals">
                <td colspan="3" class="text-right"><strong>TOTALES:</strong></td>
                <td class="text-right">{{ number_format($totales['totalImporte'], 2) }}</td>
                <td class="text-right">{{ number_format($totales['totalIgv'], 2) }}</td>
                <td class="text-right">{{ number_format($totales['totalGeneral'], 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        Sistema Académico - {{ date('Y') }}
    </div>
</body>
</html>