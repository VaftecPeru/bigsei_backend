<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Notas por Docente</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 16px; font-weight: bold; }
        .subtitle { font-size: 12px; color: #555; }
        .docente-info { margin-bottom: 15px; }
        .curso-section { margin-bottom: 30px; page-break-inside: avoid; }
        .curso-title { font-weight: bold; background-color: #f2f2f2; padding: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #343a40; color: white; text-align: center; padding: 6px; }
        td { border: 1px solid #ddd; padding: 5px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer { margin-top: 20px; font-size: 10px; text-align: center; color: #6c757d; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Reporte de Notas por Docente</div>
        <div class="subtitle">Generado el: {{ $fechaReporte }}</div>
    </div>

    <div class="docente-info">
        <strong>Docente:</strong> {{ $docente->usuario->nombre ?? 'N/A' }} {{ $docente->usuario->apellido ?? '' }}<br>
        <strong>Código:</strong> {{ $docente->codigo }}<br>
        <strong>Especialización:</strong> {{ $docente->tipoEspecializacion->nombre ?? 'N/A' }}
    </div>

    @foreach($datosReporte as $reporte)
    <div class="curso-section">
        <div class="curso-title">
            Curso: {{ $reporte['curso']->nombre }} ({{ $reporte['curso']->codigo }})
        </div>

        <table>
            <thead>
                <tr>
                    <th rowspan="2">#</th>
                    <th rowspan="2">Alumno</th>
                    <th rowspan="2">Código</th>
                    <th colspan="{{ count($reporte['evaluaciones']) }}">Evaluaciones</th>
                    <th rowspan="2">Promedio</th>
                </tr>
                <tr>
                    @foreach($reporte['evaluaciones'] as $evaluacion)
                    <th>{{ $evaluacion->nombre }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php $i = 1; @endphp
                @foreach($reporte['alumnos'] as $alumnoData)
                <tr>
                    <td class="text-center">{{ $i++ }}</td>
                    <td>{{ $alumnoData['alumno']->nombre }} {{ $alumnoData['alumno']->apellido }}</td>
                    <td class="text-center">{{ $alumnoData['alumno']->codigo }}</td>
                    
                    @php $sumaNotas = 0; $contador = 0; @endphp
                    @foreach($reporte['evaluaciones'] as $evaluacion)
                        @if(isset($alumnoData['notas'][$evaluacion->idCursoEvaluacion]))
                            <td class="text-center">{{ number_format($alumnoData['notas'][$evaluacion->idCursoEvaluacion]['nota'], 2) }}</td>
                            @php 
                                $sumaNotas += $alumnoData['notas'][$evaluacion->idCursoEvaluacion]['nota'];
                                $contador++;
                            @endphp
                        @else
                            <td class="text-center">-</td>
                        @endif
                    @endforeach
                    
                    <td class="text-center">
                        @if($contador > 0)
                            {{ number_format($sumaNotas / $contador, 2) }}
                        @else
                            -
                        @endif
                    </td>
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