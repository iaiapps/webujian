<?php

// app/Http/Controllers/Student/DashboardController.php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\TestAttempt;
use App\Models\TestPackage;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:student');
    }

    public function index()
    {
        $student = Auth::guard('student')->user();

        // Get available tests (ALL tests, not filtered by class)
        // ============================================================
        // KELAS DINONAKTIFKAN - Semua siswa bisa akses semua tes
        // ============================================================
        $availableTests = TestPackage::query()
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            // ->whereHas('classes', function ($q) use ($student) {
            //     $q->where('class_id', $student->class_id);
            // })
            ->whereDoesntHave('testAttempts', function ($q) use ($student) {
                $q->where('student_id', $student->id)
                    ->where('status', 'completed');
            })
            ->with('user')
            ->latest()
            ->get();

        // Get ongoing test
        $ongoingTest = TestAttempt::where('student_id', $student->id)
            ->where('status', 'ongoing')
            ->with('package')
            ->first();

        // Get completed tests history
        $completedTests = TestAttempt::where('student_id', $student->id)
            ->where('status', 'completed')
            ->with('package')
            ->latest()
            ->take(10)
            ->get();

        // Statistics
        $stats = [
            'total_completed' => $student->completedTests()->count(),
            'avg_score' => $student->completedTests()->avg('total_score'),
            'highest_score' => $student->completedTests()->max('total_score'),
            'total_correct' => $student->completedTests()->sum('correct_answers'),
        ];

        return view('student.dashboard', compact(
            'availableTests',
            'ongoingTest',
            'completedTests',
            'stats'
        ));
    }
}
