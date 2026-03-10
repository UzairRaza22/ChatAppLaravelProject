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
use App\Http\Requests\Channel\UpdateChannelRequest;
use App\Http\Requests\Channel\AddMemberRequest;
use App\Http\Requests\Channel\RemoveMemberRequest;
use App\Http\Requests\Channel\ReadChannelRequest;
use App\Http\Requests\Channel\DeleteChannelRequest;

// Message Requests
use App\Http\Requests\Message\SendMessageRequest;
<<<<<<< HEAD
use App\Http\Requests\Message\GetMessagesRequest;
use App\Http\Requests\Message\UpdateMessageRequest;
use App\Http\Requests\Message\DeleteMessageRequest;

=======
use App\Http\Requests\Message\GetDirectMessagesRequest;
use App\Http\Requests\Message\GetChannelMessagesRequest;
use App\Http\Requests\Message\UpdateMessageRequest;
use App\Http\Requests\Message\DeleteMessageRequest;
<<<<<<< HEAD

=======
>>>>>>> 171cca664853ef100f35468bb369b1848fd4e0c4
>>>>>>> f0c3b04a17d517e60fbdbbed4f21ff26a6ecba5e
use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class CheckValidationMiddleware
{
    public function handle(Request $request, Closure $next, $validation_type): Response
    {
<<<<<<< HEAD
        $requestClass = null;

        // Auth-related
        if ($validation_type === 'logout_request') $requestClass = LogoutRequest::class;
        if ($validation_type === 'signup_request') $requestClass = SignupRequest::class;
        if ($validation_type === 'login_request') $requestClass = LoginRequest::class;
        if ($validation_type === 'verify_signup_request') $requestClass = VerifySignupRequest::class;
        if ($validation_type === 'forgot_password_request') $requestClass = ForgotPasswordRequest::class;
        if ($validation_type === 'reset_password_request') $requestClass = ResetPasswordRequest::class;
        
        // Workspace
        if ($validation_type === 'create_workspace_request') $requestClass = CreateWorkspaceRequest::class;
        if ($validation_type === 'update_workspace_request') $requestClass = UpdateWorkspaceRequest::class;
        if ($validation_type === 'add_workspace_member_request') $requestClass = AddWorkspaceMemberRequest::class;
        if ($validation_type === 'remove_workspace_member_request') $requestClass = RemoveWorkspaceMemberRequest::class;
=======
        // ── Auth ──────────────────────────────────────────────────────────
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

        // ── Workspace ─────────────────────────────────────────────────────
        if ($validation_type === 'CreateWorkspaceRequest') {
            $request->validate(app(CreateWorkspaceRequest::class)->rules());
        }
        if ($validation_type === 'UpdateWorkspaceRequest') {
            $request->validate(app(UpdateWorkspaceRequest::class)->rules());
        }
        if ($validation_type === 'AddWorkspaceMemberRequest') {
            $request->validate(app(AddWorkspaceMemberRequest::class)->rules());
        }
        if ($validation_type === 'RemoveWorkspaceMemberRequest') {
            $request->validate(app(RemoveWorkspaceMemberRequest::class)->rules());
        }
<<<<<<< HEAD

        // ── Team ──────────────────────────────────────────────────────────
        if ($validation_type === 'CreateTeamRequest') {
            $request->validate(app(CreateTeamRequest::class)->rules());
        }
        if ($validation_type === 'UpdateTeamRequest') {
            $request->validate(app(UpdateTeamRequest::class)->rules());
        }
        if ($validation_type === 'AddTeamMemberRequest') {
            $request->validate(app(AddTeamMemberRequest::class)->rules());
        }
        if ($validation_type === 'RemoveTeamMemberRequest') {
            $request->validate(app(RemoveTeamMemberRequest::class)->rules());
        }
        if ($validation_type === 'DeleteTeamRequest') {
            $request->validate(app(DeleteTeamRequest::class)->rules());
        }
        if ($validation_type === 'ListTeamRequest') {
            $request->validate(app(ListTeamRequest::class)->rules());
        }

        // ── Channel ───────────────────────────────────────────────────────
        if ($validation_type === 'CreateChannelRequest') {
            $request->validate(app(CreateChannelRequest::class)->rules());
        }
        if ($validation_type === 'UpdateChannelRequest') {
            $request->validate(app(UpdateChannelRequest::class)->rules());
        }
        if ($validation_type === 'AddMemberRequest') {
            $request->validate(app(AddMemberRequest::class)->rules());
        }
        if ($validation_type === 'RemoveMemberRequest') {
            $request->validate(app(RemoveMemberRequest::class)->rules());
        }
        if ($validation_type === 'ReadChannelRequest') {
            $request->validate(app(ReadChannelRequest::class)->rules());
        }
        if ($validation_type === 'DeleteChannelRequest') {
            $request->validate(app(DeleteChannelRequest::class)->rules());
        }

        // ── Message ───────────────────────────────────────────────────────
        if ($validation_type === 'SendMessageRequest') {
            app(SendMessageRequest::class)->validateResolved();
        }
        if ($validation_type === 'GetMessagesRequest') {
            app(GetMessagesRequest::class)->validateResolved();
        }
        if ($validation_type === 'UpdateMessageRequest') {
            $request->validate(app(UpdateMessageRequest::class)->rules());
        }
        if ($validation_type === 'DeleteMessageRequest') {
            $request->validate(app(DeleteMessageRequest::class)->rules());
        }

=======
>>>>>>> f0c3b04a17d517e60fbdbbed4f21ff26a6ecba5e
        
        // Channel
        if ($validation_type === 'create_channel_request') $requestClass = CreateChannelRequest::class;
        if ($validation_type === 'read_channel_request') $requestClass = ReadChannelRequest::class;
        if ($validation_type === 'update_channel_request') $requestClass = UpdateChannelRequest::class;
        if ($validation_type === 'delete_channel_request') $requestClass = DeleteChannelRequest::class;
        if ($validation_type === 'add_channel_member_request') $requestClass = AddMemberRequest::class;
        if ($validation_type === 'remove_channel_member_request') $requestClass = RemoveMemberRequest::class;
        
        // Team
        if ($validation_type === 'create_team_request') $requestClass = CreateTeamRequest::class;
        if ($validation_type === 'update_team_request') $requestClass = UpdateTeamRequest::class;
        if ($validation_type === 'add_team_member_request') $requestClass = AddTeamMemberRequest::class;
        if ($validation_type === 'remove_team_member_request') $requestClass = RemoveTeamMemberRequest::class;
        if ($validation_type === 'delete_team_request') $requestClass = DeleteTeamRequest::class;
        if ($validation_type === 'read_team_request') $requestClass = ReadTeamRequest::class;
        
        // Message
        if ($validation_type === 'send_message_request') $requestClass = SendMessageRequest::class;
        if ($validation_type === 'get_direct_messages_request') $requestClass = GetDirectMessagesRequest::class;
        if ($validation_type === 'get_channel_messages_request') $requestClass = GetChannelMessagesRequest::class;
        if ($validation_type === 'update_message_request') $requestClass = UpdateMessageRequest::class;
        if ($validation_type === 'delete_message_request') $requestClass = DeleteMessageRequest::class;

        // Perform Safe Validation
        if ($requestClass) {
            $instance = new $requestClass();
            $validator = Validator::make(
                $request->all(),
                $instance->rules(),
                method_exists($instance, 'messages') ? $instance->messages() : [],
                method_exists($instance, 'attributes') ? $instance->attributes() : []
            );

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        }
        
>>>>>>> 171cca664853ef100f35468bb369b1848fd4e0c4
        return $next($request);
    }
}