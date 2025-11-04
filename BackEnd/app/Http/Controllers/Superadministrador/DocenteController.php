<?php

namespace App\Http\Controllers\Superadministrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Persona;
use App\Models\Docente;

class DocenteController extends Controller
{
    public function index(Request $request)
    {
        $result = DB::table('docente as a')
            ->join('persona as b', 'a.id_docente', 'b.id_persona')
            ->select(
                'a.id_docente',
                'b.nombre_completo',
                'b.telefono',
                'b.correo',
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

    public function show($id_docente)
    {
        $result = DB::table('docente as a')
            ->join('persona as b', 'a.id_docente', 'b.id_persona')
            ->select(
                'a.id_docente',
                'a.estado',
                'b.nombre_completo',
                'b.telefono',
                'b.correo',
                'b.fecha_nacimiento',
                'b.sexo',
                'b.direccion',
                'a.id_tipoespecializacion',
                'a.anhos_de_experiencia',
                'a.id_tiponiveleducativo',
                DB::raw("date_format(b.fecha_nacimiento, '%Y-%m-%d') as fecha_nacimiento_date")
            )
            ->where('a.id_docente', $id_docente)
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
            'id_tipoespecializacion' => 'required',
            'anhos_de_experiencia' => 'required',
            'id_tiponiveleducativo' => 'required',
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
            'id_tipoespecializacion.required' => 'La especializaciòn es requerida',
            'anhos_de_experiencia.required' => 'Los años de experiencia es requerido',
            'id_tiponiveleducativo.required' => 'El nivel educativo es requerido',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $persona = [];
        // $persona["id_empresa"] = 1;
        $persona["nombre"] = $request->nombre_completo;
        $persona["nombre_completo"] = $request->nombre_completo;
        $persona["fecha_nacimiento"] = $request->fecha_nacimiento;
        $persona["telefono"] = $request->telefono;
        $persona["correo"] = $request->correo;
        $persona["direccion"] = $request->direccion;
        $persona["sexo"] = $request->sexo;
        $persona["fechareg"] = now();
        $persona["estado"] = '1';
        $persona = Persona::create($persona);

        $docente = [];
        $docente["id_docente"] = $persona->id_persona;
        // $docente["id_empresa"] = 1;
        $docente["codigo"] = 1;
        $docente["estado"] = $request->estado;
        $docente["id_usuarioreg"] = 1;
        $docente["fechareg"] = now();
        $docente['id_tipoespecializacion'] = $request->id_tipoespecializacion;
        $docente['anhos_de_experiencia'] = $request->anhos_de_experiencia;
        $docente['id_tiponiveleducativo'] = $request->id_tiponiveleducativo;
        $docente = Docente::create($docente);

        $result = Persona::find($persona->id_docente);

        return response()->json($result);
    }

    public function update(Request $request, $id_docente)
    {
        $validator = Validator::make($request->all(), [
            'nombre_completo' => 'required|string|max:450',
            'fecha_nacimiento' => 'required|date',
            'telefono' => 'required|integer|digits:9',
            'correo' => 'required|string|email|max:50|unique:usuario',
            'direccion' => 'required|string|max:60',
            'sexo' => 'required|string|max:1',
            'estado' => 'required|string|max:1',
            'id_tipoespecializacion' => 'required',
            'anhos_de_experiencia' => 'required',
            'id_tiponiveleducativo' => 'required',
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
            'id_tipoespecializacion.required' => 'La especializaciòn es requerida',
            'anhos_de_experiencia.required' => 'Los años de experiencia es requerido',
            'id_tiponiveleducativo.required' => 'El nivel educativo es requerido',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $docente = Docente::find($id_docente);

        if(!$docente) {
            return response()->json("El estudiante no existe.", 400);
        }

        $persona = Persona::find($id_docente);

        $personaEdit = [];
        $personaEdit["nombre"] = $request->nombre_completo;
        $personaEdit["nombre_completo"] = $request->nombre_completo;
        $personaEdit["fecha_nacimiento"] = $request->fecha_nacimiento;
        $personaEdit["telefono"] = $request->telefono;
        $personaEdit["correo"] = $request->correo;
        $personaEdit["direccion"] = $request->direccion;
        $personaEdit["sexo"] = $request->sexo;
        $persona->update($personaEdit);

        $docenteEdit = [];
        // $docenteEdit["codigo"] = 1;
        $docenteEdit["estado"] = $request->estado;
        $docenteEdit["id_tipoespecializacion"] = $request->id_tipoespecializacion;
        $docenteEdit["anhos_de_experiencia"] = $request->anhos_de_experiencia;
        $docenteEdit["id_tiponiveleducativo"] = $request->id_tiponiveleducativo;
        $docente->update($docenteEdit);

        $result = Persona::find($persona->id_persona);

        return response()->json($result);
    }

    public function destroy($id_docente)
    {
        $docente = Docente::find($id_docente);
        if(!$docente) {
            return response()->json("Estudiante no encontrado.", 400);
        }
        $persona = Persona::find($id_docente);
        if(!$persona) {
            return response()->json("Persona no encontrada.", 400);
        }

        $docente->delete();
        $persona->delete();

        return response()->json([]);
    }
}