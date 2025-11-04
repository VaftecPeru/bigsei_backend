<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Usuario por Sede</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { color: #2c3e50; }
        .section { margin-bottom: 15px; }
        .section-title { 
            background-color: #3498db; 
            color: white; 
            padding: 5px; 
            font-weight: bold; 
        }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 5px; border: 1px solid #ddd; }
        .info-table tr:nth-child(even) { background-color: #f2f2f2; }
        .footer { margin-top: 30px; text-align: right; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Usuario</h1>
        <p>Sede: {{ $sede->nombreSede ?? 'No asignada' }}</p>
    </div>

    <div class="section">
        <div class="section-title">Información del Estudiante</div>
        <table class="info-table">
            <tr>
                <td><strong>Nombre:</strong></td>
                <td>{{ $estudiante->nombre ?? 'N/A' }} {{ $estudiante->apellido ?? '' }}</td>
            </tr>
            <tr>
                <td><strong>Documento:</strong></td>
                <td>{{ $estudiante->documento ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Correo:</strong></td>
                <td>{{ $estudiante->email ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Teléfono:</strong></td>
                <td>{{ $estudiante->telefono ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    @if($padre)
    <div class="section">
        <div class="section-title">Información del Padre/Madre</div>
        <table class="info-table">
            <tr>
                <td><strong>Nombre:</strong></td>
                <td>{{ $padre->nombre ?? 'N/A' }} {{ $padre->apellido ?? '' }}</td>
            </tr>
            <tr>
                <td><strong>Documento:</strong></td>
                <td>{{ $padre->documento ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Correo:</strong></td>
                <td>{{ $padre->email ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Teléfono:</strong></td>
                <td>{{ $padre->telefono ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>
    @endif

    @if($profesor)
    <div class="section">
        <div class="section-title">Información del Profesor</div>
        <table class="info-table">
            <tr>
                <td><strong>Nombre:</strong></td>
                <td>{{ $profesor->nombre ?? 'N/A' }} {{ $profesor->apellido ?? '' }}</td>
            </tr>
            <tr>
                <td><strong>Documento:</strong></td>
                <td>{{ $profesor->documento ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Correo:</strong></td>
                <td>{{ $profesor->email ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>
    @endif

    <div class="footer">
        Generado el: {{ $fecha }}
    </div>
</body>
</html>