<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->isPrivileged()) {
            return $next($request);
        }

        if ($user) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Cette zone est réservée aux administrateurs CapAcademia.');
        }

        return redirect()->route('login');
    }
}
