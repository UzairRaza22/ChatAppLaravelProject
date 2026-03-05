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
        
        return $next($request);
    }
}
