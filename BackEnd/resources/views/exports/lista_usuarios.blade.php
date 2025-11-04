<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 20px; font-weight: bold; margin-bottom: 5px; }
        .subtitle { font-size: 14px; color: #555; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #f2f2f2; text-align: left; padding: 8px; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        .footer { margin-top: 30px; text-align: right; font-size: 12px; color: #777; }
        .role-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            background-color: #e0e0e0;
            margin-right: 4px;
            font-size: 12px;
        }
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
                <th>Nombre</th>
                <th>Email</th>
                <th>Roles</th>
            </tr>
        </thead>
        <tbody>
            @foreach($usuarios as $usuario)
            <tr>
                <td>{{ $usuario->idUsuario }}</td>
                <td>{{ $usuario->nombre }}</td>
                <td>{{ $usuario->email }}</td>
                <td>
                    @foreach($usuario->roles as $rol)
                    <span class="role-badge">{{ $rol->nombreRol }}</span>
                    @endforeach
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generado el: {{ $fechaGeneracion }}
    </div>
</body>
</html>