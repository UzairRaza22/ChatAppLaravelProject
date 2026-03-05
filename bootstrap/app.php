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
            'check.validation' => \App\Http\Middleware\CheckValidationMiddleware::class,
            'check.token' => \App\Http\Middleware\auth\CheckTokenMiddleware::class,
            'check.credentials' => \App\Http\Middleware\auth\CheckCredentialsMiddleware::class,
            'check.active' => \App\Http\Middleware\auth\CheckActiveMiddleware::class,
            'check.user.exists' => \App\Http\Middleware\auth\CheckUserExistMiddleware::class,
            'check.user.exists.forgot' => \App\Http\Middleware\auth\CheckUserExistForForgotMiddleware::class,
            'check.workspace.unique.name' => \App\Http\Middleware\Workspace\CheckUniqueWorkspaceNameMiddleware::class,
            'check.workspace.creator' => \App\Http\Middleware\Workspace\CheckWorkspaceCreatorMiddleware::class,
            'check.workspace.exists' => \App\Http\Middleware\Workspace\CheckWorkspaceExistsMiddleware::class,
            'check.workspaces.exist' => \App\Http\Middleware\Workspace\CheckWorkspacesExistMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle ModelNotFoundException
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->notFound('Resource not found.');
            }
        });

        // Handle AuthenticationException
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->unauthorized('Unauthenticated. Please login to continue.');
            }
        });

        // Handle AuthorizationException
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->forbidden('You do not have permission to perform this action.');
            }
        });

        // Handle ValidationException
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->validation($e->errors(), 'The given data was invalid.');
            }
        });

        // Handle HttpException
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if ($request->is('api/*')) {
                $message = match($e->getStatusCode()) {
                    404 => 'Resource not found.',
                    403 => 'Forbidden.',
                    401 => 'Unauthorized.',
                    500 => 'Internal server error.',
                    default => $e->getMessage() ?: 'An error occurred.'
                };
                
                return response()->error($message, $e->getStatusCode());
            }
        });

        // Handle any other Throwable
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                // In production, you might want to log this and return a generic message
                if (app()->environment('production')) {
                    return response()->error('Internal server error.', 500);
                }
                
                // In development, return more details
                return response()->error($e->getMessage(), 500);
            }
        });
    })->create();
