<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Visitas</title>
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
        .title {
            font-size: 18px;
            font-weight: bold;
        }
        .subtitle {
            font-size: 14px;
            color: #555;
        }
        .filtros {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 8px;
        }
        td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .total {
            font-weight: bold;
            background-color: #e9ecef;
        }
        .footer {
            margin-top: 20px;
            font-size: 10px;
            text-align: right;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $titulo }}</div>
        <div class="subtitle">Generado el: {{ date('d/m/Y H:i:s') }}</div>
    </div>

    @if(!empty($filtros))
    <div class="filtros">
        <strong>Filtros aplicados:</strong>
        <ul>
            @isset($filtros['id_anho'])
                <li>Año: {{ $filtros['id_anho'] }}</li>
            @endisset
            @isset($filtros['id_mes'])
                <li>Mes: {{ $filtros['id_mes'] }}</li>
            @endisset
        </ul>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Año</th>
                <th>Mes</th>
                <th>Nombre del Mes</th>
                <th>Cantidad de Visitas</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($visitas as $visita)
            <tr>
                <td>{{ $visita->id_anho }}</td>
                <td>{{ $visita->id_mes }}</td>
                <td>{{ $visita->mes_nombre }}</td>
                <td>{{ number_format($visita->cant_visitas, 0, ',', '.') }}</td>
            </tr>
            @php $total += $visita->cant_visitas; @endphp
            @endforeach
            <tr class="total">
                <td colspan="3">TOTAL</td>
                <td>{{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Página {PAGENO} de {nbpg}
    </div>
</body>
</html>