<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Comprobante de Pago de Matrícula</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .institution {
            font-size: 16px;
            font-weight: bold;
        }
        .title {
            font-size: 14px;
            margin-top: 10px;
            text-decoration: underline;
        }
        .student-info {
            margin-bottom: 20px;
        }
        .payment-info {
            margin-bottom: 30px;
        }
        .details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .details th {
            background-color: #f2f2f2;
            text-align: left;
            padding: 8px;
            border: 1px solid #ddd;
        }
        .details td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .amounts {
            width: 50%;
            float: right;
            border-collapse: collapse;
        }
        .amounts td {
            padding: 5px;
            border: 1px solid #ddd;
        }
        .amounts td:first-child {
            font-weight: bold;
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="institution">BIGSEI</div>
        <div class="title">COMPROBANTE DE PAGO DE MATRÍCULA</div>
    </div>

    <div class="student-info">
        <strong>Estudiante:</strong> {{ $estudiante }}<br>
        <strong>Fecha de pago:</strong> {{ date('d/m/Y', strtotime($pago->fechaPago)) }}<br>
        <strong>Código de matrícula:</strong> {{ $idMatricula ?? '' }}
    </div>

    <table class="details">
        <thead>
            <tr>
                <th>Descripción</th>
                <th>Método de Pago</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $pago->descripcion }}</td>
                <td>{{ $pago->metodoPago }}</td>
            </tr>
        </tbody>
    </table>

    <table class="amounts">
        <tr>
            <td>Importe:</td>
            <td>S/ {{ number_format($pago->importe, 2) }}</td>
        </tr>
        <tr>
            <td>IGV (18%):</td>
            <td>S/ {{ number_format($pago->igv, 2) }}</td>
        </tr>
        <tr>
            <td><strong>TOTAL:</strong></td>
            <td><strong>S/ {{ number_format($pago->total, 2) }}</strong></td>
        </tr>
    </table>

    <div class="clear"></div>

    <div class="footer">
        Este documento es un comprobante de pago generado automáticamente.<br>
        Fecha de emisión: {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>