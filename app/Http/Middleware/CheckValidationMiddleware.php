<?php

namespace App\Http\Middleware;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\SignupRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\VerifySignupRequest;
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
use App\Http\Requests\Team\ListTeamRequest;

// Channel Requests
use App\Http\Requests\Channel\CreateChannelRequest;
use App\Http\Requests\Channel\UpdateChannelRequest;
use App\Http\Requests\Channel\AddMemberRequest;
use App\Http\Requests\Channel\RemoveMemberRequest;
use App\Http\Requests\Channel\ReadChannelRequest;
use App\Http\Requests\Channel\DeleteChannelRequest;

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
        // Auth-related validation requests only
        if ($validation_type === 'logout_request') {
            $request->validate(app(LogoutRequest::class)->rules());
        }
        if ($validation_type === 'signup_request') {
            $request->validate(app(SignupRequest::class)->rules());
        }
        if ($validation_type === 'login_request') {
            $request->validate(app(LoginRequest::class)->rules());
        }
        if ($validation_type === 'verify_signup_request') {
            $request->validate(app(VerifySignupRequest::class)->rules());
        }
        if ($validation_type === 'forgot_password_request') {
            $request->validate(app(ForgotPasswordRequest::class)->rules());
        }
        if ($validation_type === 'reset_password_request') {
            $request->validate(app(ResetPasswordRequest::class)->rules());
        }
        
        // Workspace validation requests (only for POST/PUT/PATCH)
        if ($validation_type === 'create_workspace_request') {
            $request->validate(app(CreateWorkspaceRequest::class)->rules());
        }
        if ($validation_type === 'update_workspace_request') {
            $request->validate(app(UpdateWorkspaceRequest::class)->rules());
        }
        if ($validation_type === 'add_workspace_member_request') {
            $request->validate(app(AddWorkspaceMemberRequest::class)->rules());
        }
        if ($validation_type === 'remove_workspace_member_request') {
            $request->validate(app(RemoveWorkspaceMemberRequest::class)->rules());
        }
        
        // Channel validation requests
        if ($validation_type === 'create_channel_request') {
            $request->validate(app(\App\Http\Requests\Channel\CreateChannelRequest::class)->rules());
        }
        if ($validation_type === 'read_channel_request') {
            $request->validate(app(\App\Http\Requests\Channel\ReadChannelRequest::class)->rules());
        }
        if ($validation_type === 'update_channel_request') {
            $request->validate(app(\App\Http\Requests\Channel\UpdateChannelRequest::class)->rules());
        }
        if ($validation_type === 'delete_channel_request') {
            $request->validate(app(\App\Http\Requests\Channel\DeleteChannelRequest::class)->rules());
        }
        if ($validation_type === 'add_channel_member_request') {
            $request->validate(app(\App\Http\Requests\Channel\AddMemberRequest::class)->rules());
        }
        if ($validation_type === 'remove_channel_member_request') {
            $request->validate(app(\App\Http\Requests\Channel\RemoveMemberRequest::class)->rules());
        }
        
        // Team validation requests
        if ($validation_type === 'create_team_request') {
            $request->validate(app(CreateTeamRequest::class)->rules());
        }
        if ($validation_type === 'update_team_request') {
            $request->validate(app(UpdateTeamRequest::class)->rules());
        }
        if ($validation_type === 'add_team_member_request') {
            $request->validate(app(AddTeamMemberRequest::class)->rules());
        }
        if ($validation_type === 'remove_team_member_request') {
            $request->validate(app(RemoveTeamMemberRequest::class)->rules());
        }
        if ($validation_type === 'delete_team_request') {
            $request->validate(app(DeleteTeamRequest::class)->rules());
        }
        if ($validation_type === 'list_team_request') {
            $request->validate(app(ListTeamRequest::class)->rules());
        }
        
        // Message validation requests
        if ($validation_type === 'send_message_request') {
            $request->validate(app(SendMessageRequest::class)->rules());
        }
        if ($validation_type === 'get_direct_messages_request') {
            $request->validate(app(GetDirectMessagesRequest::class)->rules());
        }
        if ($validation_type === 'get_channel_messages_request') {
            $request->validate(app(GetChannelMessagesRequest::class)->rules());
        }
        if ($validation_type === 'update_message_request') {
            $request->validate(app(UpdateMessageRequest::class)->rules());
        }
        if ($validation_type === 'delete_message_request') {
            $request->validate(app(DeleteMessageRequest::class)->rules());
        }
        
        return $next($request);
    }
}
