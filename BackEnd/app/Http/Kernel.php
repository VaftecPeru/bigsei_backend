<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * Los middlewares GLOBALES que se aplican a TODAS las rutas
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Http\Middleware\HandleCors::class, // ✅ Aquí debe estar CORS
        \App\Http\Middleware\CorsMiddleware::class,
        // Otros middlewares globales (si los tienes)
    ];

    /**
     * Los middlewares que se aplican a grupos de rutas
     *
     * @var array
     */
    protected $middlewareGroups = [
        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * Los middlewares de rutas individuales
     *
     * @var array
     */
    protected $routeMiddleware = [
        // Middlewares para asignar a rutas específicas
    ];
}
