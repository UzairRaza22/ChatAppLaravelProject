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
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class CheckValidationMiddleware
{
    public function handle(Request $request, Closure $next, $validation_type): Response
    {
        $requestClass = null;

        // Auth
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

        // Channel
        if ($validation_type === 'create_channel_request') $requestClass = CreateChannelRequest::class;
        if ($validation_type === 'read_channel_request') $requestClass = ReadChannelRequest::class;
        if ($validation_type === 'update_channel_request') $requestClass = UpdateChannelRequest::class;
        if ($validation_type === 'delete_channel_request') $requestClass = DeleteChannelRequest::class;
        if ($validation_type === 'add_channel_member_request') $requestClass = AddMemberRequest::class;
        if ($validation_type === 'remove_channel_member_request') $requestClass = RemoveMemberRequest::class;
        if ($validation_type === 'ListUserChannelsRequest') {

            $request->validate(app(ListUserChannelsRequest::class)->rules());

        }

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

        // Perform Validation
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
