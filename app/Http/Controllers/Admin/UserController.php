<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index(Request $request)
    {
        // SISTEM KREDIT - Tidak perlu load subscription
        $query = User::role('guru');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('institution_name', 'like', "%{$search}%");
            });
        }

        // SISTEM KREDIT - Filter plan dihapus

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'pending') {
                $query->whereNull('approved_at');
            }
        }

        $users = $query->latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function pending()
    {
        $users = User::role('guru')
            ->whereNull('approved_at')
            ->latest()
            ->paginate(20);

        return view('admin.users.pending', compact('users'));
    }

    public function show(User $user)
    {
        // SISTEM KREDIT - Hapus subscription dari load
        $user->load(['students', 'questions', 'testPackages']);

        // KELAS DINONAKTIFKAN - total_classes tidak perlu dikirim
        $stats = [
            'total_students' => $user->students()->count(),
            // 'total_classes' => $user->classes()->count(),
            'total_questions' => $user->questions()->count(),
            'total_packages' => $user->testPackages()->count(),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    public function approve(User $user)
    {
        // ============================================================
        // APPROVAL MANUAL DINONAKTIFKAN
        // Method ini dipertahankan untuk kebutuhan di masa depan
        // jika ingin mengaktifkan kembali sistem approval manual
        // ============================================================

        // $user->update([
        //     'approved_at' => now(),
        //     'is_active' => true,
        // ]);

        // // TODO: Send email notification to user
        // // Mail::to($user->email)->send(new UserApproved($user));

        // return redirect()->back()->with('success', "User {$user->name} telah disetujui.");

        // Untuk saat ini, cukup info saja
        return redirect()->back()->with('info', 'Fitur approval manual saat ini dinonaktifkan.');
    }

    public function reject(User $user)
    {
        // ============================================================
        // APPROVAL MANUAL DINONAKTIFKAN
        // Method ini dipertahankan untuk kebutuhan di masa depan
        // ============================================================

        // // TODO: Send email notification before delete
        // // Mail::to($user->email)->send(new UserRejected($user));

        // $user->delete();

        // return redirect()->route('admin.users.pending')->with('success', "User {$user->name} telah ditolak dan dihapus.");

        return redirect()->back()->with('info', 'Fitur reject manual saat ini dinonaktifkan.');
    }

    public function toggleStatus(User $user)
    {
        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()->with('success', "User {$user->name} telah {$status}.");
    }
}
