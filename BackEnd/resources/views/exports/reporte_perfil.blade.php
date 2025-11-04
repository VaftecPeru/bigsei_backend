<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Estudiantes</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; }
        .date { font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Reporte de Estudiantes</div>
        <div class="date">Generado el: {{ date('d/m/Y H:i:s') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Estudiante</th>
                <th>Código</th>
                <th>ID Empresa</th>
                <th>Estado</th>
                <th>Registrado por</th>
                <th>Fecha Registro</th>
            </tr>
        </thead>
        <tbody>
            @foreach($estudiantes as $estudiante)
                <tr>
                    <td>{{ $estudiante->id_estudiante }}</td>
                    <td>{{ $estudiante->codigo }}</td>
                    <td>{{ $estudiante->id_empresa }}</td>
                    <td>{{ $estudiante->estado }}</td>
                    <td>{{ $estudiante->id_usuarioreg }}</td>
                    <td>{{ $estudiante->fechareg }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>