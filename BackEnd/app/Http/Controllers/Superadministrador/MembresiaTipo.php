<?php
namespace App\Http\Controllers;

use App\Models\MembresiaTipo;

class MembresiaTipoController extends Controller
{
    public function index()
    {
        // Trae todas las membresías, sin filtrar por estado
        $membresias = MembresiaTipo::all();

        // Retorna la vista con los datos
        return view('membresias.index', compact('membresias'));
    }
}