<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

// Auth middleware
use App\Http\Middleware\CheckValidationMiddleware;
use App\Http\Middleware\auth\CheckTokenMiddleware;
use App\Http\Middleware\auth\CheckCredentialsMiddleware;
use App\Http\Middleware\auth\CheckActiveMiddleware;
use App\Http\Middleware\auth\CheckUserExistMiddleware;
use App\Http\Middleware\auth\CheckUserExistForForgotMiddleware;

// Workspace middleware
use App\Http\Middleware\Workspace\CheckUniqueWorkspaceNameMiddleware;
use App\Http\Middleware\Workspace\CheckWorkspaceCreatorMiddleware;
use App\Http\Middleware\Workspace\CheckWorkspaceExistsMiddleware;
use App\Http\Middleware\Workspace\CheckWorkspacesExistMiddleware;
use App\Http\Middleware\Workspace\CheckMembersExistMiddleware;

use App\Http\Middleware\Team\CheckTeamExistsMiddleware;
use App\Http\Middleware\Team\CheckTeamMemberExistsMiddleware;
use App\Http\Middleware\Team\CheckTeamsExistMiddleware;
use App\Http\Middleware\Team\CheckUniqueTeamNameMiddleware;
use App\Http\Middleware\Team\CheckWorkspaceCreatorTeamMiddleware;
use App\Http\Middleware\Team\CheckWorkspaceMemberMiddleware;

// Message middleware
use App\Http\Middleware\Message\CheckChannelMessageMiddleware;
use App\Http\Middleware\Message\CheckMessageExistsMiddleware;
use App\Http\Middleware\Message\CheckMessageSenderMiddleware;
use App\Http\Middleware\Message\CheckMessageFileMiddleware;
use App\Http\Middleware\Message\CheckMessageFileUploadMiddleware;
use App\Http\Middleware\Message\CheckReadMessagesMiddleware;

// Channel middleware
use App\Http\Middleware\Channel\ChannelExistMiddleware;
use App\Http\Middleware\Channel\ChannelAdminMiddleware;
use App\Http\Middleware\Channel\MemberCheckMiddleware;

use App\Http\Middleware\GlobalActivityLoggerMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend([
            GlobalActivityLoggerMiddleware::class,
        ]);

        $middleware->alias([

            // ── Auth & Validation ─────────────────────────────────────────────
            'check.validation'         => CheckValidationMiddleware::class,
            'check.token'              => CheckTokenMiddleware::class,
            'check.credentials'        => CheckCredentialsMiddleware::class,
            'check.active'             => CheckActiveMiddleware::class,
            'check.user.exists'        => CheckUserExistMiddleware::class,
            'check.user.exists.forgot' => CheckUserExistForForgotMiddleware::class,

            // ── Workspace ─────────────────────────────────────────────────────
            'check.workspace.unique.name' => CheckUniqueWorkspaceNameMiddleware::class,
            'check.workspace.creator'     => CheckWorkspaceCreatorMiddleware::class,
            'check.workspace.exists'      => CheckWorkspaceExistsMiddleware::class,
            'check.workspaces.exist'      => CheckWorkspacesExistMiddleware::class,
            'check.members.exist'         => CheckMembersExistMiddleware::class,

            // ── Team ──────────────────────────────────────────────────────────
            'team.exists'            => CheckTeamExistsMiddleware::class,
            'team.member.exists'     => CheckTeamMemberExistsMiddleware::class,
            'teams.exist'            => CheckTeamsExistMiddleware::class,
            'team.unique.name'       => CheckUniqueTeamNameMiddleware::class,
            'workspace.creator.team' => CheckWorkspaceCreatorTeamMiddleware::class,
            'workspace.member.team'  => CheckWorkspaceMemberMiddleware::class,

            // ── Message ───────────────────────────────────────────────────────
            'message.channel.check'  => CheckChannelMessageMiddleware::class,
            'message.exists'         => CheckMessageExistsMiddleware::class,
            'message.sender'         => CheckMessageSenderMiddleware::class,
            'message.file.check'     => CheckMessageFileMiddleware::class,
            'message.file.upload'    => CheckMessageFileUploadMiddleware::class,
            'message.read.resolve'   => CheckReadMessagesMiddleware::class,

            // ── Channel ───────────────────────────────────────────────────────
            'channel.exists' => ChannelExistMiddleware::class,
            'channel.admin'  => ChannelAdminMiddleware::class,
            'channel.member' => MemberCheckMiddleware::class,
            'channel.create' => \App\Http\Middleware\Channel\ChannelCreateMiddleware::class,
            'channel.add.member' => \App\Http\Middleware\Channel\ChannelAddMemberMiddleware::class,
            'channel.remove.member' => \App\Http\Middleware\Channel\ChannelRemoveMemberMiddleware::class,

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->notFound('Resource not found.');
            }
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->unauthorized('Unauthenticated. Please login to continue.');
            }
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->forbidden('You do not have permission to perform this action.');
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->validation($e->errors(), 'The given data was invalid.');
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if ($request->is('api/*')) {
                $message = match ($e->getStatusCode()) {
                    404 => 'Resource not found.',
                    403 => 'Forbidden.',
                    401 => 'Unauthorized.',
                    500 => 'Internal server error.',
                    default => $e->getMessage() ?: 'An error occurred.'
                };
                return response()->error($message, $e->getStatusCode());
            }
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                if (app()->environment('production')) {
                    return response()->error('Internal server error.', 500);
                }
                return response()->error($e->getMessage(), 500);
            }
        });
    })->create();
