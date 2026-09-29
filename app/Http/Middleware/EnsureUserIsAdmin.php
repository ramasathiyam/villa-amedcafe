<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards every /admin/* route except the login screen itself. Deliberately simple, no
 * Gate/Policy layer — a single role check is enough for the one 'admin' role this
 * project needs.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('admin.login');
        }

        if (Auth::user()->role !== 'admin') {
            // An authenticated non-admin session has no legitimate use here — log it out
            // rather than just bouncing the request, so it can't keep hitting this guard.
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->withErrors([
                'email' => 'You do not have access to the admin area.',
            ]);
        }

        return $next($request);
    }
}
