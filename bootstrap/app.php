<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

// Import all middleware classes
use App\Http\Middleware\CheckValidationMiddleware;
use App\Http\Middleware\auth\CheckTokenMiddleware;
use App\Http\Middleware\auth\CheckCredentialsMiddleware;
use App\Http\Middleware\auth\CheckActiveMiddleware;
use App\Http\Middleware\auth\CheckUserExistMiddleware;
use App\Http\Middleware\auth\CheckUserExistForForgotMiddleware;
use App\Http\Middleware\Workspace\CheckUniqueWorkspaceNameMiddleware;
use App\Http\Middleware\Workspace\CheckWorkspaceCreatorMiddleware;
use App\Http\Middleware\Workspace\CheckWorkspaceExistsMiddleware;
use App\Http\Middleware\Workspace\CheckWorkspacesExistMiddleware;

// Team middleware
use App\Http\Middleware\Team\CheckTeamExistsMiddleware;
use App\Http\Middleware\Team\CheckTeamMemberExistsMiddleware;
use App\Http\Middleware\Team\CheckTeamsExistMiddleware;
use App\Http\Middleware\Team\CheckUniqueTeamNameMiddleware;
use App\Http\Middleware\Team\CheckWorkspaceCreatorTeamMiddleware;
use App\Http\Middleware\Team\CheckWorkspaceMemberMiddleware;

// Message middleware
use App\Http\Middleware\Message\Checkchannelinworkspacemiddleware;
use App\Http\Middleware\Message\Checkmessageexistsmiddleware;
use App\Http\Middleware\Message\Checkmessagefilemiddleware;
use App\Http\Middleware\Message\Checkmessagesendermiddleware;
use App\Http\Middleware\Message\Checkreceiverinworkspacemiddleware;
use App\Http\Middleware\Message\Checkworkspacemembermiddleware as CheckMessageWorkspaceMemberMiddleware;

// Channel middleware
use App\Http\Middleware\ChannelExistMiddleware;
use App\Http\Middleware\ChannelAdminMiddleware;
use App\Http\Middleware\MemberCheckMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
<<<<<<< HEAD
            // Auth & Validation middleware
            'check.validation' => CheckValidationMiddleware::class,
            'check.token' => CheckTokenMiddleware::class,
            'check.credentials' => CheckCredentialsMiddleware::class,
            'check.active' => CheckActiveMiddleware::class,
            'check.user.exists' => CheckUserExistMiddleware::class,
            'check.user.exists.forgot' => CheckUserExistForForgotMiddleware::class,
            
            // Workspace middleware
            'check.workspace.unique.name' => CheckUniqueWorkspaceNameMiddleware::class,
            'check.workspace.creator' => CheckWorkspaceCreatorMiddleware::class,
            'check.workspace.exists' => CheckWorkspaceExistsMiddleware::class,
            'check.workspaces.exist' => CheckWorkspacesExistMiddleware::class,
            
            // Team middleware
            'team.exists' => CheckTeamExistsMiddleware::class,
            'team.member.exists' => CheckTeamMemberExistsMiddleware::class,
            'teams.exist' => CheckTeamsExistMiddleware::class,
            'team.unique.name' => CheckUniqueTeamNameMiddleware::class,
            'workspace.creator.team' => CheckWorkspaceCreatorTeamMiddleware::class,
            'workspace.member.team' => CheckWorkspaceMemberMiddleware::class,
            
            // Message middleware
            'message.workspace.member' => Checkchannelinworkspacemiddleware::class,
            'message.receiver.check' => Checkmessageexistsmiddleware::class,
            'message.file.check' => Checkmessagefilemiddleware::class,
            'message.sender.check' => Checkmessagesendermiddleware::class,
            'message.channel.check' => Checkreceiverinworkspacemiddleware::class,
            'message.exists' => CheckMessageWorkspaceMemberMiddleware::class,
            
            // Channel middleware
            'channel.exists' => ChannelExistMiddleware::class,
            'channel.admin' => ChannelAdminMiddleware::class,
            'channel.member' => MemberCheckMiddleware::class,
=======
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
            'message.workspace.member' => \App\Http\Middleware\Message\Checkworkspacemembermiddleware::class,
            'message.receiver.check' => \App\Http\Middleware\Message\Checkreceiverinworkspacemiddleware::class,
            'message.channel.check' => \App\Http\Middleware\Message\Checkchannelinworkspacemiddleware::class,
            'message.exists' => \App\Http\Middleware\Message\Checkmessageexistsmiddleware::class,
            'message.sender' => \App\Http\Middleware\Message\Checkmessagesendermiddleware::class,
            'message.file.check' => \App\Http\Middleware\Message\Checkmessagefilemiddleware::class,
>>>>>>> d811925ecffa04b2e6e5db20bc07a1a597ee98d4
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
