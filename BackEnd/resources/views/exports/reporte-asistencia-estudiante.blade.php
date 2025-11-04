<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Asistencia - {{ $estudiante->nombre }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; }
        .student-info { margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .summary { margin-top: 20px; padding: 10px; background-color: #f9f9f9; }
        .footer { margin-top: 30px; font-size: 12px; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">REPORTE DE ASISTENCIA</div>
        <div>Bigsei</div>
    </div>

    <div class="student-info">
        <p><strong>Estudiante:</strong> {{ $estudiante->nombre }} {{ $estudiante->apellido ?? '' }}</p>
        <p><strong>Fecha de reporte:</strong> {{ $fecha }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Curso</th>
                <th>Asistencias</th>
                <th>% Asistencia</th>
                <th>Ausencias</th>
                <th>% Ausencias</th>
                <th>Faltas Justificadas</th>
                <th>% Faltas Justificadas</th>
                <th>Total Clases</th>
            </tr>
        </thead>
        <tbody>
            @foreach($asistencias as $asistencia)
            <tr>
                <td>{{ $asistencia->nombreCurso }}</td>
                <td>{{ $asistencia->cantidad_asistencias }}</td>
                <td>{{ $asistencia->porcentaje_asistencia }}</td>
                <td>{{ $asistencia->cantidad_ausencias }}</td>
                <td>{{ $asistencia->porcentaje_ausencias }}</td>
                <td>{{ $asistencia->cantidad_faltas_justificadas }}</td>
                <td>{{ $asistencia->porcentaje_faltas_justificadas }}</td>
                <td>{{ $asistencia->total_clases }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <h3>Resumen General</h3>
        <p><strong>Total Asistencias:</strong> {{ $totales['asistencias'] }}</p>
        <p><strong>Total Ausencias:</strong> {{ $totales['ausencias'] }}</p>
        <p><strong>Total Faltas Justificadas:</strong> {{ $totales['faltas_justificadas'] }}</p>
        <p><strong>Total Clases:</strong> {{ $totales['total_clases'] }}</p>
    </div>

    <div class="footer">
        <p>Generado el {{ $fecha }} - Sistema de Gestión Académica</p>
    </div>
</body>
</html>