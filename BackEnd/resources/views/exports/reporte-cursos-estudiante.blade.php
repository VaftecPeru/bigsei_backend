<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Cursos</title>
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
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
        }
        .student-info {
            margin-bottom: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .footer {
            text-align: right;
            margin-top: 30px;
            font-size: 10px;
            color: #666;
        }
        .text-center {
            text-align: center;
        }
        .course-title {
            background-color: #f9f9f9;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Cursos Matriculados</h1>
        <p>Bigsei</p>
    </div>

    <div class="student-info">
        <p><strong>Estudiante:</strong> {{ $estudiante->nombre }} {{ $estudiante->apellido }}</p>
        <p><strong>Código:</strong> {{ $estudiante->codigo ?? 'N/A' }}</p>
        <p><strong>Fecha de emisión:</strong> {{ $fecha }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre del Curso</th>
                <th>Docente</th>
                <th>Días</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cursos as $index => $curso)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $curso['nombreCurso'] }}</td>
                    <td>{{ $curso['docente'] }}</td>
                    <td class="text-center">{{ $curso['dias'] }}</td>
                </tr>
            @endforeach
            @if(count($cursos) == 0)
                <tr>
                    <td colspan="4" class="text-center">No tiene cursos matriculados</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        Sistema Académico - {{ date('Y') }}
    </div>
</body>
</html>