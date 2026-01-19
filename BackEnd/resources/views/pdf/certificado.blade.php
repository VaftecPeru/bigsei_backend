<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado de Finalización</title>
    <style>
        @page {
            margin: 0cm 0cm;
            size: A4 landscape;
        }
        
        body {
            font-family: 'DejaVu Sans', 'Helvetica', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #fff;
            width: 100%;
            height: 100%;
        }
        
        /* 
           Usar position: fixed para el contenedor principal asegura que cubra
           toda la página exactamente, sin márgenes que lo empujen.
           Esto evita la "segunda hoja" y el "desplazamiento".
        */
        .certificate-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            background: white;
            z-index: -10;
        }
        
        .border-frame {
            position: absolute;
            top: 1cm;
            left: 1cm;
            right: 1cm;
            bottom: 1cm;
            border: 6px solid #1e3a8a;
            outline: 2px solid #fbbf24;
            outline-offset: -8px;
            background: transparent;
            z-index: 10;
        }

        .content {
            position: absolute;
            top: 2cm;
            left: 2.5cm;
            right: 2.5cm;
            bottom: 2cm;
            text-align: center;
            z-index: 20;
        }
        
        .header {
            margin-bottom: 25px;
        }
        
        .logo {
            height: 70px;
            width: auto;
            margin-bottom: 15px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        
        .company-name {
            font-size: 14px;
            color: #64748b;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 5px 0;
        }
        
        .title {
            font-size: 38px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 10px 0;
            letter-spacing: 3px;
            line-height: 1;
        }
        
        .subtitle {
            font-size: 12px;
            color: #fbbf24;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 30px;
        }
        
        .recipient-label {
            font-size: 13px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }
        
        .recipient-name {
            font-size: 30px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0 auto 25px auto;
            border-bottom: 2px solid #fbbf24;
            display: inline-block;
            padding-bottom: 8px;
            min-width: 450px;
        }
        
        .description {
            font-size: 13px;
            color: #475569;
            line-height: 1.5;
            max-width: 85%;
            margin: 0 auto 10px;
        }
        
        .course-name {
            font-size: 22px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 15px 0;
        }
        
        .course-type {
            display: inline-block;
            background: #1e3a8a;
            color: white;
            padding: 4px 18px;
            border-radius: 12px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 25px;
        }
        
        /* Footer usando tabla simple para layout fijo */
        .footer-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }
        
        .footer-cell {
            width: 33.33%;
            vertical-align: bottom;
            padding: 0 10px;
        }
        
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .date-label, .code-label {
            font-size: 9px;
            color: #94a3b8;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        
        .date-value, .code-value {
            font-size: 11px;
            color: #1e3a8a;
            font-weight: bold;
        }
        
        .signature-line {
            width: 180px;
            border-top: 1.5px solid #1e3a8a;
            margin: 0 auto 5px;
        }
        
        .signature-name {
            font-size: 11px;
            color: #1e3a8a;
            font-weight: bold;
            margin: 0;
        }
        
        .signature-title {
            font-size: 9px;
            color: #64748b;
            margin: 0;
        }
    </style>
</head>
<body>
    <!-- Contenedor fijo que actúa como "papel" -->
    <div class="certificate-wrapper">
        <div class="border-frame"></div>
        
        <div class="content">
            <div class="header">
                @if($logo_base64)
                    {{-- Usar display:block y margin auto para centrar img en dompdf --}}
                    <img src="{{ $logo_base64 }}" class="logo" alt="BIGSEI">
                @endif
                <p class="company-name">{{ $nombre_empresa }}</p>
                <h1 class="title">CERTIFICADO</h1>
                <p class="subtitle">DE FINALIZACIÓN DE CURSO</p>
            </div>
            
            <p class="recipient-label">Se otorga a</p>
            <h2 class="recipient-name">{{ $nombre_estudiante }}</h2>
            
            <p class="description">
                Por haber completado satisfactoriamente el 100% del contenido del curso:
            </p>
            
            <p class="course-name">{{ $nombre_curso }}</p>
            
            <span class="course-type">
                Curso {{ $es_sincrono ? 'Síncrono' : 'Asíncrono' }}
            </span>
            
            <p class="description" style="font-size: 11px;">
                Duración aproximada: {{ $duracion_curso }} horas
            </p>
            
            <table class="footer-table">
                <tr>
                    <td class="footer-cell text-left">
                        <p class="date-label">Fecha de emisión</p>
                        <p class="date-value">{{ $fecha_emision }}</p>
                    </td>
                    <td class="footer-cell text-center">
                        <div class="signature-line"></div>
                        <p class="signature-name">Director Académico</p>
                        <p class="signature-title">{{ $nombre_empresa }}</p>
                    </td>
                    <td class="footer-cell text-right">
                        <p class="code-label">Código de verificación</p>
                        <p class="code-value">{{ $codigo_certificado }}</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
