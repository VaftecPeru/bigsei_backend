<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Periodos</title>
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
                <th>Nombre del Periodo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($periodos as $index => $periodo)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $periodo->idPeriodo }}</td>
                <td>{{ $periodo->nombre }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Sistema Académico - {{ date('Y') }}
    </div>
</body>
</html>