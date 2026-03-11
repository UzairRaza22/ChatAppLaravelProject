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

use App\Http\Middleware\Team\CheckTeamExistsMiddleware;
use App\Http\Middleware\Team\CheckTeamMemberExistsMiddleware;
use App\Http\Middleware\Team\CheckTeamsExistMiddleware;
use App\Http\Middleware\Team\CheckUniqueTeamNameMiddleware;
use App\Http\Middleware\Team\CheckWorkspaceCreatorTeamMiddleware;
use App\Http\Middleware\Team\CheckWorkspaceMemberMiddleware;

use App\Http\Middleware\Message\Checkworkspacemembermiddleware as CheckMessageWorkspaceMember;
use App\Http\Middleware\Message\Checkreceiverinworkspacemiddleware as CheckReceiverInWorkspace;
use App\Http\Middleware\Message\Checkchannelinworkspacemiddleware as CheckChannelInWorkspace;
use App\Http\Middleware\Message\Checkmessageexistsmiddleware as CheckMessageExists;
use App\Http\Middleware\Message\Checkmessagesendermiddleware as CheckMessageSender;
use App\Http\Middleware\Message\Checkmessagefilemiddleware as CheckMessageFile;

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
            'check.validation'            => CheckValidationMiddleware::class,
            'check.token'                 => CheckTokenMiddleware::class,
            'check.credentials'           => CheckCredentialsMiddleware::class,
            'check.active'                => CheckActiveMiddleware::class,
            'check.user.exists'           => CheckUserExistMiddleware::class,
            'check.user.exists.forgot'    => CheckUserExistForForgotMiddleware::class,
            'check.workspace.unique.name' => CheckUniqueWorkspaceNameMiddleware::class,
            'check.workspace.creator'     => CheckWorkspaceCreatorMiddleware::class,
            'check.workspace.exists'      => CheckWorkspaceExistsMiddleware::class,
            'check.workspaces.exist'      => CheckWorkspacesExistMiddleware::class,
            'team.exists'                 => CheckTeamExistsMiddleware::class,
            'team.member.exists'          => CheckTeamMemberExistsMiddleware::class,
            'teams.exist'                 => CheckTeamsExistMiddleware::class,
            'team.unique.name'            => CheckUniqueTeamNameMiddleware::class,
            'workspace.creator.team'      => CheckWorkspaceCreatorTeamMiddleware::class,
            'workspace.member.team'       => CheckWorkspaceMemberMiddleware::class,
            'message.workspace.member'    => CheckMessageWorkspaceMember::class,
            'message.receiver.check'      => CheckReceiverInWorkspace::class,
            'message.channel.check'       => CheckChannelInWorkspace::class,
            'message.exists'              => CheckMessageExists::class,
            'message.sender'              => CheckMessageSender::class,
            'message.file.check'          => CheckMessageFile::class,
            'channel.exists'              => ChannelExistMiddleware::class,
            'channel.admin'               => ChannelAdminMiddleware::class,
            'channel.member'              => MemberCheckMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Exceptions logic yahan aayega
    })->create();