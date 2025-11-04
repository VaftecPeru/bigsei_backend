<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Calificaciones</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; }
        .subtitle { font-size: 14px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .footer { margin-top: 30px; font-size: 12px; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Reporte de Calificaciones</div>
        <div class="subtitle">{{ $evaluacion->nombre }}</div>
        <div class="subtitle">Curso: {{ $evaluacion->curso->nombre ?? 'N/A' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Estudiante</th>
                <th>Nota</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($notas as $index => $nota)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $nota->usuario->nombre ?? 'N/A' }} {{ $nota->usuario->apellido ?? '' }}</td>
                <td>{{ number_format($nota->nota, 2) }}</td>
                <td>{{ $evaluacion->fecha ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generado el: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>