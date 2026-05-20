<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TipoDocumentoController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('tipo_documento')
            ->select(
                'id_tipodocumento',
                'nombre',
                'siglas'
            );

        if ($request->filled('text_search')) {
            $texto = $this->normalizeSearchText($request->text_search);
            $searchTerm = '%' . $texto . '%';

            $query->where(function ($q) use ($searchTerm) {
                $q->where(DB::raw('upper(nombre)'), 'LIKE', DB::raw("upper(?)"), [$searchTerm])
                    ->orWhere(DB::raw('upper(siglas)'), 'LIKE', DB::raw("upper(?)"), [$searchTerm]);
            });
        }

        $result = $query->orderBy('orden', 'asc')
            ->orderBy('id_tipodocumento', 'asc')->get();

        return response()->json($result);
    }

    /**
     * Normaliza el texto de búsqueda para eliminar acentos y caracteres especiales
     */
    protected function normalizeSearchText($text)
    {
        $text = utf8_decode($text);
        $from = utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ');
        $to   = 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY';

        $text = strtr($text, $from, $to);
        return str_replace(' ', '%', $text);
    }
}
