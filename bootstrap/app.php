<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\RegistrarAuditoria::class,
        ]);

        $middleware->alias([
            'rol' => \App\Http\Middleware\VerificarRol::class,
        ]);

        // Las estaciones IoT no tienen sesión web: se autentican por token (X-IOT-TOKEN)
        $middleware->validateCsrfTokens(except: ['api/iot/*']);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
