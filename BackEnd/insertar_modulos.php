<?php
use Illuminate\Support\Facades\DB;

$modulosParaInsertar = [
    // SuperAdministrador (rol 1)
    [
        'rol_id' => 1,
        'nombre' => 'Licencias',
        'url' => '/superadministrador/licencias',
        'icon' => 'FileText',
        'orden' => 18
    ],
    [
        'rol_id' => 1,
        'nombre' => 'Membresías',
        'url' => '/superadministrador/membresias',
        'icon' => 'FolderPlus',
        'orden' => 19
    ],
    [
        'rol_id' => 1,
        'nombre' => 'Vendedores',
        'url' => '/superadministrador/vendedores',
        'icon' => 'UsersIcon',
        'orden' => 20
    ],
    
    // Administrador (rol 2)
    [
        'rol_id' => 2,
        'nombre' => 'Mensajería',
        'url' => '/admin/mensajeria',
        'icon' => 'BookMarked', 
        'orden' => 14
    ],
    [
        'rol_id' => 2,
        'nombre' => 'Matrículas',
        'url' => '/admin/matriculas',
        'icon' => 'NotebookPen',
        'orden' => 15
    ],
    [
        'rol_id' => 2,
        'nombre' => 'Roles de Usuario',
        'url' => '/admin/roles',
        'icon' => 'Users',
        'orden' => 16
    ],
    [
        'rol_id' => 2,
        'nombre' => 'Membresías de Sede',
        'url' => '/admin/membresias-sede',
        'icon' => 'ReceiptText',
        'orden' => 17
    ]
];

foreach ($modulosParaInsertar as $m) {
    // Verificar si ya existe el modulo con esa URL
    $modulo = DB::table('modulo')->where('url', $m['url'])->first();
    
    if (!$modulo) {
        $idModuloItem = DB::table('modulo')->insertGetId([
            'nombre' => $m['nombre'],
            'url' => $m['url'],
            'url_activa' => '1',
            'icon' => $m['icon'],
            'estado' => '1',
            'id_modulosup' => null,
            'orden' => $m['orden']
        ]);
        echo "Modulo '{$m['nombre']}' insertado con ID: $idModuloItem\n";
    } else {
        $idModuloItem = $modulo->id_modulo;
        echo "Modulo '{$m['nombre']}' ya existia con ID: $idModuloItem\n";
    }
    
    // Verificar si el rol ya lo tiene
    $tieneRol = DB::table('rol_modulo')
                  ->where('id_rol', $m['rol_id'])
                  ->where('id_modulo', $idModuloItem)
                  ->exists();
                  
    if (!$tieneRol) {
        DB::table('rol_modulo')->insert([
            'id_rol' => $m['rol_id'],
            'id_modulo' => $idModuloItem
        ]);
        echo "  -> Asignado al rol ID: {$m['rol_id']}\n";
    }
}
echo "PROCESO COMPLETADO\n";
