<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: [
            __DIR__.'/../routes/api.php',
            __DIR__.'/../routes/workspace.php',
            __DIR__.'/../routes/team.php',      // Naya Team routes file
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            // Standard Aliases
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'api.token' => \App\Http\Middleware\ApiTokenAuth::class,
            'check.validation' => \App\Http\Middleware\CheckValidationMiddleware::class,

            // Auth Middlewares
            'check.token' => \App\Http\Middleware\auth\CheckTokenMiddleware::class,
            'check.credentials' => \App\Http\Middleware\auth\CheckCredentialsMiddleware::class,
            'check.active' => \App\Http\Middleware\auth\CheckActiveMiddleware::class,
            'check.user.exists' => \App\Http\Middleware\auth\CheckUserExistMiddleware::class,
            'check.user.exists.forgot' => \App\Http\Middleware\auth\CheckUserExistForForgotMiddleware::class,

            // Workspace Middlewares
            'workspace.unique.name' => \App\Http\Middleware\Workspace\CheckUniqueWorkspaceNameMiddleware::class,
            'workspace.creator' => \App\Http\Middleware\Workspace\CheckWorkspaceCreatorMiddleware::class,
            'workspace.exists' => \App\Http\Middleware\Workspace\CheckWorkspaceExistsMiddleware::class,
            'workspaces.exist' => \App\Http\Middleware\Workspace\CheckWorkspacesExistMiddleware::class,
            'workspace.access' => \App\Http\Middleware\CheckWorkspaceAccess::class,
            'workspace.ownership' => \App\Http\Middleware\CheckWorkspaceOwnership::class,

            // --- NAYE TEAM MIDDLEWARES (As per your list) ---
            'team.exists' => \App\Http\Middleware\Team\CheckTeamExistsMiddleware::class,
            'teams.exist' => \App\Http\Middleware\Team\CheckTeamsExistMiddleware::class,
            'team.member.exists' => \App\Http\Middleware\Team\CheckTeamMemberExistsMiddleware::class,
            'team.unique.name' => \App\Http\Middleware\Team\CheckUniqueTeamNameMiddleware::class,
            'workspace.creator.team' => \App\Http\Middleware\Team\CheckWorkspaceCreatorTeamMiddleware::class,
            'workspace.member.team' => \App\Http\Middleware\Team\CheckWorkspaceMemberMiddleware::class,

            // Channel Middlewares
            'channel.exists' => \App\Http\Middleware\Channel\ChannelExistMiddleware::class,
            'channel.admin' => \App\Http\Middleware\Channel\ChannelAdminMiddleware::class,
            'channel.member' => \App\Http\Middleware\Channel\MemberCheckMiddleware::class,
            
            // Other Uzair's Aliases
            'team.access' => \App\Http\Middleware\CheckTeamAccess::class,
            'team.ownership' => \App\Http\Middleware\CheckTeamOwnership::class,
            'channel.access' => \App\Http\Middleware\CheckChannelAccess::class,
            'channel.ownership' => \App\Http\Middleware\CheckChannelOwnership::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($e instanceof \Illuminate\Validation\ValidationException && $request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors()
                ], $e->status);
            }
            return null;
        });
    })->create();
