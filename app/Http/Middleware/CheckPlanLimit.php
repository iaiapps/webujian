<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanLimit
{
    public function handle(Request $request, Closure $next, string $limitType): Response
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Admin tidak kena limit
        if ($user->isAdmin()) {
            return $next($request);
        }

        // ============================================================
        // SISTEM KREDIT - package limit sekarang menggunakan kredit
        // ============================================================
        $canProceed = match ($limitType) {
            'student' => $user->canAddStudent(),
            'package' => $user->canCreatePackage(), // Cek kredit, bukan max_packages
            'question' => $user->canAddQuestion(),
            // 'class' => $user->canAddClass(), // DINONAKTIFKAN
            default => true,
        };

        if (! $canProceed) {
            $limitName = match ($limitType) {
                'student' => 'siswa',
                'package' => 'paket tes (kredit tidak cukup)',
                'question' => 'soal',
                // 'class' => 'kelas', // DINONAKTIFKAN
                default => 'item',
            };

            // Pesan berbeda untuk package karena menggunakan kredit
            $message = match ($limitType) {
                'package' => 'Kredit Anda tidak cukup untuk membuat paket tes. Silakan beli kredit terlebih dahulu.',
                default => "Anda sudah mencapai batas maksimal {$limitName}. Upgrade untuk menambah {$limitName}.",
            };

            return redirect()->back()->with('limit_reached', [
                'type' => $limitType,
                'limit' => $limitType === 'package' ? $user->credits : $user->{"max_{$limitType}s"},
                'current' => $limitType === 'package' ? $user->packagesCount() : $user->{$limitType.'sCount'}(),
                'message' => $message,
            ]);
        }

        return $next($request);
    }
}
