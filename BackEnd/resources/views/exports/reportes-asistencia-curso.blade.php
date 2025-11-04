<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Asistencia</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
        .subtitle { font-size: 18px; color: #555; margin-bottom: 30px; }
        .stats-container { display: flex; justify-content: space-around; margin-bottom: 30px; }
        .stat-box { text-align: center; width: 45%; padding: 15px; border-radius: 8px; }
        .asistencia { background-color: #e6f7e6; }
        .inasistencia { background-color: #ffe6e6; }
        .percentage { font-size: 32px; font-weight: bold; }
        .variation { font-size: 16px; }
        .positive { color: green; }
        .negative { color: red; }
        .footer { margin-top: 30px; text-align: right; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Reporte de Asistencia</div>
        <div class="subtitle">Estadísticas del periodo académico actual</div>
    </div>

    <div>
        <p><strong>Periodo:</strong> {{ $periodo }}</p>
        <p><strong>Ciclo:</strong> {{ $ciclo }}</p>
        <p><strong>Curso:</strong> {{ $curso }}</p>
    </div>

    <div class="stats-container">
        <div class="stat-box asistencia">
            <div class="percentage">{{ $porcentajeAsistencia }}%</div>
            <div class="variation {{ $variacionAsistencia >= 0 ? 'positive' : 'negative' }}">
                {{ $variacionAsistencia >= 0 ? '+' : '' }}{{ $variacionAsistencia }}%
            </div>
            <div>Asistencia</div>
        </div>

        <div class="stat-box inasistencia">
            <div class="percentage">{{ $porcentajeInasistencia }}%</div>
            <div class="variation {{ $variacionInasistencia >= 0 ? 'positive' : 'negative' }}">
                {{ $variacionInasistencia >= 0 ? '+' : '' }}{{ $variacionInasistencia }}%
            </div>
            <div>Inasistencia</div>
        </div>
    </div>

    <div class="footer">
        Generado el: {{ $fechaGeneracion }}
    </div>
</body>
</html>