<?php

use App\Exceptions\SafeApiException;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        /**
         * Centralized, safe error responses. Every JSON error response uses this
         * shape and never leaks stack traces, SQL errors, or other internals;
         * the real exception is still logged via the default reporting path.
         */
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! ($request->is('api/*') || $request->expectsJson())) {
                return null;
            }

            if ($e instanceof SafeApiException) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], $e->statusCode);
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json(['success' => false, 'message' => 'You must be signed in to do that.'], 401);
            }

            if ($e instanceof AuthorizationException) {
                return response()->json(['success' => false, 'message' => 'You are not authorized to do that.'], 403);
            }

            if ($e instanceof TooManyRequestsHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => "You're making requests too quickly. Please wait a moment and try again.",
                ], 429);
            }

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return response()->json(['success' => false, 'message' => 'The requested resource could not be found.'], 404);
            }

            if ($e instanceof HttpExceptionInterface) {
                return response()->json(['success' => false, 'message' => 'The request could not be completed.'], $e->getStatusCode());
            }

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while processing your request. Please try again.',
            ], 500);
        });
    })->create();
