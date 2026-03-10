<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

<<<<<<< HEAD
// Auth middleware
=======
// Import all middleware classes
>>>>>>> 171cca664853ef100f35468bb369b1848fd4e0c4
use App\Http\Middleware\CheckValidationMiddleware;
use App\Http\Middleware\auth\CheckTokenMiddleware;
use App\Http\Middleware\auth\CheckCredentialsMiddleware;
use App\Http\Middleware\auth\CheckActiveMiddleware;
use App\Http\Middleware\auth\CheckUserExistMiddleware;
use App\Http\Middleware\auth\CheckUserExistForForgotMiddleware;
<<<<<<< HEAD

// Workspace middleware
=======
>>>>>>> 171cca664853ef100f35468bb369b1848fd4e0c4
use App\Http\Middleware\Workspace\CheckUniqueWorkspaceNameMiddleware;
use App\Http\Middleware\Workspace\CheckWorkspaceCreatorMiddleware;
use App\Http\Middleware\Workspace\CheckWorkspaceExistsMiddleware;
use App\Http\Middleware\Workspace\CheckWorkspacesExistMiddleware;
use App\Http\Middleware\Workspace\CheckMembersExistMiddleware;

// Team middleware
use App\Http\Middleware\Team\CheckTeamExistsMiddleware;
use App\Http\Middleware\Team\CheckTeamMemberExistsMiddleware;
use App\Http\Middleware\Team\CheckTeamsExistMiddleware;
use App\Http\Middleware\Team\CheckUniqueTeamNameMiddleware;
use App\Http\Middleware\Team\CheckWorkspaceCreatorTeamMiddleware;
use App\Http\Middleware\Team\CheckWorkspaceMemberMiddleware;

// Message middleware
use App\Http\Middleware\Message\Checkworkspacemembermiddleware as CheckMessageWorkspaceMember;
use App\Http\Middleware\Message\Checkreceiverinworkspacemiddleware as CheckReceiverInWorkspace;
use App\Http\Middleware\Message\Checkchannelinworkspacemiddleware as CheckChannelInWorkspace;
use App\Http\Middleware\Message\Checkmessageexistsmiddleware as CheckMessageExists;
use App\Http\Middleware\Message\Checkmessagesendermiddleware as CheckMessageSender;
use App\Http\Middleware\Message\Checkmessagefilemiddleware as CheckMessageFile;
use App\Http\Middleware\Message\Checkworkspacemembermiddleware as CheckMessageWorkspaceMember;
use App\Http\Middleware\Message\Checkreceiverinworkspacemiddleware as CheckReceiverInWorkspace;
use App\Http\Middleware\Message\Checkchannelinworkspacemiddleware as CheckChannelInWorkspace;
use App\Http\Middleware\Message\Checkmessageexistsmiddleware as CheckMessageExists;
use App\Http\Middleware\Message\Checkmessagesendermiddleware as CheckMessageSender;
use App\Http\Middleware\Message\Checkmessagefilemiddleware as CheckMessageFile;
use App\Http\Middleware\Message\Checkmessagefileuploadmiddleware as CheckMessageFileUpload;  // ← NEW
use App\Http\Middleware\Message\Checkreadmessagesmiddleware as CheckReadMessages;             // ← NEW


// Channel middleware
use App\Http\Middleware\ChannelExistMiddleware;
use App\Http\Middleware\ChannelAdminMiddleware;
use App\Http\Middleware\MemberCheckMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
<<<<<<< HEAD

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

            // ── Team ──────────────────────────────────────────────────────────
            'team.exists'          => CheckTeamExistsMiddleware::class,
            'team.member.exists'   => CheckTeamMemberExistsMiddleware::class,
            'teams.exist'          => CheckTeamsExistMiddleware::class,
            'team.unique.name'     => CheckUniqueTeamNameMiddleware::class,
            'workspace.creator.team' => CheckWorkspaceCreatorTeamMiddleware::class,
            'workspace.member.team'  => CheckWorkspaceMemberMiddleware::class,

            // ── Message ───────────────────────────────────────────────────────
            'message.workspace.member' => CheckMessageWorkspaceMember::class,
            'message.receiver.check'   => CheckReceiverInWorkspace::class,
            'message.channel.check'    => CheckChannelInWorkspace::class,
            'message.exists'           => CheckMessageExists::class,
            'message.sender'           => CheckMessageSender::class,
            'message.file.check'       => CheckMessageFile::class,

            // ── Channel ───────────────────────────────────────────────────────
            'channel.exists' => ChannelExistMiddleware::class,
            'channel.admin'  => ChannelAdminMiddleware::class,
            'channel.member' => MemberCheckMiddleware::class,

=======

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
            'check.members.exist' => CheckMembersExistMiddleware::class,
            
            // Team middleware
            'check.team.exists' => CheckTeamExistsMiddleware::class,
            'check.team.member.exists' => CheckTeamMemberExistsMiddleware::class,
            'check.teams.exist' => CheckTeamsExistMiddleware::class,
            'check.team.unique.name' => CheckUniqueTeamNameMiddleware::class,
            'check.workspace.creator.team' => CheckWorkspaceCreatorTeamMiddleware::class,
            'check.workspace.member.team' => CheckWorkspaceMemberMiddleware::class,
            
            // Message middleware
            'message.workspace.member' => CheckMessageWorkspaceMember::class,
            'message.receiver.check'   => CheckReceiverInWorkspace::class,
            'message.channel.check'    => CheckChannelInWorkspace::class,
            'message.exists'           => CheckMessageExists::class,
            'message.sender'           => CheckMessageSender::class,
            'message.file.check'       => CheckMessageFile::class,
            'message.file.upload'      => CheckMessageFileUpload::class,  // ← NEW
            'message.read.resolve'     => CheckReadMessages::class,        // ← NEW
            
            // Channel middleware
            'check.channel.exists' => ChannelExistMiddleware::class,
            'check.channel.admin' => ChannelAdminMiddleware::class,
            'check.channel.member' => MemberCheckMiddleware::class,
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
                $message = match ($e->getStatusCode()) {
                    404 => 'Resource not found.',
                    403 => 'Forbidden.',
                    401 => 'Unauthorized.',
                    500 => 'Internal server error.',
                    default => $e->getMessage() ?: 'An error occurred.'
                };
<<<<<<< HEAD
=======

>>>>>>> 171cca664853ef100f35468bb369b1848fd4e0c4
                return response()->error($message, $e->getStatusCode());
            }
        });

        // Handle any other Throwable
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                if (app()->environment('production')) {
                    return response()->error('Internal server error.', 500);
                }
<<<<<<< HEAD
=======

                // In development, return more details
>>>>>>> 171cca664853ef100f35468bb369b1848fd4e0c4
                return response()->error($e->getMessage(), 500);
            }
        });
    })->create();
