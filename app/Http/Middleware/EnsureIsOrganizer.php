<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsOrganizer
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            abort(403, 'Anda harus login terlebih dahulu.');
        }

        if (!auth()->user()->isOrganizer()) {
            abort(403, 'Halaman ini khusus untuk organizer.');
        }

        // Bonus: cek status organizer harus approved dulu
        $organizer = auth()->user()->organizer;
        if (!$organizer || $organizer->status !== 'approved') {
            abort(403, 'Akun organizer Anda belum disetujui atau sedang ditangguhkan.');
        }

        return $next($request);
    }
}