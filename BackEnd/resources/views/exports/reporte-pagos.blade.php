<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Pagos</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 16px; font-weight: bold; }
        .subtitle { font-size: 12px; color: #555; margin-bottom: 10px; }
        .filtros { margin-bottom: 15px; padding: 10px; background: #f5f5f5; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #343a40; color: white; text-align: left; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .totales { margin-top: 15px; padding: 10px; background: #e9ecef; border-radius: 5px; }
        .text-right { text-align: right; }
        .footer { margin-top: 20px; font-size: 10px; text-align: center; color: #6c757d; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Reporte de Pagos</div>
        <div class="subtitle">Generado el: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    @if(isset($filtros) && (count(array_filter($filtros)) > 0))
    <div class="filtros">
        <strong>Filtros aplicados:</strong>
        <ul>
            @if(!empty($filtros['fecha_inicio']))
            <li>Desde: {{ date('d/m/Y', strtotime($filtros['fecha_inicio'])) }}</li>
            @endif
            @if(!empty($filtros['fecha_fin']))
            <li>Hasta: {{ date('d/m/Y', strtotime($filtros['fecha_fin'])) }}</li>
            @endif
            @if(!empty($filtros['idNivel']) && $pagos->first()->nivel)
            <li>Nivel: {{ $pagos->first()->nivel->nombre }}</li>
            @endif
            @if(!empty($filtros['idGrado']) && $pagos->first()->grado)
            <li>Grado: {{ $pagos->first()->grado->nombre }}</li>
            @endif
            @if(!empty($filtros['idMetodoPago']) && $pagos->first()->metodoPago)
            <li>Método de pago: {{ $pagos->first()->metodoPago->nombre }}</li>
            @endif
        </ul>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Estudiante</th>
                <th>Nivel/Grado</th>
                <th>Descripción</th>
                <th>Método Pago</th>
                <th class="text-right">Importe</th>
                <th class="text-right">IGV</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pagos as $index => $pago)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ date('d/m/Y', strtotime($pago->fechaPago)) }}</td>
                <td>{{ $pago->usuario->nombre ?? 'N/A' }} {{ $pago->usuario->apellido ?? '' }}</td>
                <td>{{ $pago->nivel->nombre ?? 'N/A' }} / {{ $pago->grado->nombre ?? 'N/A' }}</td>
                <td>{{ $pago->descripcion }}</td>
                <td>{{ $pago->metodoPago->nombre ?? 'N/A' }}</td>
                <td class="text-right">S/ {{ number_format($pago->importe, 2) }}</td>
                <td class="text-right">S/ {{ number_format($pago->igv, 2) }}</td>
                <td class="text-right">S/ {{ number_format($pago->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totales">
        <strong>Totales:</strong>
        <div>Importe: S/ {{ number_format($totalImporte, 2) }}</div>
        <div>IGV: S/ {{ number_format($totalIgv, 2) }}</div>
        <div><strong>Total General: S/ {{ number_format($totalGeneral, 2) }}</strong></div>
    </div>

    <div class="footer">
        Sistema de Gestión Académica - {{ date('Y') }}
    </div>
</body>
</html>