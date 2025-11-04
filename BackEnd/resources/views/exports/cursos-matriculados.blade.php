<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Cursos Matriculados</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .student-info {
            margin-bottom: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th {
            background-color: #f2f2f2;
            text-align: left;
            padding: 8px;
            border: 1px solid #ddd;
        }
        .table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">CURSOS MATRICULADOS</div>
    </div>

    <div class="student-info">
        <strong>Estudiante:</strong> {{ $estudiante }}
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Curso</th>
                <th>Docente</th>
                <th>Día</th>
                <th>Horario</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cursos as $index => $curso)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $curso['nombreCurso'] }}</td>
                <td>{{ $curso['docente'] }}</td>
                <td>{{ $curso['dia'] }}</td>
                <td>{{ $curso['horaInicio'] }} - {{ $curso['horaFin'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generado el: {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>