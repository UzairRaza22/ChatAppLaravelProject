<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class ResponseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Success response macro
        Response::macro('success', function ($data = null, $message = "Success", $code = 200) {
            return Response::json([
                'success' => true,
                'message' => $message,
                'errors' => null,
                'data' => $data
            ], $code);
        });

        // Error response macro
        Response::macro('error', function ($message = "Error", $code = 400, $errors = []) {
            return Response::json([
                'success' => false,
                'message' => $message,
                'errors' => $errors,
                'data' => null
            ], $code);
        });

        // Validation error response macro
        Response::macro('validation', function ($errors, $message = "Validation Failed") {
            return Response::json([
                'success' => false,
                'message' => $message,
                'errors' => $errors,
                'data' => null
            ], 422);
        });

        // Unauthorized response macro
        Response::macro('unauthorized', function ($message = "Unauthorized") {
            return Response::json([
                'success' => false,
                'message' => $message,
                'errors' => null,
                'data' => null
            ], 401);
        });

        // Forbidden response macro
        Response::macro('forbidden', function ($message = "Forbidden") {
            return Response::json([
                'success' => false,
                'message' => $message,
                'errors' => null,
                'data' => null
            ], 403);
        });

        // Not found response macro
        Response::macro('notFound', function ($message = "Resource Not Found") {
            return Response::json([
                'success' => false,
                'message' => $message,
                'errors' => null,
                'data' => null
            ], 404);
        });
    }
}
