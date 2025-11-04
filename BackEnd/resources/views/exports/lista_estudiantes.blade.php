<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Listado de Estudiantes</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 20px; font-weight: bold; margin-bottom: 5px; }
        .subtitle { font-size: 14px; color: #555; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #f2f2f2; text-align: left; padding: 8px; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        .footer { margin-top: 30px; text-align: right; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $titulo }}</div>
        <div class="subtitle">Generado por el sistema académico</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre del Estudiante</th>
            </tr>
        </thead>
        <tbody>
            @foreach($docentes as $docente)
            <tr>
                <td>{{ $docente->idUsuario }}</td>
                <td>{{ $docente->nombre }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generado el: {{ $fechaGeneracion }}
    </div>
</body>
</html>