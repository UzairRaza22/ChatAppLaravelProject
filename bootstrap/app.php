<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
use App\Http\Middleware\Message\CheckReadByMiddleware;
use App\Http\Middleware\Message\CheckMessageReactionMiddleware;
use App\Http\Middleware\Message\SendMessagePushNotificationMiddleware;

// Channel middleware
use App\Http\Middleware\Channel\ChannelExistMiddleware;
use App\Http\Middleware\Channel\ChannelAdminMiddleware;
use App\Http\Middleware\Channel\MemberCheckMiddleware;

use App\Http\Middleware\GlobalActivityLoggerMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        App\Providers\AttachmentServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',

        then: function () {
            Route::middleware(['api', 'throttle:120,1'])
                ->prefix('api')
                ->name('api.')
                ->group(function () {
                    Route::prefix('auth')->group(base_path('routes/auth.php'));
                    Route::prefix('workspaces')->group(base_path('routes/workspaces.php'));
                    Route::prefix('team')->group(base_path('routes/team.php'));
                    Route::prefix('messages')->group(base_path('routes/Messages.php'));
                    Route::prefix('channels')->group(base_path('routes/channel.php'));
                    require base_path('routes/Fcm.php');
                });
        },
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
            'message.search'         => \App\Http\Middleware\Message\SearchMessageMiddleware::class,
            'message.exists'         => CheckMessageExistsMiddleware::class,
            'message.sender'         => CheckMessageSenderMiddleware::class,
            'message.file.check'     => CheckMessageFileMiddleware::class,
            'message.file.upload'    => CheckMessageFileUploadMiddleware::class,
            'message.read.resolve'   => CheckReadMessagesMiddleware::class,
            'message.readby'         => CheckReadByMiddleware::class,
            'message.react'          => CheckMessageReactionMiddleware::class,
            // 'message.notification'   => SendMessagePushNotificationMiddleware::class, // Disabled to fix 500 error

            // ── Channel ───────────────────────────────────────────────────────
            'channel.exists'        => ChannelExistMiddleware::class,
            'channel.admin'         => ChannelAdminMiddleware::class,
            'channel.member'        => MemberCheckMiddleware::class,
            'channel.create'        => \App\Http\Middleware\Channel\ChannelCreateMiddleware::class,
            'channel.add.member'    => \App\Http\Middleware\Channel\ChannelAddMemberMiddleware::class,
            'channel.remove.member' => \App\Http\Middleware\Channel\ChannelRemoveMemberMiddleware::class,

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        /*
        |--------------------------------------------------------------------------
        | Webhook Alerting (Global) — App Errors Only, No Logging
        |--------------------------------------------------------------------------
        | Only fires on staging/production for 500-level errors.
        | Skips all user-caused errors (401, 403, 404, 422, 429).
        | Deduplicates alerts for 3 minutes to avoid spam.
        | ENV: ALERT_WEBHOOK_URL=https://your-webhook-endpoint
        |--------------------------------------------------------------------------
        */
        $exceptions->reportable(function (\Throwable $e) {

            // Only staging/production
            if (!app()->environment(['staging', 'production'])) {
                return;
            }

            // Skip common user-caused errors
            if ($e instanceof \Illuminate\Validation\ValidationException) return;           // 422
            if ($e instanceof \Illuminate\Auth\AuthenticationException) return;             // 401
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) return;       // 403
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) return; // 404

            // Skip HTTP exceptions with status < 500 (404/401/403/405/429 etc.)
            if ($e instanceof HttpExceptionInterface) {
                if ($e->getStatusCode() < 500) return;
            }

            // Deduplicate alerts for 3 minutes
            $dedupeKey = 'webhook_alert_dedupe:' . md5(
                get_class($e) . '|' . $e->getMessage() . '|' . $e->getFile() . ':' . $e->getLine()
            );

            if (Cache::has($dedupeKey)) {
                return;
            }
            Cache::put($dedupeKey, true, 180);

            $webhookUrl = env('ALERT_WEBHOOK_URL');
            if (!$webhookUrl) {
                return;
            }

            $req = request();

            $payload = [
                'app'       => config('app.name'),
                'env'       => app()->environment(),
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'time'      => now()->toIso8601String(),
                'request'   => [
                    'method' => $req?->method(),
                    'url'    => $req?->fullUrl(),
                    'ip'     => $req?->ip(),
                ],
                'user_id' => optional($req?->user())->_id ?? optional($req?->user())->id ?? null,
                'trace'   => array_slice(explode("\n", $e->getTraceAsString()), 0, 10),
            ];

            // Silent failure — never throw from reportable
            try {
                Http::timeout(3)->post($webhookUrl, $payload);
            } catch (\Throwable $ignored) {
                // intentionally silent
            }
        });

        // ── Render Handlers ───────────────────────────────────────────────────

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

        /*
        |--------------------------------------------------------------------------
        | Throttle Exception — HTTP 429
        |--------------------------------------------------------------------------
        | Caught before the generic HttpException handler so we can read
        | Retry-After from the exception headers and include it in the message.
        |--------------------------------------------------------------------------
        */
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*')) {
                $retryAfter = $e->getHeaders()['Retry-After'] ?? 60;
                return response()->error(
                    'Too many requests. Please try again in ' . $retryAfter . ' seconds.',
                    429
                );
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if ($request->is('api/*')) {
                $message = match ($e->getStatusCode()) {
                    404 => 'Resource not found.',
                    403 => 'Forbidden.',
                    401 => 'Unauthorized.',
                    429 => 'Too many requests. Please try again later.',
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
