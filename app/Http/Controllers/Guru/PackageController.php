<?php

// app/Http/Controllers/Guru/PackageController.php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\TestPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:guru', 'check.approved']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $user->testPackages()->withCount(['questions', 'testAttempts']);

        // Search
        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true)
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
            } elseif ($request->status === 'upcoming') {
                $query->where('start_date', '>', now());
            } elseif ($request->status === 'expired') {
                $query->where('end_date', '<', now());
            }
        }

        $packages = $query->latest()->paginate(10);

        return view('guru.packages.index', compact('packages'));
    }

    public function create()
    {
        $user = Auth::user();

        // ============================================================
        // SISTEM KREDIT - Cek kredit, bukan max_packages
        // ============================================================
        if (! $user->canCreatePackage()) {
            return redirect()->route('guru.packages.index')->with('limit_reached', [
                'type' => 'package',
                'limit' => $user->credits,
                'current' => $user->packagesCount(),
                'message' => 'Kredit Anda tidak cukup untuk membuat paket tes. Silakan beli kredit terlebih dahulu.',
            ]);
        }

        $classes = $user->classes;
        $categories = QuestionCategory::active()->get();

        return view('guru.packages.create', compact('classes', 'categories'));
    }

    public function store(Request $request)
    {
        // ============================================================
        // KELAS DINONAKTIFKAN - class_ids tidak wajib
        // SISTEM KREDIT - Cek kredit cukup sebelum create
        // ============================================================
        $user = Auth::user();

        if (! $user->canCreatePackage()) {
            return redirect()->back()->with('error', 'Kredit Anda tidak cukup untuk membuat paket tes.')->withInput();
        }

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            // 'class_ids' => ['nullable', 'array'],
            // 'class_ids.*' => ['exists:classes,id'],
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['exists:questions,id'],
        ]);

        DB::beginTransaction();
        try {
            // Create package
            $package = $user->testPackages()->create([
                'title' => $request->title,
                'description' => $request->description,
                'duration' => $request->duration,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'show_result' => (bool) $request->input('show_result', 0),
                'show_explanation' => (bool) $request->input('show_explanation', 0),
                'show_ranking' => (bool) $request->input('show_ranking', 0),
                'shuffle_questions' => (bool) $request->input('shuffle_questions', 0),
                'is_active' => (bool) $request->input('is_active', 1),
                'total_questions' => count($request->question_ids),
                'score_correct' => $request->input('score_correct', 4),
                'score_wrong' => $request->input('score_wrong', -1),
                'score_empty' => $request->input('score_empty', 0),
            ]);

            // Attach questions with order
            foreach ($request->question_ids as $order => $questionId) {
                $package->questions()->attach($questionId, ['order' => $order + 1]);

                // Increment usage count
                Question::find($questionId)?->incrementUsage();
            }

            // Attach classes (DINONAKTIFKAN)
            // if ($request->filled('class_ids')) {
            //     $package->classes()->attach($request->class_ids);
            // }

            // ============================================================
            // SISTEM KREDIT - Kurangi 1 kredit saat buat package
            // Tidak ada refund jika package dihapus
            // ============================================================
            $user->deductCredits(1);

            DB::commit();

            return redirect()->route('guru.packages.show', $package)
                ->with('success', 'Paket tes berhasil dibuat! (1 kredit digunakan)');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal membuat paket: '.$e->getMessage())->withInput();
        }
    }

    public function show(TestPackage $package)
    {
        // Check ownership
        if ($package->user_id !== Auth::id()) {
            abort(403);
        }

        $package->load(['questions.category', 'classes', 'testAttempts.student']);

        $statistics = [
            'total_attempts' => $package->testAttempts()->count(),
            'completed' => $package->completedAttempts()->count(),
            'ongoing' => $package->testAttempts()->where('status', 'ongoing')->count(),
            'avg_score' => $package->completedAttempts()->avg('total_score'),
        ];

        return view('guru.packages.show', compact('package', 'statistics'));
    }

    public function edit(TestPackage $package)
    {
        // Check ownership
        if ($package->user_id !== Auth::id()) {
            abort(403);
        }

        // ============================================================
        // KELAS DINONAKTIFKAN
        // ============================================================
        $classes = Auth::user()->classes;
        $categories = QuestionCategory::active()->get();
        $selectedClasses = []; // $package->classes->pluck('id')->toArray(); // DINONAKTIFKAN
        $selectedQuestions = $package->questions->pluck('id')->toArray();

        return view('guru.packages.edit', compact('package', 'classes', 'categories', 'selectedClasses', 'selectedQuestions'));
    }

    public function update(Request $request, TestPackage $package)
    {
        // Check ownership
        if ($package->user_id !== Auth::id()) {
            abort(403);
        }

        // ============================================================
        // KELAS DINONAKTIFKAN - class_ids tidak wajib
        // ============================================================
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            // 'class_ids' => ['nullable', 'array'],
            // 'class_ids.*' => ['exists:classes,id'],
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['exists:questions,id'],
        ]);

        DB::beginTransaction();
        try {
            // Update package
            $package->update([
                'title' => $request->title,
                'description' => $request->description,
                'duration' => $request->duration,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'show_result' => (bool) $request->input('show_result', 0),
                'show_explanation' => (bool) $request->input('show_explanation', 0),
                'show_ranking' => (bool) $request->input('show_ranking', 0),
                'shuffle_questions' => (bool) $request->input('shuffle_questions', 0),
                'is_active' => (bool) $request->input('is_active', 1),
                'total_questions' => count($request->question_ids),
                'score_correct' => $request->input('score_correct', 4),
                'score_wrong' => $request->input('score_wrong', -1),
                'score_empty' => $request->input('score_empty', 0),
            ]);

            // Sync questions with order
            $syncData = [];
            foreach ($request->question_ids as $order => $questionId) {
                $syncData[$questionId] = ['order' => $order + 1];
            }
            $package->questions()->sync($syncData);

            // Sync classes (DINONAKTIFKAN)
            // $package->classes()->sync($request->class_ids ?? []);

            DB::commit();

            return redirect()->route('guru.packages.show', $package)
                ->with('success', 'Paket tes berhasil diupdate!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal update paket: '.$e->getMessage())->withInput();
        }
    }

    public function destroy(TestPackage $package)
    {
        // Check ownership
        if ($package->user_id !== Auth::id()) {
            abort(403);
        }

        $title = $package->title;
        $package->delete();

        return redirect()->route('guru.packages.index')
            ->with('success', "Paket tes '{$title}' berhasil dihapus!");
    }

    // AJAX: Get questions for selection
    public function getQuestions(Request $request)
    {
        $user = Auth::user();

        $query = $user->questions()->with('category');

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by type
        if ($request->filled('question_type')) {
            $query->where('question_type', $request->question_type);
        }

        // Filter by difficulty
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('question_text', 'like', "%{$request->search}%");
        }

        $questions = $query->latest()->paginate(20);

        return response()->json($questions);
    }

    // AJAX: Get random questions
    public function getRandomQuestions(Request $request)
    {
        $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:100'],
            'category_id' => ['nullable', 'exists:question_categories,id'],
            'difficulty' => ['nullable', 'in:easy,medium,hard'],
        ]);

        $user = Auth::user();

        $query = $user->questions();

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        $questions = $query->inRandomOrder()->take($request->count)->get();

        return response()->json($questions);
    }
}
