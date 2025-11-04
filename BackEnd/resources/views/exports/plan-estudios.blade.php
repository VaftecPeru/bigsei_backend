<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Descargar Plan de Estudios</title>
  <style>
    * {
      font-family: Arial, sans-serif;
    }
    h1 {
      text-align: center;
    }
    table {
      margin: 20px auto;
      border-collapse: collapse;
    }
    td {
      padding: 5px;
    }
    .table-datos-alumno {
      width: 100%;
      border: 1px solid black;
    }
    .tabla-plan-estudio {
      width: 100%;
      border: 1px solid black;
    }
    .sub-titulo {
      font-weight: bold;
    }
    .text-center {
      text-align: center;
    }
  </style>
</head>
<body>
  <h1>Plan de Estudios</h1>

  <table class="table-datos-alumno" border="1">
    <tbody>
      <tr>
        <td class="sub-titulo">Nombre del estudiante:</td>
        <td>{{ $nombreEstudiante }}</td>
      </tr>
      <tr>
        <td class="sub-titulo">Carrera:</td>
        <td>{{ $planEstudio['carrera'] }}</td>
      </tr>
      <tr>
        <td class="sub-titulo">Plan de estudios:</td>
        <td>{{ $planEstudio['nombrePlan'] }}</td>
      </tr>
    </tbody>
  </table>

  <table class="tabla-plan-estudio" border="1">
    <thead>
      <tr>
        <th>Curso</th>
        <th>Creditos</th>
        <th>Horas</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($planEstudio['ciclos'] as $ciclo)
        <tr>
          <td class="text-center" colspan="3">{{ $ciclo['nombreCiclo'] }}</td>
          @foreach ($ciclo['cursos'] as $curso)
            <tr>
              <td class="text-center">{{ $curso['nombreCurso'] }}</td>
              <td class="text-center">{{ $curso['creditos'] }}</td>
              <td class="text-center">{{ $curso['horas'] }}</td>
            </tr>
          @endforeach
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>