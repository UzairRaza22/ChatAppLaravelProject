<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

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
            'check.validation' => \App\Http\Middleware\CheckValidationMiddleware::class,
            'check.token' => \App\Http\Middleware\auth\CheckTokenMiddleware::class,
            'check.credentials' => \App\Http\Middleware\auth\CheckCredentialsMiddleware::class,
            'check.active' => \App\Http\Middleware\auth\CheckActiveMiddleware::class,
            'check.user.exists' => \App\Http\Middleware\auth\CheckUserExistMiddleware::class,
            'check.user.exists.forgot' => \App\Http\Middleware\auth\CheckUserExistForForgotMiddleware::class,
            'workspace.unique.name' => \App\Http\Middleware\Workspace\CheckUniqueWorkspaceNameMiddleware::class,
            'workspace.creator' => \App\Http\Middleware\Workspace\CheckWorkspaceCreatorMiddleware::class,
            'workspace.exists' => \App\Http\Middleware\Workspace\CheckWorkspaceExistsMiddleware::class,
            'workspaces.exist' => \App\Http\Middleware\Workspace\CheckWorkspacesExistMiddleware::class,
            // admin middlewares
            'check.admin' => \App\Http\Middleware\Admin\CheckAdminMiddleware::class,
            'check.user.exists.impersonate' => \App\Http\Middleware\Admin\CheckUserExistsForImpersonationMiddleware::class,
            'check.workspace.exists' => \App\Http\Middleware\Admin\CheckWorkspaceExistsMiddleware::class,
            'check.team.exists' => \App\Http\Middleware\Admin\CheckTeamExistsMiddleware::class,
            'check.channel.exists' => \App\Http\Middleware\Admin\CheckChannelExistsMiddleware::class,
            'check.message.exists' => \App\Http\Middleware\Admin\CheckMessageExistsMiddleware::class,
            'check.tokens' => \App\Http\Middleware\Admin\CheckTokensMiddleware::class,

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            // Handle validation exceptions for API routes
            if ($e instanceof \Illuminate\Validation\ValidationException && $request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors()
                ], $e->status);
            }

            return null;
        });
    })->create();
