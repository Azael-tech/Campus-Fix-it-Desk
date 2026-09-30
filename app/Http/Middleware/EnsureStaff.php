<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role !== 'staff') {
            return redirect()
                ->route('reports.index')
                ->with('error', 'Only maintenance staff can do that. Log in with a staff account to continue.');
        }

        return $next($request);
    }
}
