<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Horarios de Cursos</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 16px; font-weight: bold; }
        .subtitle { font-size: 12px; color: #555; }
        .filtros { margin-bottom: 15px; padding: 10px; background: #f5f5f5; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #343a40; color: white; text-align: left; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .footer { margin-top: 20px; font-size: 10px; text-align: center; color: #6c757d; }
        .dia-section { margin-top: 15px; }
        .dia-title { font-weight: bold; background-color: #e9ecef; padding: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Reporte de Horarios de Cursos</div>
        <div class="subtitle">Generado el: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    @if(count(array_filter($filtros)) > 0)
    <div class="filtros">
        <strong>Filtros aplicados:</strong>
        <ul>
            @if(!empty($filtros['idCursoTipo']))
            <li>Tipo de Curso: {{ $horarios->first()->cursoTipo->nombre ?? 'N/A' }}</li>
            @endif
            @if(!empty($filtros['dia']))
            <li>Día: {{ ucfirst($filtros['dia']) }}</li>
            @endif
            @if(!empty($filtros['fecha_ini']))
            <li>Desde: {{ date('d/m/Y', strtotime($filtros['fecha_ini'])) }}</li>
            @endif
            @if(!empty($filtros['fecha_fin']))
            <li>Hasta: {{ date('d/m/Y', strtotime($filtros['fecha_fin'])) }}</li>
            @endif
        </ul>
    </div>
    @endif

    @php
        $dias = $horarios->groupBy('dia');
    @endphp

    @foreach($dias as $dia => $horariosDia)
    <div class="dia-section">
        <div class="dia-title">Día: {{ ucfirst($dia) }}</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Curso</th>
                    <th>Aula</th>
                    <th>Docente(s)</th>
                    <th>Estudiantes</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                </tr>
            </thead>
            <tbody>
                @foreach($horariosDia as $index => $horario)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $horario->cursoTipo->nombre ?? 'N/A' }}</td>
                    <td>{{ $horario->aula }}</td>
                    <td>
                        @foreach($horario->cursoDocentes as $docente)
                            {{ $docente->docente->nombre ?? 'N/A' }} {{ $docente->docente->apellido ?? '' }}<br>
                        @endforeach
                    </td>
                    <td>{{ $horario->cursoEstudiantes->count() }}</td>
                    <td>{{ date('d/m/Y', strtotime($horario->fecha_ini)) }}</td>
                    <td>{{ date('d/m/Y', strtotime($horario->fecha_fin)) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach

    <div class="footer">
        Sistema Académico - {{ date('Y') }}
    </div>
</body>
</html>