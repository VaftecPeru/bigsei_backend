<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Trámite Académico</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 30px; }
        .content { margin: 0 auto; width: 80%; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table td { padding: 8px; border: 1px solid #ddd; }
        .data-table td:first-child { font-weight: bold; width: 30%; }
        .footer { margin-top: 50px; text-align: right; }
        .signature-line { width: 300px; border-top: 1px solid #000; margin-top: 70px; display: inline-block; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Bigsei</h2>
        <h3>SOLICITUD DE TRÁMITE ACADÉMICO</h3>
    </div>

    <div class="content">
        <table class="data-table">
            <tr>
                <td>Nombre del Estudiante:</td>
                <td>{{ $nombreEstudiante }}</td>
            </tr>
            <tr>
                <td>DNI:</td>
                <td>{{ $dniEstudiante }}</td>
            </tr>
            <tr>
                <td>Carrera:</td>
                <td>{{ $nombreCarrera }}</td>
            </tr>
            <tr>
                <td>Teléfono:</td>
                <td>{{ $telefonoEstudiante }}</td>
            </tr>
            <tr>
                <td>Correo electrónico:</td>
                <td>{{ $correoEstudiante }}</td>
            </tr>
            <tr>
                <td>Tipo de Trámite:</td>
                <td>{{ $tipoTramite }}</td>
            </tr>
            <tr>
                <td>Lugar del Trámite:</td>
                <td>{{ $lugarTramite }}</td>
            </tr>
            <tr>
                <td>Fecha:</td>
                <td>{{ $fechaActual }}</td>
            </tr>
        </table>

        <div class="motivo">
            <p><strong>Motivo/Solicitud:</strong></p>
            <p>Por medio del presente documento, solicito a la oficina de trámites académicos el proceso de {{ strtolower($tipoTramite) }} para los fines que estime conveniente.</p>
        </div>

        <div class="footer">
            <p>Atentamente,</p>
            <div class="signature-line"></div>
            <p>{{ $nombreEstudiante }}</p>
        </div>
    </div>
</body>
</html>