<?php

// app/Http/Controllers/Guru/DashboardController.php
namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\Question;
use App\Models\TestPackage;
use App\Models\TestAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:guru', 'check.approved']);
    }

    public function index()
    {
        $user = auth()->user();

        // Auto-downgrade jika plan expired
        if ($user->plan !== 'free' && $user->isPlanExpired()) {
            $user->update(['plan' => 'free', 'plan_expired_at' => null]);
            $user->refresh();
        }

        // Statistics
        $stats = [
            'total_students' => $user->studentsCount(),
            'max_students' => $user->max_students,
            'total_classes' => $user->classesCount(),
            'max_classes' => $user->max_classes,
            'total_questions' => $user->questionsCount(),
            'max_questions' => $user->max_questions,
            'total_packages' => $user->packagesCount(),
            'max_packages' => $user->max_packages,
        ];

        // Calculate usage percentage
        $usage = [
            'students' => $user->max_students > 0 ? round(($stats['total_students'] / $user->max_students) * 100) : 0,
            'classes' => $user->max_classes > 0 ? round(($stats['total_classes'] / $user->max_classes) * 100) : 0,
            'questions' => $user->max_questions > 0 ? round(($stats['total_questions'] / $user->max_questions) * 100) : 0,
            'packages' => $user->max_packages > 0 ? round(($stats['total_packages'] / $user->max_packages) * 100) : 0,
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

        // Plan info
        $planInfo = [
            'current_plan' => $user->plan,
            'expired_at' => $user->plan_expired_at,
            'is_expired' => $user->isPlanExpired(),
            'days_remaining' => $user->plan_expired_at ? now()->diffInDays($user->plan_expired_at, false) : null,
        ];

        // Pending subscription
        $pendingSubscription = $user->subscriptions()
            ->where('status', 'pending')
            ->latest()
            ->first();

        // Check over limit (hanya untuk plan free)
        $overLimit = [];
        if ($user->plan === 'free') {
            if ($stats['total_students'] > $stats['max_students']) {
                $overLimit[] = "Siswa: {$stats['total_students']} dari maksimal {$stats['max_students']}";
            }
            if ($stats['total_classes'] > $stats['max_classes']) {
                $overLimit[] = "Kelas: {$stats['total_classes']} dari maksimal {$stats['max_classes']}";
            }
            if ($stats['total_questions'] > $stats['max_questions']) {
                $overLimit[] = "Soal: {$stats['total_questions']} dari maksimal {$stats['max_questions']}";
            }
            if ($stats['total_packages'] > $stats['max_packages']) {
                $overLimit[] = "Paket Tes: {$stats['total_packages']} dari maksimal {$stats['max_packages']}";
            }
        }

        return view('guru.dashboard', compact(
            'stats',
            'usage',
            'recentStudents',
            'recentPackages',
            'activeTests',
            'totalAttempts',
            'avgScore',
            'planInfo',
            'pendingSubscription',
            'overLimit'
        ));
    }
}
