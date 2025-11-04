<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckUser;
use App\Http\Middleware\CheckUserRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    //ACA REGISTRAMOS MIDDLEWARES
    ->withMiddleware(function (Middleware $middleware) {
        $middleware ->alias([
            //MIDDLEWARE PARA VERIFICAR ROL
            'checkRoleMW' =>CheckRole::class,
            //MIDDLEWARE PARA AUTH DEL TOKEN
            'auth.jwt' =>\Tymon\JWTAuth\Http\Middleware\Authenticate::class,
            //MIDDLEWARE CORS
            \App\Http\Middleware\CorsMiddleware::class,
            //MIDDLEWARE PARA VERIFICAR ROL
            'CheckUserRoleMW' =>CheckUserRole::class,
            'CheckUserMW' =>CheckUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
