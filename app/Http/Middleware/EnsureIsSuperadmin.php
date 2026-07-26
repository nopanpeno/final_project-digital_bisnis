<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsSuperadmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            abort(403, 'Anda harus login terlebih dahulu.');
        }

        if (!auth()->user()->isSuperadmin()) {
            abort(403, 'Halaman ini khusus untuk superadmin.');
        }

        return $next($request);
    }
}