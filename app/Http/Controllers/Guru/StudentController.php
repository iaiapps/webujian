<?php

// app/Http/Controllers/Guru/StudentController.php

namespace App\Http\Controllers\Guru;

use App\Exports\StudentsTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\StudentsImport;
use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:guru', 'check.approved']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $user->students()->with('classRoom');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        // Filter by class (DINONAKTIFKAN)
        // if ($request->filled('class_id')) {
        //     $query->where('class_id', $request->class_id);
        // }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        $students = $query->latest()->paginate(20);
        $classes = $user->classes;

        return view('guru.students.index', compact('students', 'classes'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();

        // Check if can add student
        if (! $user->canAddStudent()) {
            return redirect()->route('guru.students.index')->with('limit_reached', [
                'type' => 'student',
                'limit' => $user->max_students,
                'current' => $user->studentsCount(),
                'message' => "Anda sudah mencapai batas maksimal {$user->max_students} siswa untuk plan {$user->plan}. Upgrade plan untuk menambah siswa.",
            ]);
        }

        $classes = $user->classes;
        $selectedClassId = $request->get('class_id');

        return view('guru.students.create', compact('classes', 'selectedClassId'));
    }

    public function store(Request $request)
    {
        // ============================================================
        // KELAS DINONAKTIFKAN - class_id tidak wajib
        // ============================================================
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // 'class_id' => ['required', 'exists:classes,id'],
            'class_id' => ['nullable'], // DINONAKTIFKAN
            'nisn' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', 'unique:students,username'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $user = Auth::user();

        // Verify class ownership (DINONAKTIFKAN)
        // $class = ClassRoom::where('id', $request->class_id)
        //     ->where('user_id', $user->id)
        //     ->firstOrFail();

        // Auto-generate username if not provided
        $username = $request->username;
        if (! $username) {
            $username = $request->nisn ?? $this->generateUsername($request->name);
        }

        // Auto-generate password if not provided
        $password = $request->password ?? '123456';

        $student = $user->students()->create([
            'class_id' => $request->class_id, // Bisa null
            'name' => $request->name,
            'nisn' => $request->nisn,
            'email' => $request->email,
            'username' => $username,
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        // Update class student count (DINONAKTIFKAN)
        // if ($class) {
        //     $class->updateStudentCount();
        // }

        // Store plain password temporarily for display (in session)
        session()->flash('new_student_credentials', [
            'name' => $student->name,
            'username' => $student->username,
            'password' => $password,
        ]);

        return redirect()->route('guru.students.show', $student)->with('success', 'Siswa berhasil ditambahkan!');
    }

    public function show(Student $student)
    {
        // Check ownership
        if ($student->user_id !== Auth::id()) {
            abort(403);
        }

        $student->load(['classRoom', 'testAttempts.package']);

        return view('guru.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        // Check ownership
        if ($student->user_id !== Auth::id()) {
            abort(403);
        }

        $classes = Auth::user()->classes;

        return view('guru.students.edit', compact('student', 'classes'));
    }

    public function update(Request $request, Student $student)
    {
        // Check ownership
        if ($student->user_id !== Auth::id()) {
            abort(403);
        }

        // ============================================================
        // KELAS DINONAKTIFKAN - class_id tidak wajib
        // ============================================================
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // 'class_id' => ['required', 'exists:classes,id'],
            'class_id' => ['nullable'], // DINONAKTIFKAN
            'nisn' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:students,username,'.$student->id],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['required', 'boolean'],
        ]);

        $oldClassId = $student->class_id;

        $student->update([
            'class_id' => $request->class_id,
            'name' => $request->name,
            'nisn' => $request->nisn,
            'email' => $request->email,
            'username' => $request->username,
            'is_active' => $request->is_active,
        ]);

        // Update password only if provided
        if ($request->filled('password')) {
            $student->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // Update class student count if class changed (DINONAKTIFKAN)
        // if ($oldClassId != $request->class_id) {
        //     if ($oldClassId) {
        //         ClassRoom::find($oldClassId)?->updateStudentCount();
        //     }
        //     ClassRoom::find($request->class_id)?->updateStudentCount();
        // }

        return redirect()->route('guru.students.index')->with('success', 'Data siswa berhasil diupdate!');
    }

    public function destroy(Student $student)
    {
        // Check ownership
        if ($student->user_id !== Auth::id()) {
            abort(403);
        }

        $name = $student->name;
        $classId = $student->class_id;

        $student->delete();

        // Update class student count (DINONAKTIFKAN)
        // if ($classId) {
        //     ClassRoom::find($classId)?->updateStudentCount();
        // }

        return redirect()->route('guru.students.index')->with('success', "Siswa {$name} berhasil dihapus!");
    }

    // Download Excel Template
    public function downloadTemplate()
    {
        return Excel::download(new StudentsTemplateExport, 'template_siswa.xlsx');
    }

    // Import Students from Excel
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:2048'],
            'default_password' => ['required', 'string', 'min:6'],
        ]);

        $user = Auth::user();

        try {
            $import = new StudentsImport($user, $request->default_password);
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $credentials = $import->getCredentials();

            // Store credentials in session for display
            session()->flash('import_success', [
                'count' => $imported,
                'credentials' => $credentials,
            ]);

            return redirect()->route('guru.students.index')->with('success', "Berhasil import {$imported} siswa!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import gagal: '.$e->getMessage());
        }
    }

    // Generate unique username from name
    private function generateUsername($name)
    {
        $base = Str::slug(Str::lower($name));
        $username = $base;
        $counter = 1;

        while (Student::where('username', $username)->exists()) {
            $username = $base.$counter;
            $counter++;
        }

        return $username;
    }
}
