<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Student;
use App\Models\TestAttempt;
use App\Models\TestPackage;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        // Statistics
        $stats = [
            'total_users' => User::role('guru')->count(),
            'active_users' => User::role('guru')->active()->count(),
            'pending_approval' => User::role('guru')->pendingApproval()->count(),
            'total_students' => Student::count(),
            'total_questions' => Question::count(),
            'total_packages' => TestPackage::count(),
            'total_attempts' => TestAttempt::where('status', 'completed')->count(),
        ];

        // ============================================================
        // SISTEM KREDIT - Revenue tracking dihapus (tidak ada tabel transaksi)
        // ============================================================
        $revenue = [
            'today' => 0,
            'this_month' => 0,
            'total' => 0,
        ];

        // ============================================================
        // SISTEM KREDIT - Distribusi kredit (ganti dari plan)
        // ============================================================
        $creditDistribution = User::role('guru')
            ->select(
                DB::raw('CASE 
                    WHEN credits = 0 THEN "Tidak ada kredit"
                    WHEN credits BETWEEN 1 AND 10 THEN "1-10 Kredit"
                    WHEN credits BETWEEN 11 AND 50 THEN "11-50 Kredit"
                    ELSE "50+ Kredit"
                END as credit_range'),
                DB::raw('count(*) as total')
            )
            ->groupBy('credit_range')
            ->get();

        // Recent users (pending approval)
        $pendingUsers = User::role('guru')
            ->pendingApproval()
            ->latest()
            ->take(5)
            ->get();

        // ============================================================
        // SISTEM KREDIT - Tidak ada tabel transaksi kredit
        // ============================================================
        $recentTransactions = collect();

        // Monthly revenue chart data - dihapus
        $monthlyRevenue = collect();

        return view('admin.dashboard', compact(
            'stats',
            'revenue',
            'creditDistribution',
            'pendingUsers',
            'recentTransactions',
            'monthlyRevenue'
        ));
    }
}
