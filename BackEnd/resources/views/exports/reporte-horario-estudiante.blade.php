<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Horario</title>
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
        .month-title {
            background-color: #333;
            color: white;
            padding: 8px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .footer {
            text-align: right;
            margin-top: 30px;
            font-size: 10px;
            color: #666;
        }
        .day-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Horario Académico</h1>
        <p>Bigsei</p>
    </div>

    <div class="student-info">
        <p><strong>Estudiante:</strong> {{ $estudiante->nombre }} {{ $estudiante->apellido }}</p>
        <p><strong>Código:</strong> {{ $estudiante->codigo ?? 'N/A' }}</p>
        <p><strong>Fecha de emisión:</strong> {{ $fecha }}</p>
    </div>

    <div class="month-title">
        Horario para el mes de: {{ ucfirst($mes) }}
    </div>

    @php
        // Agrupar cursos por día
        $horarioPorDia = collect($cursos)->groupBy('dia');
        
        // Ordenar los días de la semana
        $diasOrdenados = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        $horarioOrdenado = $horarioPorDia->sortBy(function ($item, $key) use ($diasOrdenados) {
            return array_search($key, $diasOrdenados);
        });
    @endphp

    <table class="table">
        <thead>
            <tr>
                <th>Día</th>
                <th>Curso</th>
                <th>Tipo</th>
                <th>Horario</th>
            </tr>
        </thead>
        <tbody>
            @foreach($horarioOrdenado as $dia => $cursosDia)
                @foreach($cursosDia as $index => $curso)
                    <tr>
                        @if($index === 0)
                            <td rowspan="{{ count($cursosDia) }}" class="day-row">{{ $dia }}</td>
                        @endif
                        <td>{{ $curso['nombreCurso'] }}</td>
                        <td>{{ $curso['tipoCurso'] }}</td>
                        <td>{{ $curso['horaInicio'] }} - {{ $curso['horaFin'] }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Sistema Académico - {{ date('Y') }}
    </div>
</body>
</html>