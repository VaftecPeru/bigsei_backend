<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura Electrónica</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
        }

        .contenedor {
            border: 1px solid #000;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
        }

        .info {
            margin-bottom: 20px;
        }

        .info p {
            margin: 3px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table, th, td {
            border: 1px solid #000;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        .total {
            text-align: right;
            font-size: 14px;
            margin-top: 15px;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
        }

        .box {
            display: flex;
            justify-content: space-between;
        }

        .empresa {
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="contenedor">

    <!-- ENCABEZADO -->
    <div class="header">
        <h2>FACTURA ELECTRÓNICA</h2>
        <h3>{{ $factura->numeroFactura }}</h3>
    </div>

    <!-- EMPRESA -->
    <div class="box">
        <div class="empresa">
            SISTEMA BIGSEI<br>
            RUC: 00000000000<br>
            Lima - Perú
        </div>

        <div>
            <strong>Fecha emisión:</strong><br>
            {{ \Carbon\Carbon::parse($factura->fecha)->format('d/m/Y H:i') }}
        </div>
    </div>

    <hr>

    <!-- CLIENTE -->
    <div class="info">
        <p><strong>Cliente:</strong> {{ $factura->cliente }}</p>
        <p><strong>Documento:</strong> {{ $factura->documento }}</p>
    </div>

    <!-- TABLA -->
    <table>
        <thead>
            <tr>
                <th>Descripción</th>
                <th>Subtotal</th>
                <th>IGV</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>{{ optional($factura->pago)->descripcion ?? 'Servicio' }}</td>
                <td>S/ {{ number_format($factura->subtotal, 2) }}</td>
                <td>S/ {{ number_format($factura->igv, 2) }}</td>
                <td>S/ {{ number_format($factura->total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- TOTAL -->
    <div class="total">
        TOTAL A PAGAR: S/ {{ number_format($factura->total, 2) }}
    </div>

    <!-- FOOTER -->
    <div class="footer">
        Documento generado automáticamente por el sistema BIGSEI.
    </div>

</div>

</body>
</html>