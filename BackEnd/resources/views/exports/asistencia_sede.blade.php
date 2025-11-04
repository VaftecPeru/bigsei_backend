<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Asistencia - {{ $sede->nombreSede }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 20px;
        }
        .header-info {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .resumen-general {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #3498db;
        }
        .resumen-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .resumen-item strong {
            width: 180px;
        }
        .porcentaje {
            font-weight: bold;
            padding: 2px 5px;
            border-radius: 3px;
        }
        .alto { background-color: #d4edda; color: #155724; }
        .medio { background-color: #fff3cd; color: #856404; }
        .bajo { background-color: #f8d7da; color: #721c24; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #3498db;
            color: white;
            padding: 8px;
            text-align: left;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Asistencia por Sede</h1>
        <div class="header-info">
            <div><strong>Sede:</strong> {{ $sede->nombreSede }}</div>
            <div><strong>Periodo:</strong> {{ $fechaInicio }} - {{ $fechaFin }}</div>
            <div><strong>Generado:</strong> {{ $fechaGeneracion }}</div>
        </div>
    </div>

    <div class="resumen-general">
        <h3>Resumen General</h3>
        <div class="resumen-item">
            <strong>Total de estudiantes:</strong>
            <span>{{ count($reporte) }}</span>
        </div>
        <div class="resumen-item">
            <strong>Total de registros:</strong>
            <span>{{ $totalRegistros }}</span>
        </div>
        <div class="resumen-item">
            <strong>Asistencias totales:</strong>
            <span>{{ $totalAsistencias }}</span>
        </div>
        <div class="resumen-item">
            <strong>Porcentaje general:</strong>
            <span class="porcentaje 
                @if($porcentajeGeneral >= 80) alto
                @elseif($porcentajeGeneral >= 50) medio
                @else bajo
                @endif">
                {{ $porcentajeGeneral }}%
            </span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Estudiante</th>
                <th>Documento</th>
                <th class="text-center">Presentes</th>
                <th class="text-center">Ausentes</th>
                <th class="text-center">Total</th>
                <th class="text-center">Asistencia</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reporte as $item)
            <tr>
                <td>{{ $item['estudiante']->nombre }} {{ $item['estudiante']->apellido }}</td>
                <td>{{ $item['estudiante']->documento }}</td>
                <td class="text-center">{{ $item['presentes'] }}</td>
                <td class="text-center">{{ $item['ausentes'] }}</td>
                <td class="text-center">{{ $item['total'] }}</td>
                <td class="text-center">
                    <span class="porcentaje 
                        @if($item['porcentaje'] >= 80) alto
                        @elseif($item['porcentaje'] >= 50) medio
                        @else bajo
                        @endif">
                        {{ $item['porcentaje'] }}%
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Sistema de Gestión Académica - {{ date('Y') }}
    </div>
</body>
</html>