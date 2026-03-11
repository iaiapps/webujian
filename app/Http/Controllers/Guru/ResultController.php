<?php

// app/Http/Controllers/Guru/ResultController.php
namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\TestPackage;
use App\Models\TestAttempt;
use App\Models\Student;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TestResultsExport;

class ResultController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:guru', 'check.approved']);
    }

    public function index()
    {
        $user = Auth::user();

        // Get packages with attempt counts
        $packages = $user->testPackages()
            ->withCount(['testAttempts', 'completedAttempts'])
            ->where('start_date', '<=', now())
            ->latest()
            ->paginate(10);

        return view('guru.results.index', compact('packages'));
    }

    public function package(TestPackage $package)
    {
        // Check ownership
        if ($package->user_id !== Auth::id()) {
            abort(403);
        }

        // Get all completed attempts with students
        $attempts = $package->completedAttempts()
            ->with('student.classRoom')
            ->orderBy('total_score', 'desc')
            ->get();

        // Statistics
        $statistics = [
            'total_attempts' => $attempts->count(),
            'avg_score' => $attempts->avg('total_score'),
            'highest_score' => $attempts->max('total_score'),
            'lowest_score' => $attempts->min('total_score'),
            'avg_correct' => $attempts->avg('correct_answers'),
            'avg_wrong' => $attempts->avg('wrong_answers'),
            'avg_unanswered' => $attempts->avg('unanswered'),
        ];

        // Score distribution
        $scoreDistribution = $attempts->groupBy(function ($attempt) {
            $score = $attempt->total_score;
            if ($score >= 80) return '80-100';
            if ($score >= 60) return '60-79';
            if ($score >= 40) return '40-59';
            if ($score >= 20) return '20-39';
            return '0-19';
        })->map->count();

        // Question analysis
        $questionAnalysis = $this->analyzeQuestions($package);

        return view('guru.results.package', compact(
            'package',
            'attempts',
            'statistics',
            'scoreDistribution',
            'questionAnalysis'
        ));
    }

    public function student(Student $student)
    {
        // Check ownership
        if ($student->user_id !== Auth::id()) {
            abort(403);
        }

        // Get all completed attempts
        $attempts = $student->completedTests()
            ->with('package')
            ->latest()
            ->get();

        // Statistics
        $statistics = [
            'total_tests' => $attempts->count(),
            'avg_score' => $attempts->avg('total_score'),
            'highest_score' => $attempts->max('total_score'),
            'lowest_score' => $attempts->min('total_score'),
            'total_correct' => $attempts->sum('correct_answers'),
            'total_wrong' => $attempts->sum('wrong_answers'),
        ];

        // Score trend (for chart)
        $scoreTrend = $attempts->map(function ($attempt) {
            return [
                'date' => $attempt->submitted_at->format('d M'),
                'score' => $attempt->total_score,
                'package' => $attempt->package->title,
            ];
        });

        // Per category performance
        $categoryPerformance = $this->getStudentCategoryPerformance($student);

        return view('guru.results.student', compact(
            'student',
            'attempts',
            'statistics',
            'scoreTrend',
            'categoryPerformance'
        ));
    }

    public function export(TestPackage $package)
    {
        // Check ownership
        if ($package->user_id !== Auth::id()) {
            abort(403);
        }

        $fileName = 'hasil_' . str_replace(' ', '_', $package->title) . '_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new TestResultsExport($package), $fileName);
    }

    private function analyzeQuestions(TestPackage $package)
    {
        $questions = $package->questions;
        $analysis = [];

        foreach ($questions as $question) {
            // Get all answers for this question
            $answers = DB::table('test_answers')
                ->join('test_attempts', 'test_answers.attempt_id', '=', 'test_attempts.id')
                ->where('test_attempts.package_id', $package->id)
                ->where('test_answers.question_id', $question->id)
                ->where('test_attempts.status', 'completed')
                ->select('test_answers.is_correct')
                ->get();

            $totalAnswers = $answers->count();
            $correctCount = $answers->where('is_correct', true)->count();
            $wrongCount = $totalAnswers - $correctCount;

            $successRate = $totalAnswers > 0 ? round(($correctCount / $totalAnswers) * 100, 1) : 0;

            $analysis[] = [
                'question_id' => $question->id,
                'question_text' => $question->question_text,
                'category' => $question->category->name,
                'difficulty' => $question->difficulty,
                'total_answers' => $totalAnswers,
                'correct_count' => $correctCount,
                'wrong_count' => $wrongCount,
                'success_rate' => $successRate,
            ];
        }

        // Sort by success rate (ascending) to show hardest questions first
        usort($analysis, function ($a, $b) {
            return $a['success_rate'] <=> $b['success_rate'];
        });

        return $analysis;
    }

    private function getStudentCategoryPerformance(Student $student)
    {
        $attempts = $student->completedTests()->with('answers.question.category')->get();

        $categoryData = [];

        foreach ($attempts as $attempt) {
            foreach ($attempt->answers as $answer) {
                $categoryName = $answer->question->category->name;

                if (!isset($categoryData[$categoryName])) {
                    $categoryData[$categoryName] = [
                        'total' => 0,
                        'correct' => 0,
                    ];
                }

                $categoryData[$categoryName]['total']++;
                if ($answer->is_correct) {
                    $categoryData[$categoryName]['correct']++;
                }
            }
        }

        // Calculate percentages
        $performance = [];
        foreach ($categoryData as $category => $data) {
            $percentage = $data['total'] > 0 ? round(($data['correct'] / $data['total']) * 100, 1) : 0;
            $performance[] = [
                'category' => $category,
                'correct' => $data['correct'],
                'total' => $data['total'],
                'percentage' => $percentage,
            ];
        }

        return $performance;
    }
}
