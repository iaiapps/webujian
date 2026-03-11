<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $categories = QuestionCategory::withCount('questions')
            ->orderBy('order')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:question_categories,slug',
            'description' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        QuestionCategory::create([
            'name' => $request->name,
            'slug' => $request->slug ?: Str::slug($request->name),
            'description' => $request->description,
            'order' => $request->order ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(QuestionCategory $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, QuestionCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:question_categories,slug,' . $category->id,
            'description' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => $request->slug ?: Str::slug($request->name),
            'description' => $request->description,
            'order' => $request->order ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(QuestionCategory $category)
    {
        if ($category->questions()->exists()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki soal.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
