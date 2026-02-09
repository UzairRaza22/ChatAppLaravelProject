<?php

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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'workspace.access' => \App\Http\Middleware\CheckWorkspaceAccess::class,
            'workspace.ownership' => \App\Http\Middleware\CheckWorkspaceOwnership::class,
            'team.access' => \App\Http\Middleware\CheckTeamAccess::class,
            'team.ownership' => \App\Http\Middleware\CheckTeamOwnership::class,
            'channel.access' => \App\Http\Middleware\CheckChannelAccess::class,
            'channel.ownership' => \App\Http\Middleware\CheckChannelOwnership::class,
            'api.token' => \App\Http\Middleware\ApiTokenAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
