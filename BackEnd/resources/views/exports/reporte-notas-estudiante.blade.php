<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Notas</title>
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
        .cycle-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .cycle-title {
            background-color: #333;
            color: white;
            padding: 8px;
            font-weight: bold;
        }
        .course-title {
            background-color: #f2f2f2;
            padding: 6px;
            font-weight: bold;
            margin-top: 15px;
        }
        .footer {
            text-align: right;
            margin-top: 30px;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Notas</h1>
        <p>Bigsei</p>
    </div>

    <div class="student-info">
        <p><strong>Estudiante:</strong> {{ $estudiante->nombre }} {{ $estudiante->apellido }}</p>
        <p><strong>Código:</strong> {{ $estudiante->codigo ?? 'N/A' }}</p>
        <p><strong>Fecha de emisión:</strong> {{ $fecha }}</p>
    </div>

    @foreach($cursos as $periodo)
        <div class="cycle-section">
            <div class="cycle-title">{{ $periodo['ciclo'] }}</div>

            @foreach($periodo['cursos'] as $curso)
                <div class="course-title">{{ $curso['nombreCurso'] }}</div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>Evaluación</th>
                            <th>Porcentaje</th>
                            <th>Nota</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($curso['evaluaciones'] as $evaluacion)
                            <tr>
                                <td>{{ $evaluacion['nombre'] }}</td>
                                <td>{{ $evaluacion['porcentaje'] }}%</td>
                                <td>{{ $evaluacion['nota'] }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="3"><strong>Fórmula de cálculo:</strong> {{ $curso['formula'] }}</td>
                        </tr>
                    </tbody>
                </table>
            @endforeach
        </div>
    @endforeach

    <div class="footer">
        Sistema Académico - {{ date('Y') }}
    </div>
</body>
</html>