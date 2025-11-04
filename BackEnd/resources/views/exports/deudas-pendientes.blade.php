<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Deudas Pendientes - Año {{ $anho }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .total { font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Deudas Pendientes</h1>
        <h2>Año: {{ $anho }}</h2>
        @if($textoBuscar)
        <p>Filtrado por: "{{ $textoBuscar }}"</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Descripción</th>
                <th>Importe</th>
                <th>Fecha a Pagar</th>
                <th>Observación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deudas as $deuda)
            <tr>
                <td>{{ $deuda->usuario->nombre ?? 'N/A' }}</td>
                <td>{{ $deuda->descripcion }}</td>
                <td>{{ number_format($deuda->importe, 2) }}</td>
                <td>{{ $deuda->fecha_a_pagar->format('d/m/Y') }}</td>
                <td>{{ $deuda->observacion ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        Total de deudas: {{ count($deudas) }}<br>
        Importe total: {{ number_format($deudas->sum('importe'), 2) }}
    </div>
</body>
</html>