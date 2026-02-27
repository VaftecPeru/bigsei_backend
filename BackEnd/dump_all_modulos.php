<?php
$out = "";
$roles = \Illuminate\Support\Facades\DB::table('rol')->orderBy('id_rol')->get();
foreach($roles as $rol) {
    $out .= "--- ROL: {$rol->nombre} ({$rol->id_rol}) ---\n";
    $modulos = \Illuminate\Support\Facades\DB::table('rol_modulo as rm')
        ->join('modulo as m', 'rm.id_modulo', '=', 'm.id_modulo')
        ->where('rm.id_rol', $rol->id_rol)
        ->select('m.id_modulo', 'm.nombre', 'm.url', 'm.id_modulosup', 'm.icon')
        ->orderByRaw('COALESCE(m.id_modulosup, m.id_modulo), m.id_modulosup IS NOT NULL, m.orden')
        ->get();
    foreach($modulos as $mod) {
        $parent = $mod->id_modulosup ? " (Sub de {$mod->id_modulosup})" : "";
        $out .= " - [{$mod->id_modulo}] {$mod->nombre}: {$mod->url} [Icon: {$mod->icon}]{$parent}\n";
    }
}
file_put_contents('todos_los_modulos_por_rol.txt', $out);
echo "OK\n";
