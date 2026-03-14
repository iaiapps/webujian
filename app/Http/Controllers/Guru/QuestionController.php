<?php

namespace App\Http\Controllers\Guru;

use App\Exports\QuestionsTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\QuestionsImport;
use App\Models\Question;
use App\Models\QuestionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;

class QuestionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:guru', 'check.approved']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $user->questions()->with('category');

        // Search
        if ($request->filled('search')) {
            $query->where('question_text', 'like', "%{$request->search}%");
        }

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

        $questions = $query->latest()->paginate(20);
        $categories = QuestionCategory::where('is_active', true)->get();

        return view('guru.questions.index', compact('questions', 'categories'));
    }

    public function create()
    {
        $user = Auth::user();

        // Check if can add question
        if (! $user->canAddQuestion()) {
            return redirect()->route('guru.questions.index')->with('limit_reached', [
                'type' => 'question',
                'limit' => $user->max_questions,
                'current' => $user->questionsCount(),
                'message' => "Anda sudah mencapai batas maksimal {$user->max_questions} soal. Hubungi admin untuk menambah limit.",
            ]);
        }

        $categories = QuestionCategory::where('is_active', true)->get();

        return view('guru.questions.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // Filter out empty options (D & E can be empty)
        $filteredOptions = array_filter($request->options ?? [], function ($option) {
            return ! empty(trim($option['content'] ?? ''));
        });

        // Replace request options with filtered
        $request->merge(['options' => $filteredOptions]);

        $request->validate([
            'category_id' => ['required', 'exists:question_categories,id'],
            'question_type' => ['required', 'in:single,complex,category'],
            'question_text' => ['required', 'string'],
            'question_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'options' => ['required', 'array', 'min:3', 'max:5'], // Min 3 (A, B, C), max 5
            'options.*.label' => ['required', 'string', 'size:1'],
            'options.*.content' => ['required', 'string'],
            'correct_answer' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'difficulty' => ['required', 'in:easy,medium,hard'],
        ]);

        $user = Auth::user();

        $data = $request->only([
            'category_id',
            'question_type',
            'question_text',
            'correct_answer',
            'explanation',
            'difficulty',
        ]);

        // Handle image upload
        if ($request->hasFile('question_image')) {
            $data['question_image'] = $this->uploadImage($request->file('question_image'));
        }

        // Format correct answer untuk complex type
        if ($request->question_type === 'complex') {
            // Jika dari checkbox, akan berupa array
            if (is_array($request->correct_answer)) {
                $data['correct_answer'] = implode(',', $request->correct_answer);
            }
        }

        // Format correct answer untuk category type
        if ($request->question_type === 'category') {
            // Dari form: ['A' => 'B', 'B' => 'S', ...]
            if (is_array($request->correct_answer)) {
                $pairs = [];
                foreach ($request->correct_answer as $statement => $value) {
                    $pairs[] = strtoupper($statement).':'.strtoupper($value);
                }
                $data['correct_answer'] = implode(',', $pairs);
            }
        }

        // Create question
        $question = $user->questions()->create($data);

        // Create options
        foreach ($request->options as $index => $option) {
            $question->options()->create([
                'label' => strtoupper($option['label']),
                'content' => $option['content'],
                'order' => $index,
            ]);
        }

        return redirect()->route('guru.questions.index')->with('success', 'Soal berhasil ditambahkan!');
    }

    public function show(Question $question)
    {
        // Check ownership
        if ($question->user_id !== Auth::id()) {
            abort(403);
        }

        $question->load(['category', 'options']);

        return view('guru.questions.show', compact('question'));
    }

    public function edit(Question $question)
    {
        // Check ownership
        if ($question->user_id !== Auth::id()) {
            abort(403);
        }

        $question->load('options');
        $categories = QuestionCategory::where('is_active', true)->get();

        return view('guru.questions.edit', compact('question', 'categories'));
    }

    public function update(Request $request, Question $question)
    {
        // Check ownership
        if ($question->user_id !== Auth::id()) {
            abort(403);
        }

        // Filter out empty options (D & E can be empty)
        $filteredOptions = array_filter($request->options ?? [], function ($option) {
            return ! empty(trim($option['content'] ?? ''));
        });

        // Replace request options with filtered
        $request->merge(['options' => $filteredOptions]);

        $request->validate([
            'category_id' => ['required', 'exists:question_categories,id'],
            'question_type' => ['required', 'in:single,complex,category'],
            'question_text' => ['required', 'string'],
            'question_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'options' => ['required', 'array', 'min:3', 'max:5'], // Min 3 (A, B, C), max 5
            'options.*.label' => ['required', 'string', 'size:1'],
            'options.*.content' => ['required', 'string'],
            'correct_answer' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'difficulty' => ['required', 'in:easy,medium,hard'],
        ]);

        $data = $request->only([
            'category_id',
            'question_type',
            'question_text',
            'correct_answer',
            'explanation',
            'difficulty',
        ]);

        // Handle image upload
        if ($request->hasFile('question_image')) {
            // Delete old image
            if ($question->question_image) {
                Storage::disk('public')->delete($question->question_image);
            }
            $data['question_image'] = $this->uploadImage($request->file('question_image'));
        }

        // Format correct answer untuk complex type
        if ($request->question_type === 'complex') {
            if (is_array($request->correct_answer)) {
                $data['correct_answer'] = implode(',', $request->correct_answer);
            }
        }

        // Format correct answer untuk category type
        if ($request->question_type === 'category') {
            if (is_array($request->correct_answer)) {
                $pairs = [];
                foreach ($request->correct_answer as $statement => $value) {
                    $pairs[] = strtoupper($statement).':'.strtoupper($value);
                }
                $data['correct_answer'] = implode(',', $pairs);
            }
        }

        // Update question
        $question->update($data);

        // Delete old options and create new ones
        $question->options()->delete();
        foreach ($request->options as $index => $option) {
            $question->options()->create([
                'label' => strtoupper($option['label']),
                'content' => $option['content'],
                'order' => $index,
            ]);
        }

        return redirect()->route('guru.questions.index')->with('success', 'Soal berhasil diupdate!');
    }

    public function destroy(Question $question)
    {
        // Check ownership
        if ($question->user_id !== Auth::id()) {
            abort(403);
        }

        // Delete image if exists
        if ($question->question_image) {
            Storage::disk('public')->delete($question->question_image);
        }

        // Options will be deleted automatically via cascade
        $question->delete();

        return redirect()->route('guru.questions.index')->with('success', 'Soal berhasil dihapus!');
    }

    // Download Excel Template
    public function downloadTemplate()
    {
        return Excel::download(new QuestionsTemplateExport, 'template_soal.xlsx');
    }

    // Import Questions from Excel
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        $user = Auth::user();

        // Check limit
        $currentCount = $user->questionsCount();
        $maxQuestions = $user->max_questions;

        try {
            $import = new QuestionsImport($user);
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();

            // Check if over limit after import
            $newCount = $user->questionsCount();
            if ($newCount > $maxQuestions) {
                return redirect()->route('guru.questions.index')
                    ->with('warning', "Import berhasil ({$imported} soal), tetapi Anda melebihi limit {$maxQuestions} soal. Hubungi admin untuk menambah limit.");
            }

            return redirect()->route('guru.questions.index')
                ->with('success', "Berhasil import {$imported} soal!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Import gagal: '.$e->getMessage())
                ->withInput();
        }
    }

    // Upload image with optimization
    private function uploadImage($file)
    {
        // Read and process image
        $image = Image::read($file);

        // Scale to max width 800px while maintaining aspect ratio
        $image->scale(width: 800);

        // Generate unique filename
        $filename = time().'_'.uniqid().'.jpg';
        $path = 'questions/'.$filename;

        // Save with compression (quality 85)
        $fullPath = storage_path('app/public/'.$path);
        $image->save($fullPath, quality: 85);

        return $path;
    }
}
