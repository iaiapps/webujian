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

        $canProceed = match ($limitType) {
            'student' => $user->canAddStudent(),
            'package' => $user->canAddPackage(),
            'question' => $user->canAddQuestion(),
            // 'class' => $user->canAddClass(), // DINONAKTIFKAN
            default => true,
        };

        if (! $canProceed) {
            $limitName = match ($limitType) {
                'student' => 'siswa',
                'package' => 'paket tes',
                'question' => 'soal',
                // 'class' => 'kelas', // DINONAKTIFKAN
                default => 'item',
            };

            return redirect()->back()->with('limit_reached', [
                'type' => $limitType,
                'limit' => $user->{"max_{$limitType}s"},
                'current' => $user->{$limitType.'sCount'}(),
                'message' => "Anda sudah mencapai batas maksimal {$limitName} untuk plan {$user->plan}. Upgrade plan untuk menambah {$limitName}.",
            ]);
        }

        return $next($request);
    }
}
