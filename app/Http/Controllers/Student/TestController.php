<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\TestAnswer;
use App\Models\TestAttempt;
use App\Models\TestPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:student');
    }

    public function start(TestPackage $package)
    {
        $student = Auth::guard('student')->user();

        // Check if package is available
        if (! $package->isAvailable()) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Tes ini tidak tersedia atau sudah berakhir.');
        }

        // ============================================================
        // KELAS DINONAKTIFKAN - Semua siswa bisa akses tes tanpa perlu kelas
        // ============================================================
        // // Check if student's class is assigned
        // $isAssigned = $package->classes()
        //     ->where('class_id', $student->class_id)
        //     ->exists();

        // if (! $isAssigned) {
        //     return redirect()->route('student.dashboard')
        //         ->with('error', 'Anda tidak terdaftar untuk tes ini.');
        // }

        // Check if already attempted
        $existingAttempt = TestAttempt::where('student_id', $student->id)
            ->where('package_id', $package->id)
            ->first();

        if ($existingAttempt) {
            if ($existingAttempt->status === 'completed') {
                return redirect()->route('student.test.result', $existingAttempt)
                    ->with('info', 'Anda sudah mengerjakan tes ini.');
            } else {
                // Continue existing attempt
                return redirect()->route('student.test.work', $existingAttempt);
            }
        }

        // Show confirmation page
        $package->load('questions.category');

        return view('student.exam.start', compact('package'));
    }

    public function createAttempt(Request $request)
    {
        $request->validate([
            'package_id' => ['required', 'exists:test_packages,id'],
        ]);

        $student = Auth::guard('student')->user();
        $package = TestPackage::findOrFail($request->package_id);

        // Check if package is available
        if (! $package->isAvailable()) {
            return response()->json(['error' => 'Tes tidak tersedia'], 400);
        }

        // ============================================================
        // KELAS DINONAKTIFKAN - Semua siswa bisa akses tes
        // ============================================================
        // // Check if student's class is assigned
        // $isAssigned = $package->classes()
        //     ->where('class_id', $student->class_id)
        //     ->exists();

        // if (! $isAssigned) {
        //     return response()->json(['error' => 'Anda tidak terdaftar untuk tes ini'], 403);
        // }

        // Check if already attempted
        $existingAttempt = TestAttempt::where('student_id', $student->id)
            ->where('package_id', $package->id)
            ->first();

        if ($existingAttempt) {
            return response()->json([
                'attempt_id' => $existingAttempt->id,
                'message' => 'Melanjutkan tes sebelumnya',
            ]);
        }

        // Create new attempt
        $attempt = TestAttempt::create([
            'student_id' => $student->id,
            'package_id' => $package->id,
            'start_time' => now(),
            'end_time' => now()->addMinutes($package->duration),
            'status' => 'ongoing',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'attempt_id' => $attempt->id,
            'message' => 'Tes dimulai',
        ]);
    }

    public function work(TestAttempt $attempt)
    {
        $student = Auth::guard('student')->user();

        // Check ownership
        if ($attempt->student_id !== $student->id) {
            abort(403);
        }

        // Check if flagged (kicked out due to violations)
        if ($attempt->is_flagged) {
            return redirect()->route('student.test.result', $attempt)
                ->with('error', 'Anda telah dikeluarikan dari ujian ini. Hubungi guru untuk mendapatkan token reset.');
        }

        // Check if expired
        if ($attempt->isExpired()) {
            $this->autoSubmit($attempt);

            return redirect()->route('student.test.result', $attempt)
                ->with('info', 'Waktu tes telah habis. Tes Anda telah di-submit otomatis.');
        }

        // Check if already completed
        if ($attempt->isCompleted()) {
            return redirect()->route('student.test.result', $attempt);
        }

        $package = $attempt->package;
        $package->load(['questions.category', 'questions.options']);

        // Get questions (shuffle if needed)
        $questions = $package->questions;
        if ($package->shuffle_questions) {
            $questions = $questions->shuffle();
        }

        // Get existing answers
        $existingAnswers = $attempt->answers()
            ->pluck('answer', 'question_id')
            ->toArray();

        $doubtQuestions = $attempt->answers()
            ->where('is_doubt', true)
            ->pluck('question_id')
            ->toArray();

        // Get current violation count for JS initialization
        $violationCount = $attempt->violations_count;

        return view('student.exam.work', compact('attempt', 'package', 'questions', 'existingAnswers', 'doubtQuestions', 'violationCount'));
    }

    public function continueAttempt(TestAttempt $attempt)
    {
        return $this->work($attempt);
    }

    public function saveAnswer(Request $request, TestAttempt $attempt)
    {
        $request->validate([
            'question_id' => ['required', 'exists:questions,id'],
            'answer' => ['nullable', 'string'],
            'is_doubt' => ['boolean'],
        ]);

        $student = Auth::guard('student')->user();

        // Check ownership
        if ($attempt->student_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if still ongoing
        if (! $attempt->isOngoing()) {
            return response()->json(['error' => 'Test already completed'], 400);
        }

        // Check if expired
        if ($attempt->isExpired()) {
            $this->autoSubmit($attempt);

            return response()->json(['error' => 'Time expired', 'expired' => true], 400);
        }

        $question = $attempt->package->questions()->find($request->question_id);

        if (! $question) {
            return response()->json(['error' => 'Question not found'], 404);
        }

        // Check answer correctness
        $isCorrect = false;
        if ($request->filled('answer')) {
            $isCorrect = $question->checkAnswer($request->answer);
        }

        // Save or update answer
        TestAnswer::updateOrCreate(
            [
                'attempt_id' => $attempt->id,
                'question_id' => $request->question_id,
            ],
            [
                'answer' => $request->answer,
                'is_correct' => $isCorrect,
                'is_doubt' => $request->is_doubt ?? false,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Answer saved',
        ]);
    }

    public function submit(Request $request, TestAttempt $attempt)
    {
        $student = Auth::guard('student')->user();

        // Check ownership
        if ($attempt->student_id !== $student->id) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        // Check if already completed
        if ($attempt->isCompleted()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'redirect' => route('student.test.result', $attempt)]);
            }

            return redirect()->route('student.test.result', $attempt);
        }

        DB::beginTransaction();
        try {
            // Calculate statistics
            $attempt->calculateStatistics();

            // Calculate score
            $score = $attempt->calculateScore();

            // Update attempt
            $attempt->update([
                'status' => 'completed',
                'submitted_at' => now(),
                'total_score' => $score,
            ]);

            // Increment package attempt count
            $attempt->package->incrementAttemptCount();

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tes berhasil diselesaikan!',
                    'redirect' => route('student.test.result', $attempt),
                ]);
            }

            return redirect()->route('student.test.result', $attempt)
                ->with('success', 'Tes berhasil diselesaikan!');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Gagal submit tes: '.$e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Gagal submit tes: '.$e->getMessage());
        }
    }

    /**
     * Bulk sync answers from LocalStorage
     */
    public function bulkSync(Request $request, TestAttempt $attempt)
    {
        $student = Auth::guard('student')->user();

        // Check ownership
        if ($attempt->student_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if still ongoing
        if (! $attempt->isOngoing()) {
            return response()->json(['error' => 'Test already completed'], 400);
        }

        // Validate request
        $request->validate([
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required', 'integer'],
            'answers.*.answer' => ['nullable', 'string'],
            'answers.*.is_doubt' => ['boolean'],
        ]);

        $syncedIds = [];
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($request->answers as $answerData) {
                $questionId = $answerData['question_id'];
                $answer = $answerData['answer'];
                $isDoubt = $answerData['is_doubt'] ?? false;

                $question = $attempt->package->questions()->find($questionId);

                if (! $question) {
                    $errors[] = "Question {$questionId} not found";

                    continue;
                }

                // Check answer correctness
                $isCorrect = false;
                if ($answer !== null && $answer !== '') {
                    $isCorrect = $question->checkAnswer($answer);
                }

                // Save or update answer
                TestAnswer::updateOrCreate(
                    [
                        'attempt_id' => $attempt->id,
                        'question_id' => $questionId,
                    ],
                    [
                        'answer' => $answer,
                        'is_correct' => $isCorrect,
                        'is_doubt' => $isDoubt,
                    ]
                );

                $syncedIds[] = $questionId;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'synced' => count($syncedIds),
                'synced_ids' => $syncedIds,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function autoSubmit(TestAttempt $attempt)
    {
        if ($attempt->isCompleted()) {
            return;
        }

        DB::beginTransaction();
        try {
            $attempt->calculateStatistics();
            $score = $attempt->calculateScore();

            $attempt->update([
                'status' => 'completed',
                'submitted_at' => now(),
                'total_score' => $score,
            ]);

            $attempt->package->incrementAttemptCount();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Auto submit failed: '.$e->getMessage());
        }
    }
    
    /**
     * Get single question for lazy loading
     */
    public function getQuestion(Request $request, TestAttempt $attempt, int $questionNumber)
    {
        $student = Auth::guard('student')->user();
        
        // Check ownership
        if ($attempt->student_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Check if still ongoing
        if (! $attempt->isOngoing()) {
            return response()->json(['error' => 'Test already completed'], 400);
        }
        
        // Load package with questions
        $package = $attempt->package;
        $package->load(['questions' => function ($query) {
            $query->with('category', 'options')->orderBy('test_package_questions.order');
        }]);
        
        // Get question by number (index + 1)
        $questions = $package->questions;
        if ($questionNumber < 1 || $questionNumber > $questions->count()) {
            return response()->json(['error' => 'Question not found'], 404);
        }
        
        $question = $questions[$questionNumber - 1];
        
        // Get existing answer if any
        $existingAnswer = TestAnswer::where('attempt_id', $attempt->id)
            ->where('question_id', $question->id)
            ->first();
        
        // Format question data
        $questionData = [
            'id' => $question->id,
            'number' => $questionNumber,
            'text' => $question->question_text,
            'image' => $question->question_image ? Storage::url($question->question_image) : null,
            'type' => $question->question_type,
            'category' => $question->category ? $question->category->name : null,
            'options' => $question->options->map(function ($option) {
                return [
                    'label' => $option->label,
                    'content' => $option->content,
                ];
            }),
            'existing_answer' => $existingAnswer ? $existingAnswer->answer : null,
            'is_doubt' => $existingAnswer ? $existingAnswer->is_doubt : false,
        ];
        
        return response()->json([
            'success' => true,
            'question' => $questionData,
            'total_questions' => $questions->count(),
        ]);
    }
}
