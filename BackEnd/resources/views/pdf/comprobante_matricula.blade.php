<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Comprobante de Matrícula</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: #f8fafc;
            padding: 40px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #00264A 0%, #003366 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }
        .header p {
            font-size: 14px;
            opacity: 0.8;
        }
        .logo {
            width: 80px;
            height: auto;
            margin-bottom: 15px;
        }
        .badge {
            display: inline-block;
            background: #10b981;
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 15px;
        }
        .content {
            padding: 30px;
        }
        .student-info {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }
        .student-avatar {
            width: 50px;
            height: 50px;
            background: #e2e8f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        .student-avatar span {
            font-size: 24px;
            color: #00264A;
        }
        .student-details h3 {
            color: #00264A;
            font-size: 16px;
            margin-bottom: 4px;
        }
        .student-details p {
            color: #64748b;
            font-size: 13px;
        }
        .details-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .details-table tr {
            border-bottom: 1px solid #e2e8f0;
        }
        .details-table td {
            padding: 12px 0;
            font-size: 14px;
        }
        .details-table td:first-child {
            color: #64748b;
        }
        .details-table td:last-child {
            color: #1e293b;
            font-weight: 500;
            text-align: right;
        }
        .total-row {
            background: #f0fdf4;
            border-radius: 10px;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .total-row .label {
            color: #00264A;
            font-weight: bold;
            font-size: 14px;
        }
        .total-row .amount {
            color: #C9002B;
            font-weight: bold;
            font-size: 22px;
        }
        .status-row {
            background: #ecfdf5;
            border-radius: 10px;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .status-row span {
            color: #047857;
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            padding: 20px 30px 30px;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            color: #64748b;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .codigo {
            font-family: monospace;
            background: #f1f5f9;
            padding: 8px 16px;
            border-radius: 6px;
            display: inline-block;
            color: #334155;
            font-size: 13px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            @if($logo_base64)
                <img src="{{ $logo_base64 }}" alt="Logo" class="logo">
            @endif
            <h1>Comprobante de Matrícula</h1>
            <p>{{ $empresa_nombre ?? 'BIGSEI' }}</p>
            <div class="badge">✓ Pagado</div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Student Info -->
            <div class="student-info">
                <div class="student-avatar">
                    <span>👤</span>
                </div>
                <div class="student-details">
                    <h3>{{ $nombre_estudiante }}</h3>
                    <p>DNI: {{ $dni_estudiante }} • {{ $correo_estudiante }}</p>
                </div>
            </div>

            <!-- Details Table -->
            <table class="details-table">
                <tr>
                    <td>Curso</td>
                    <td>{{ $nombre_curso }}</td>
                </tr>
                @if($duracion_curso)
                <tr>
                    <td>Duración</td>
                    <td>{{ $duracion_curso }}</td>
                </tr>
                @endif
                @if($docente_nombre)
                <tr>
                    <td>Docente</td>
                    <td>{{ $docente_nombre }}</td>
                </tr>
                @endif
                <tr>
                    <td>Modalidad</td>
                    <td>{{ $modalidad ?? 'Virtual' }}</td>
                </tr>
                <tr>
                    <td>Fecha de Matrícula</td>
                    <td>{{ $fecha_matricula }}</td>
                </tr>
            </table>

            <!-- Total -->
            @if($precio)
            <div class="total-row">
                <span class="label">Total Pagado</span>
                <span class="amount">S/{{ number_format($precio, 2) }}</span>
            </div>
            @endif

            <!-- Status -->
            <div class="status-row">
                <span>Estado</span>
                <span>✓ Acceso habilitado</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Este documento es un comprobante válido de tu matrícula.</p>
            <p>Guarda este documento para futuras referencias.</p>
            <div class="codigo">{{ $codigo_comprobante }}</div>
        </div>
    </div>
</body>
</html>
