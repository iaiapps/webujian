<?php

// app/Http/Controllers/Guru/DashboardController.php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\TestAttempt;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:guru', 'check.approved']);
    }

    public function index()
    {
        $user = auth()->user();

        // ============================================================
        // SISTEM KREDIT - Tidak ada auto-downgrade plan
        // ============================================================

        // Statistics (KELAS DINONAKTIFKAN - tidak ditampilkan)
        $stats = [
            'total_students' => $user->studentsCount(),
            'max_students' => $user->max_students,
            // 'total_classes' => $user->classesCount(),
            // 'max_classes' => $user->max_classes,
            'total_questions' => $user->questionsCount(),
            'max_questions' => $user->max_questions,
            'total_packages' => $user->packagesCount(),
            'credits' => $user->credits,
        ];

        // Calculate usage percentage
        $usage = [
            'students' => $user->max_students > 0 ? round(($stats['total_students'] / $user->max_students) * 100) : 0,
            // 'classes' => $user->max_classes > 0 ? round(($stats['total_classes'] / $user->max_classes) * 100) : 0,
            'questions' => $user->max_questions > 0 ? round(($stats['total_questions'] / $user->max_questions) * 100) : 0,
        ];

        // Recent activities
        $recentStudents = $user->students()
            ->latest()
            ->take(5)
            ->get();

        $recentPackages = $user->testPackages()
            ->latest()
            ->take(5)
            ->get();

        // Active tests (ongoing)
        $activeTests = $user->testPackages()
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->withCount('testAttempts')
            ->get();

        // Test statistics
        $totalAttempts = TestAttempt::whereHas('package', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('status', 'completed')->count();

        $avgScore = TestAttempt::whereHas('package', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('status', 'completed')->avg('total_score');

        // ============================================================
        // SISTEM KREDIT - Ganti planInfo dengan creditInfo
        // ============================================================
        $creditInfo = [
            'current_credits' => $user->credits,
            'can_create_package' => $user->canCreatePackage(),
        ];

        // Pending subscription - tidak lagi digunakan
        $pendingSubscription = null;

        // Check over limit - tidak ada lagi karena pakai kredit
        $overLimit = [];
        if ($stats['total_students'] > $stats['max_students']) {
            $overLimit[] = "Siswa: {$stats['total_students']} dari maksimal {$stats['max_students']}";
        }
        if ($stats['total_questions'] > $stats['max_questions']) {
            $overLimit[] = "Soal: {$stats['total_questions']} dari maksimal {$stats['max_questions']}";
        }
        // Paket tidak ada limit, pakai kredit
        // if ($stats['total_packages'] > $stats['max_packages']) {
        //     $overLimit[] = "Paket Tes: {$stats['total_packages']} dari maksimal {$stats['max_packages']}";
        // }

        return view('guru.dashboard', compact(
            'stats',
            'usage',
            'recentStudents',
            'recentPackages',
            'activeTests',
            'totalAttempts',
            'avgScore',
            'creditInfo',
            'pendingSubscription',
            'overLimit'
        ));
    }
}
