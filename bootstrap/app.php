<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global security headers on every response
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Exclude Stripe webhook from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);

        // Named middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        // Redirect guests to the appropriate login page
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('oxybliss-admin-panel*')) {
                return route('admin.login');
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // In production, render generic error messages to prevent information leakage
        $exceptions->renderable(function (\Throwable $e, $request) {
            // Check if we are in production or if debug is false
            if (config('app.env') === 'production' || !config('app.debug')) {
                // Determine HTTP status code
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                
                // If it's a 404, let Laravel handle it normally to show the custom 404.blade.php
                if ($status === 404) {
                    return null; // Let the default handler load the 404 view
                }

                if ($request->expectsJson() || $request->is('cart/add/*') || $request->is('cart/update/*') || $request->is('cart/remove/*') || $request->is('wishlist/*')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Something went wrong. Please try again.',
                        // We do not leak details here
                    ], $status === 200 ? 500 : $status); // Ensure failure status
                }

                // For web requests, explicitly show the 500 page instead of letting Laravel render the stack trace
                return response()->view('errors.500', [], 500);
            }
        });
    })->create();

// On Hostinger, the document root is 'public_html' instead of 'public'.
// Override the public path so Vite and other helpers resolve correctly.
$publicHtmlPath = dirname(__DIR__) . '/public_html';
if (is_dir($publicHtmlPath)) {
    $app->usePublicPath($publicHtmlPath);
}

return $app;
