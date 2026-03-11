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
use App\Http\Requests\Message\GetMessagesRequest;
use App\Http\Requests\Message\GetDirectMessagesRequest;
use App\Http\Requests\Message\GetChannelMessagesRequest;
use App\Http\Requests\Message\UpdateMessageRequest;
use App\Http\Requests\Message\DeleteMessageRequest;

use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class CheckValidationMiddleware
{
    public function handle(Request $request, Closure $next, $validation_type): Response
    {
        $requestClass = null;

        // ── Auth ──────────────────────────────────────────────────────────
        if ($validation_type === 'logout_request') $requestClass = LogoutRequest::class;
        if ($validation_type === 'signup_request') $requestClass = SignupRequest::class;
        if ($validation_type === 'login_request') $requestClass = LoginRequest::class;
        if ($validation_type === 'verify_signup_request') $requestClass = VerifySignupRequest::class;
        if ($validation_type === 'forgot_password_request') $requestClass = ForgotPasswordRequest::class;
        if ($validation_type === 'reset_password_request') $requestClass = ResetPasswordRequest::class;
        
        // ── Workspace ─────────────────────────────────────────────────────
        if (in_array($validation_type, ['create_workspace_request', 'CreateWorkspaceRequest'])) $requestClass = CreateWorkspaceRequest::class;
        if (in_array($validation_type, ['update_workspace_request', 'UpdateWorkspaceRequest'])) $requestClass = UpdateWorkspaceRequest::class;
        if (in_array($validation_type, ['add_workspace_member_request', 'AddWorkspaceMemberRequest'])) $requestClass = AddWorkspaceMemberRequest::class;
        if (in_array($validation_type, ['remove_workspace_member_request', 'RemoveWorkspaceMemberRequest'])) $requestClass = RemoveWorkspaceMemberRequest::class;

        // ── Team ──────────────────────────────────────────────────────────
        if (in_array($validation_type, ['create_team_request', 'CreateTeamRequest'])) $requestClass = CreateTeamRequest::class;
        if (in_array($validation_type, ['update_team_request', 'UpdateTeamRequest'])) $requestClass = UpdateTeamRequest::class;
        if (in_array($validation_type, ['add_team_member_request', 'AddTeamMemberRequest'])) $requestClass = AddTeamMemberRequest::class;
        if (in_array($validation_type, ['remove_team_member_request', 'RemoveTeamMemberRequest'])) $requestClass = RemoveTeamMemberRequest::class;
        if (in_array($validation_type, ['delete_team_request', 'DeleteTeamRequest'])) $requestClass = DeleteTeamRequest::class;
        if (in_array($validation_type, ['read_team_request', 'ReadTeamRequest', 'ListTeamRequest'])) $requestClass = ReadTeamRequest::class;

        // ── Channel ───────────────────────────────────────────────────────
        if (in_array($validation_type, ['create_channel_request', 'CreateChannelRequest'])) $requestClass = CreateChannelRequest::class;
        if (in_array($validation_type, ['read_channel_request', 'ReadChannelRequest'])) $requestClass = ReadChannelRequest::class;
        if (in_array($validation_type, ['update_channel_request', 'UpdateChannelRequest'])) $requestClass = UpdateChannelRequest::class;
        if (in_array($validation_type, ['delete_channel_request', 'DeleteChannelRequest'])) $requestClass = DeleteChannelRequest::class;
        if (in_array($validation_type, ['add_channel_member_request', 'AddMemberRequest'])) $requestClass = AddMemberRequest::class;
        if (in_array($validation_type, ['remove_channel_member_request', 'RemoveMemberRequest'])) $requestClass = RemoveMemberRequest::class;

        // ── Message ───────────────────────────────────────────────────────
        if (in_array($validation_type, ['send_message_request', 'SendMessageRequest'])) $requestClass = SendMessageRequest::class;
        if (in_array($validation_type, ['get_direct_messages_request', 'GetDirectMessagesRequest'])) $requestClass = GetDirectMessagesRequest::class;
        if (in_array($validation_type, ['get_channel_messages_request', 'GetChannelMessagesRequest'])) $requestClass = GetChannelMessagesRequest::class;
        if (in_array($validation_type, ['get_messages_request', 'GetMessagesRequest'])) $requestClass = GetMessagesRequest::class;
        if (in_array($validation_type, ['update_message_request', 'UpdateMessageRequest'])) $requestClass = UpdateMessageRequest::class;
        if (in_array($validation_type, ['delete_message_request', 'DeleteMessageRequest'])) $requestClass = DeleteMessageRequest::class;

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

        return $next($request);
    }
}