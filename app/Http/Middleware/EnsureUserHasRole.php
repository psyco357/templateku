<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        if (! $user->hasRole($roles)) {
            return redirect()->route('dashboard')->with([
                'status' => 'Anda tidak memiliki akses ke halaman tersebut.',
                'status_type' => 'info',
            ]);
        }

        return $next($request);
    }
}
