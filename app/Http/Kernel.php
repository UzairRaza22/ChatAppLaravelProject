<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     */
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        // Yahan se GlobalActivityLogger ko hata diya hai taake upar conflict na ho
    ];

    /**
     * The application's route middleware groups.
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            
            // --- UPDATE: Yahan add kiya hai taake auth aur routes sahi track hon ---
            \App\Http\Middleware\GlobalActivityLogger::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     */
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'channel.create' => \App\Http\Middleware\Channel\ChannelCreateMiddleware::class,
        'channel.add.member' => \App\Http\Middleware\Channel\ChannelAddMemberMiddleware::class,
        'channel.remove.member' => \App\Http\Middleware\Channel\ChannelRemoveMemberMiddleware::class,
        'log.activity' => \App\Http\Middleware\GlobalActivityLogger::class,

        // ── Message ───────────────────────────────────────────────────────
        'message.channel.check'  => \App\Http\Middleware\Message\CheckChannelMessageMiddleware::class,
        'message.search'         => \App\Http\Middleware\Message\SearchMessageMiddleware::class,
        'message.exists'         => \App\Http\Middleware\Message\CheckMessageExistsMiddleware::class,
        'message.readby'         => \App\Http\Middleware\Message\CheckReadByMiddleware::class,
        'message.react'          => \App\Http\Middleware\Message\CheckMessageReactionMiddleware::class,
        'message.sender'          => \App\Http\Middleware\Message\Checkmessagesendermiddleware::class,
        'message.file.check'     => \App\Http\Middleware\Message\Checkmessagefilemiddleware::class,
        'message.file.upload'    => \App\Http\Middleware\Message\Checkmessagefileuploadmiddleware::class,
        'message.read'           => \App\Http\Middleware\Message\Checkreadmessagesmiddleware::class,
        'message.notification'   => \App\Http\Middleware\Message\Checkmessagenotificationmiddleware::class,
        'message.validation'     => \App\Http\Middleware\Message\CheckMessageValidationMiddleware::class,
    
    ];
}