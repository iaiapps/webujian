<?php

// app/Http/Controllers/Guru/ClassController.php
namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:guru', 'check.approved']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $user->classes()->withCount('students');

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $classes = $query->latest()->paginate(10);

        return view('guru.classes.index', compact('classes'));
    }

    public function create()
    {
        $user = Auth::user();

        // Check if can add class
        if (!$user->canAddClass()) {
            return redirect()->route('guru.classes.index')->with('limit_reached', [
                'type' => 'class',
                'limit' => $user->max_classes,
                'current' => $user->classesCount(),
                'message' => "Anda sudah mencapai batas maksimal {$user->max_classes} kelas untuk plan {$user->plan}. Upgrade plan untuk menambah kelas.",
            ]);
        }

        return view('guru.classes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'academic_year' => ['nullable', 'string', 'max:20'],
        ]);

        $user = Auth::user();

        $class = $user->classes()->create([
            'name' => $request->name,
            'description' => $request->description,
            'academic_year' => $request->academic_year,
            'student_count' => 0,
        ]);

        return redirect()->route('guru.classes.index')->with('success', "Kelas {$class->name} berhasil dibuat!");
    }

    public function show(ClassRoom $class)
    {
        // Check ownership
        if ($class->user_id !== Auth::id()) {
            abort(403);
        }

        $class->load(['students' => function ($q) {
            $q->latest();
        }]);

        return view('guru.classes.show', compact('class'));
    }

    public function edit(ClassRoom $class)
    {
        // Check ownership
        if ($class->user_id !== Auth::id()) {
            abort(403);
        }

        return view('guru.classes.edit', compact('class'));
    }

    public function update(Request $request, ClassRoom $class)
    {
        // Check ownership
        if ($class->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'academic_year' => ['nullable', 'string', 'max:20'],
        ]);

        $class->update([
            'name' => $request->name,
            'description' => $request->description,
            'academic_year' => $request->academic_year,
        ]);

        return redirect()->route('guru.classes.index')->with('success', "Kelas {$class->name} berhasil diupdate!");
    }

    public function destroy(ClassRoom $class)
    {
        // Check ownership
        if ($class->user_id !== Auth::id()) {
            abort(403);
        }

        $name = $class->name;
        $class->delete();

        return redirect()->route('guru.classes.index')->with('success', "Kelas {$name} berhasil dihapus!");
    }
}
