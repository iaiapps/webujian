<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Admin tidak perlu approval
        if ($user->isAdmin()) {
            return $next($request);
        }

        // ============================================================
        // APPROVAL MANUAL DINONAKTIFKAN
        // User yang baru daftar langsung bisa akses tanpa approval admin
        // ============================================================

        // // Cek apakah user sudah di-approve
        // if (!$user->isApproved()) {
        //     auth()->logout();
        //     return redirect()->route('auth.waiting-approval')->with('warning', 'Akun Anda masih menunggu persetujuan admin.');
        // }

        // Cek apakah akun aktif
        if (! $user->is_active) {
            auth()->logout();

            return redirect()->route('login')->with('error', 'Akun Anda telah dinonaktifkan. Hubungi admin untuk informasi lebih lanjut.');
        }

        return $next($request);
    }
}
