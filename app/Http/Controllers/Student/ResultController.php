<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\TestAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:student');
    }

    public function show(TestAttempt $attempt)
    {
        $student = Auth::guard('student')->user();

        // Check ownership
        if ($attempt->student_id !== $student->id) {
            abort(403);
        }

        // Check if completed
        if (!$attempt->isCompleted()) {
            return redirect()->route('student.test.work', $attempt)
                ->with('info', 'Selesaikan tes terlebih dahulu.');
        }

        $package = $attempt->package;

        // Check if show result is enabled
        if (!$package->show_result) {
            return redirect()->route('student.dashboard')
                ->with('info', 'Hasil tes tidak dapat ditampilkan.');
        }

        // Get ranking if enabled
        $ranking = null;
        if ($package->show_ranking) {
            $ranking = TestAttempt::where('package_id', $package->id)
                ->where('status', 'completed')
                ->orderBy('total_score', 'desc')
                ->pluck('student_id')
                ->search($student->id);

            $ranking = $ranking !== false ? $ranking + 1 : null;
        }

        $totalAttempts = TestAttempt::where('package_id', $package->id)
            ->where('status', 'completed')
            ->count();

        // Get answers with questions
        $attempt->load(['answers.question.category']);

        return view('student.test.result', compact('attempt', 'package', 'ranking', 'totalAttempts'));
    }

    public function history()
    {
        $student = Auth::guard('student')->user();

        $attempts = TestAttempt::where('student_id', $student->id)
            ->where('status', 'completed')
            ->with('package')
            ->latest()
            ->paginate(10);

        return view('student.test.history', compact('attempts'));
    }

    public function review(TestAttempt $attempt)
    {
        $student = Auth::guard('student')->user();

        // Check ownership
        if ($attempt->student_id !== $student->id) {
            abort(403);
        }

        // Check if completed
        if (!$attempt->isCompleted()) {
            return redirect()->route('student.test.work', $attempt);
        }

        $package = $attempt->package;

        // Check if show explanation is enabled
        if (!$package->show_explanation) {
            return redirect()->route('student.test.result', $attempt)
                ->with('info', 'Pembahasan tidak tersedia untuk tes ini.');
        }

        // Load questions with answers
        $attempt->load(['answers.question.category']);

        // Get questions
        $questions = $package->questions()->with('category')->get();

        // Map answers to questions
        $answersMap = $attempt->answers->keyBy('question_id');

        return view('student.test.review', compact('attempt', 'package', 'questions', 'answersMap'));
    }
}
