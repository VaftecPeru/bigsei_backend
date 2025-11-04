<?php

namespace App\Http\Controllers\Superadministrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Persona;
use App\Models\Estudiante;
use App\Models\Matricula;

class EstudianteController extends Controller
{
    public function index(Request $request)
    {
        $result = DB::table('estudiante as a')
            ->join('persona as b', 'a.id_estudiante', 'b.id_persona')
            ->select(
                'a.id_estudiante',
                'b.nombre_completo',
                'b.telefono',
                'b.correo',
                'b.numero_documento',
                DB::raw("case when a.estado = '1' then 'Activo' else 'Desactivo' end estado_descripcion")
            );
            // ->where("a.id_empresa", 1);
        if(isset($request->text_search)) {
            $texto = strtr(utf8_decode($request->text_search), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = strtr(utf8_decode($texto), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $texto = str_replace(' ', '%', $texto);
            $result->whereRaw("upper(concat(b.nombre_completo, b.telefono, b.correo)) LIKE upper( ? )", ['%'.$texto.'%']);
        }
        $result->orderBy("nombre_completo", "asc");
        $result = $result->get();

        return response()->json($result);
    }

    public function show($id_estudiante)
    {
        $result = DB::table('estudiante as a')
            ->join('persona as b', 'a.id_estudiante', 'b.id_persona')
            ->select(
                'a.id_estudiante',
                'a.estado',
                'b.nombre_completo',
                'b.telefono',
                'b.correo',
                'b.fecha_nacimiento',
                'b.sexo',
                'b.direccion',
                DB::raw("date_format(b.fecha_nacimiento, '%Y-%m-%d') as fecha_nacimiento_date")
            )
            ->where('a.id_estudiante', $id_estudiante)
            ->first();

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_completo' => 'required|string|max:450',
            'fecha_nacimiento' => 'required|date',
            'telefono' => 'required|integer|digits:9',
            'correo' => 'required|string|email|max:50|unique:usuario',
            'direccion' => 'required|string|max:60',
            'sexo' => 'required|string|max:1',
            'estado' => 'required|string|max:1',
        ], [
            'nombre_completo.required' => 'El nombre es requerido',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es requerida',
            'telefono.required' => 'El teléfono es requerido',
            'telefono.digits' => 'El teléfono debe tener 9 dígitos',
            'correo.required' => 'El correo es requerido',
            'correo.email' => 'El correo no es válido',
            'correo.unique' => 'El correo ya existe',
            'direccion.required' => 'La dirección es requerida',
            'sexo.required' => 'El campo sexo es requerida',
            'estado.required' => 'El estado es requerida',
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $persona = [];
        // $persona["id_empresa"] = 1;
        // $persona["nombre"] = $request->nombre_completo;
        $persona["nombre_completo"] = $request->nombre_completo;
        $persona["fecha_nacimiento"] = $request->fecha_nacimiento;
        $persona["telefono"] = $request->telefono;
        $persona["correo"] = $request->correo;
        $persona["direccion"] = $request->direccion;
        $persona["sexo"] = $request->sexo;
        $persona["fechareg"] = now();
        $persona["estado"] = '1';
        $persona = Persona::create($persona);

        $estudiante = [];
        $estudiante["id_estudiante"] = $persona->id_persona;
        // $estudiante["id_empresa"] = 1;
        $estudiante["codigo"] = 1;
        $estudiante["estado"] = $request->estado;
        $estudiante["id_usuarioreg"] = 1;
        $estudiante["fechareg"] = now();
        $estudiante = Estudiante::create($estudiante);

        $result = Persona::find($persona->id_persona);

        return response()->json($result);
    }

    public function update(Request $request, $id_estudiante)
    {
        $validator = Validator::make($request->all(), [
            'nombre_completo' => 'required|string|max:450',
            'fecha_nacimiento' => 'required|date',
            'telefono' => 'required|integer|digits:9',
            'correo' => 'required|string|email|max:50|unique:usuario',
            'direccion' => 'required|string|max:60',
            'sexo' => 'required|string|max:1',
            'estado' => 'required|string|max:1',
        ], [
            'nombre_completo.required' => 'El nombre es requerido',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es requerida',
            'telefono.required' => 'El teléfono es requerido',
            'telefono.digits' => 'El teléfono debe tener 9 dígitos',
            'correo.required' => 'El correo es requerido',
            'correo.email' => 'El correo no es válido',
            'correo.unique' => 'El correo ya existe',
            'direccion.required' => 'La dirección es requerida',
            'sexo.required' => 'El campo sexo es requerida',
            'estado.required' => 'El estado es requerida',
        ]);

        if ($validator->fails()) {
            return response()->json(
                implode(",",$validator->messages()->all()), 400);
        }

        $estudiante = Estudiante::find($id_estudiante);

        if(!$estudiante) {
            return response()->json("El estudiante no existe.", 400);
        }

        $persona = Persona::find($id_estudiante);

        $personaEdit = [];
        // $personaEdit["nombre"] = $request->nombre_completo;
        $personaEdit["nombre_completo"] = $request->nombre_completo;
        $personaEdit["fecha_nacimiento"] = $request->fecha_nacimiento;
        $personaEdit["telefono"] = $request->telefono;
        $personaEdit["correo"] = $request->correo;
        $personaEdit["direccion"] = $request->direccion;
        $personaEdit["sexo"] = $request->sexo;
        $persona->update($personaEdit);

        $estudianteEdit = [];
        // $estudianteEdit["codigo"] = 1;
        $estudianteEdit["estado"] = $request->estado;
        $estudiante->update($estudianteEdit);

        $result = Persona::find($persona->id_persona);

        return response()->json($result);
    }

    public function destroy($id_estudiante)
    {
        $estudiante = Estudiante::find($id_estudiante);
        if(!$estudiante) {
            return response()->json("Estudiante no encontrado.", 400);
        }
        $persona = Persona::find($id_estudiante);
        if(!$persona) {
            return response()->json("Persona no encontrada.", 400);
        }

        $matricula = Matricula::where("id_estudiante", $id_estudiante)->first();
        if($matricula) {
            return response()->json("El estudiante tiene matrìcula.", 400);
        }

        $estudiante->delete();
        $persona->delete();

        return response()->json([]);
    }
}