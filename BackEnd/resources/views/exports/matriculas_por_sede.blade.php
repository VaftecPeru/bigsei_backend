<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Matrículas por Sede</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { color: #2c3e50; text-align: center; }
        .sede { margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .sede h2 { color: #3498db; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Reporte de Matrículas por Sede</h1>
    
    @foreach($sedes as $sede)
        <div class="sede">
            <h2>Sede: {{ $sede->nombreSede }}</h2>
            
            @if($sede->matriculas->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>ID Matrícula</th>
                            <th>Alumno</th>
                            <th>Importe</th>
                            <th>Estado</th>
                            <th>Cursos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sede->matriculas as $matricula)
                            <tr>
                                <td>{{ $matricula->idMatricula }}</td>
                                <td>{{ $matricula->usuario->nombre ?? 'N/A' }}</td>
                                <td>S/ {{ number_format($matricula->importe, 2) }}</td>
                                <td>{{ ucfirst($matricula->estado) }}</td>
                                <td>
                                    @foreach($matricula->matriculaCursos as $mc)
                                        {{ $mc->curso->nombreCurso ?? 'Curso no disponible' }}@if(!$loop->last), @endif
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No hay matrículas registradas para esta sede.</p>
            @endif
        </div>
    @endforeach
</body>
</html>