<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Reservas</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
            padding: 8px;
            border: 1px solid #ddd;
        }
        td {
            padding: 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        .foto-estudiante {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
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
        <div class="title">Reporte de Reservas de Libros</div>
        <div class="subtitle">Generado el: {{ date('d/m/Y H:i:s') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Libro</th>
                <th>Título</th>
                <th>Código</th>
                <th>DNI Estudiante</th>
                <th>Nombre Completo</th>
                <th>Foto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td>{{ $item['id_libro'] }}</td>
                <td>{{ $item['titulo'] }}</td>
                <td>{{ $item['codigo'] }}</td>
                <td>{{ $item['estudiante_dni'] }}</td>
                <td>{{ $item['estudiante_nombre_completo'] }}</td>
                <td>
                    @if($item['estudiante_foto_url'])
                        <img src="{{ $item['estudiante_foto_url'] }}" class="foto-estudiante">
                    @else
                        Sin foto
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Página {PAGENO} de {nbpg}
    </div>
</body>
</html>