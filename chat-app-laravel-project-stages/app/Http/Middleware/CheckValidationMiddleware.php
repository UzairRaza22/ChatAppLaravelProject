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
use App\Http\Requests\Team\ListTeamRequest;

// Channel Requests
use App\Http\Requests\Channel\CreateChannelRequest;
use App\Http\Requests\Channel\UpdateChannelRequest;
use App\Http\Requests\Channel\AddMemberRequest;
use App\Http\Requests\Channel\RemoveMemberRequest;
use App\Http\Requests\Channel\ReadChannelRequest;
use App\Http\Requests\Channel\DeleteChannelRequest;

use Illuminate\Http\Request;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class CheckValidationMiddleware
{
    public function handle(Request $request, Closure $next, $validation_type): Response
    {
        // --- Auth ---
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

        // --- Workspace ---
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

        // --- Team ---
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

        // --- Channel ---
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

        return $next($request);
    }
}