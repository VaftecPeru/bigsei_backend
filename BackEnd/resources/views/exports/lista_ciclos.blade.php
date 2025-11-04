<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ciclos</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; }
        .date { font-size: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #343a40; color: white; text-align: left; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .footer { margin-top: 30px; font-size: 10px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $titulo }}</div>
        <div class="date">Generado el: {{ $fecha }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Código</th>
                <th>Nombre del Ciclo</th>
                <th>Periodo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ciclos as $index => $ciclo)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $ciclo['idCiclo'] }}</td>
                <td>{{ $ciclo['nombreCiclo'] }}</td>
                <td>{{ $ciclo['nombrePeriodo'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Sistema Académico - {{ date('Y') }}
    </div>
</body>
</html>