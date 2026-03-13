<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\TestAttempt;
use App\Models\TestPackage;
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

        // Check if completed or flagged (both can view results)
        if (! $attempt->isCompleted() && ! $attempt->is_flagged) {
            return redirect()->route('student.test.work', $attempt)
                ->with('info', 'Selesaikan tes terlebih dahulu.');
        }

        $package = $attempt->package;

        // Check if show result is enabled
        if (! $package->show_result) {
            return redirect()->route('student.dashboard')
                ->with('info', 'Hasil tes tidak dapat ditampilkan.');
        }

        // Get ranking if enabled
        $ranking = null;
        $leaderboard = null;
        if ($package->show_ranking) {
            $ranking = TestAttempt::where('package_id', $package->id)
                ->where('status', 'completed')
                ->orderBy('total_score', 'desc')
                ->pluck('student_id')
                ->search($student->id);

            $ranking = $ranking !== false ? $ranking + 1 : null;

            // Get leaderboard top 10
            $leaderboard = TestAttempt::where('package_id', $package->id)
                ->where('status', 'completed')
                ->with('student:id,name')
                ->orderBy('total_score', 'desc')
                ->orderBy('submitted_at', 'asc')
                ->limit(10)
                ->get()
                ->map(function ($attempt, $index) {
                    return [
                        'rank' => $index + 1,
                        'name' => $attempt->student->name,
                        'score' => $attempt->total_score,
                        'submitted_at' => $attempt->submitted_at,
                        'duration' => $attempt->start_time && $attempt->submitted_at
                            ? $attempt->submitted_at->diffInMinutes($attempt->start_time)
                            : null,
                    ];
                });
        }

        $totalAttempts = TestAttempt::where('package_id', $package->id)
            ->where('status', 'completed')
            ->count();

        // Get answers with questions
        $attempt->load(['answers.question.category']);

        return view('student.test.result', compact('attempt', 'package', 'ranking', 'totalAttempts', 'leaderboard'));
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
        if (! $attempt->isCompleted()) {
            return redirect()->route('student.test.work', $attempt);
        }

        $package = $attempt->package;

        // Check if show explanation is enabled
        if (! $package->show_explanation) {
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

    public function leaderboard(TestPackage $package)
    {
        $student = Auth::guard('student')->user();

        $leaderboard = TestAttempt::where('package_id', $package->id)
            ->where('status', 'completed')
            ->with('student:id,name')
            ->orderBy('total_score', 'desc')
            ->orderBy('submitted_at', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($attempt, $index) {
                return [
                    'rank' => $index + 1,
                    'name' => $attempt->student->name,
                    'score' => number_format($attempt->total_score, 1),
                    'duration' => $attempt->start_time && $attempt->submitted_at
                        ? $attempt->submitted_at->diffInMinutes($attempt->start_time)
                        : null,
                ];
            });

        $userRank = TestAttempt::where('package_id', $package->id)
            ->where('status', 'completed')
            ->orderBy('total_score', 'desc')
            ->pluck('student_id')
            ->search($student->id);

        $userRank = $userRank !== false ? $userRank + 1 : null;

        return response()->json([
            'leaderboard' => $leaderboard,
            'userRank' => $userRank,
        ]);
    }

    public function recordViolation(Request $request, TestAttempt $attempt)
    {
        $student = Auth::guard('student')->user();

        // Check ownership
        if ($attempt->student_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if already flagged
        if ($attempt->is_flagged) {
            return response()->json([
                'flagged' => true,
                'message' => 'Anda telah dikeluarikan dari ujian ini.',
            ]);
        }

        // Get max violations from package settings (default 3)
        $maxViolations = $attempt->package->max_violations ?? 3;

        // Get violation type from request
        $violationType = $request->input('type', 'unknown');

        // Get existing violations log
        $violationsLog = $attempt->violations_log ? json_decode($attempt->violations_log, true) : [];

        // Add new violation entry
        $violationsLog[] = [
            'type' => $violationType,
            'time' => now()->toDateTimeString(),
        ];

        // Save violations log and increment count
        $attempt->update([
            'violations_count' => $attempt->violations_count + 1,
            'violations_log' => json_encode($violationsLog),
        ]);
        $attempt->refresh();

        // Check if exceeded limit
        if ($attempt->violations_count >= $maxViolations) {
            $attempt->update([
                'is_flagged' => true,
                'flagged_at' => now(),
                'status' => 'expired',
            ]);

            return response()->json([
                'flagged' => true,
                'violations_count' => $attempt->violations_count,
                'max_violations' => $maxViolations,
                'message' => 'Anda telah dikeluarikan dari ujian karena terlalu banyak pelanggaran.',
            ]);
        }

        return response()->json([
            'flagged' => false,
            'violations_count' => $attempt->violations_count,
            'max_violations' => $maxViolations,
            'message' => 'Pelanggaran dicatat. '.($maxViolations - $attempt->violations_count).' pelanggaran lagi maka Anda akan dikeluarikan.',
        ]);
    }

    public function resetWithToken(Request $request, TestAttempt $attempt)
    {
        $student = Auth::guard('student')->user();

        // Check ownership
        if ($attempt->student_id !== $student->id) {
            abort(403);
        }

        $request->validate([
            'reset_token' => 'required|string',
        ]);

        // Check if token is valid
        if (! $attempt->reset_token || ! $attempt->reset_token_expires_at) {
            return back()->with('error', 'Token reset tidak tersedia. Hubungi guru untuk meminta token.');
        }

        if (now()->greaterThan($attempt->reset_token_expires_at)) {
            return back()->with('error', 'Token reset sudah kedaluwarsa. Minta token baru dari guru.');
        }

        if ($attempt->reset_token !== $request->reset_token) {
            return back()->with('error', 'Token reset tidak valid.');
        }

        // Reset the attempt
        $attempt->update([
            'violations_count' => 0,
            'violations_log' => null,
            'is_flagged' => false,
            'flagged_at' => null,
            'reset_token' => null,
            'reset_token_expires_at' => null,
            'status' => 'ongoing',
            'end_time' => null,
            'submitted_at' => null,
            'total_score' => 0,
            'correct_answers' => 0,
            'wrong_answers' => 0,
            'unanswered' => 0,
        ]);

        // Delete all answers
        $attempt->answers()->delete();

        return redirect()->route('student.test.work', $attempt)
            ->with('success', 'Token berhasil digunakan. Anda dapat mengikuti tes ulang.');
    }
}
