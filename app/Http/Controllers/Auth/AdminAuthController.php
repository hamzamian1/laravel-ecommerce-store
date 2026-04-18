<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AdminLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    /**
     * Display the admin login view.
     */
    public function create(Request $request): View|RedirectResponse
    {
        // If the user is already logged in as an admin, send them to the dashboard.
        if ($request->user() && $request->user()->isAdmin()) {
            return redirect()->route('dashboard');
        }

        // If a normal customer accidentally (or intentionally) opens the admin URL
        // while logged in, we MUST log them out in order to show the Admin Login form.
        // Otherwise they get stuck in a redirect loop to the homepage.
        if ($request->user() && !$request->user()->isAdmin()) {
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        
        return view('auth.admin_login');
    }

    /**
     * Handle an incoming admin authentication request.
     */
    public function store(AdminLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $request->session()->save();

        return redirect()->route('dashboard');
    }
}
