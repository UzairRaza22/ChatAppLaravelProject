<?php

namespace App\Http\Middleware;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\SignupRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\VerifySignupRequest;

// Workspace Requests
use App\Http\Requests\Workspace\CreateWorkspaceRequest;
use App\Http\Requests\Workspace\UpdateWorkspaceRequest;
use App\Http\Requests\Workspace\AddWorkspaceMemberRequest;
use App\Http\Requests\Workspace\RemoveWorkspaceMemberRequest;

// Team Requests
use App\Http\Requests\Team\CreateTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Http\Requests\Team\AddTeamMemberRequest;
use App\Http\Requests\Team\RemoveTeamMemberRequest;
use App\Http\Requests\Team\DeleteTeamRequest;
use App\Http\Requests\Team\ReadTeamRequest;

// Channel Requests
use App\Http\Requests\Channel\CreateChannelRequest;
use App\Http\Requests\Channel\ReadChannelRequest;
use App\Http\Requests\Channel\UpdateChannelRequest;
use App\Http\Requests\Channel\DeleteChannelRequest;
use App\Http\Requests\Channel\AddMemberRequest;
use App\Http\Requests\Channel\RemoveMemberRequest;
use App\Http\Requests\Channel\ListUserChannelsRequest;

// Message Requests
use App\Http\Requests\Message\SendMessageRequest;
use App\Http\Requests\Message\GetDirectMessagesRequest;
use App\Http\Requests\Message\GetChannelMessagesRequest;
use App\Http\Requests\Message\UpdateMessageRequest;
use App\Http\Requests\Message\DeleteMessageRequest;

use Illuminate\Http\Request;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class CheckValidationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $validation_type): Response
    {
        // Auth
        if ($validation_type === 'logout_request') {
            app(LogoutRequest::class);
        }
        if ($validation_type === 'signup_request') {
            app(SignupRequest::class);
        }
        if ($validation_type === 'login_request') {
            app(LoginRequest::class);
        }
        if ($validation_type === 'verify_signup_request') {
            app(VerifySignupRequest::class);
        }
        if ($validation_type === 'forgot_password_request') {
            app(ForgotPasswordRequest::class);
        }
        if ($validation_type === 'reset_password_request') {
            app(ResetPasswordRequest::class);
        }

        // Workspace
        if ($validation_type === 'create_workspace_request') {
            app(CreateWorkspaceRequest::class);
        }
        if ($validation_type === 'update_workspace_request') {
            app(UpdateWorkspaceRequest::class);
        }
        if ($validation_type === 'add_workspace_member_request') {
            app(AddWorkspaceMemberRequest::class);
        }
        if ($validation_type === 'remove_workspace_member_request') {
            app(RemoveWorkspaceMemberRequest::class);
        }

        // Channel
        if ($validation_type === 'create_channel_request') {
            app(CreateChannelRequest::class);
        }
        if ($validation_type === 'read_channel_request') {
            app(ReadChannelRequest::class);
        }
        if ($validation_type === 'update_channel_request') {
            app(UpdateChannelRequest::class);
        }
        if ($validation_type === 'delete_channel_request') {
            app(DeleteChannelRequest::class);
        }
        if ($validation_type === 'add_channel_member_request') {
            app(AddMemberRequest::class);
        }
        if ($validation_type === 'remove_channel_member_request') {
            app(RemoveMemberRequest::class);
        }
        if ($validation_type === 'list_user_channels_request') {
            app(ListUserChannelsRequest::class);
        }

        // Team
        if ($validation_type === 'create_team_request') {
            app(CreateTeamRequest::class);
        }
        if ($validation_type === 'update_team_request') {
            app(UpdateTeamRequest::class);
        }
        if ($validation_type === 'add_team_member_request') {
            app(AddTeamMemberRequest::class);
        }
        if ($validation_type === 'remove_team_member_request') {
            app(RemoveTeamMemberRequest::class);
        }
        if ($validation_type === 'delete_team_request') {
            app(DeleteTeamRequest::class);
        }
        if ($validation_type === 'read_team_request') {
            app(ReadTeamRequest::class);
        }

        // Message
        if ($validation_type === 'send_message_request') {
            app(SendMessageRequest::class);
        }
        if ($validation_type === 'get_direct_messages_request') {
            app(GetDirectMessagesRequest::class);
        }
        if ($validation_type === 'get_channel_messages_request') {
            app(GetChannelMessagesRequest::class);
        }
        if ($validation_type === 'update_message_request') {
            app(UpdateMessageRequest::class);
        }
        if ($validation_type === 'delete_message_request') {
            app(DeleteMessageRequest::class);
        }

        return $next($request);
    }
}