<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * Only allow users with 'admin' role to access admin routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            if ($request->user() && $request->user()->role !== 'admin') {
                \Illuminate\Support\Facades\Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
            return redirect()->route('admin.login')->withErrors(['email' => 'Unauthorized. Admin login required.']);
        }

        return $next($request);
    }
}
