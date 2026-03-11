<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Question;
use App\Models\TestPackage;
use App\Models\TestAttempt;
use App\Models\Subscription;
use Illuminate\Http\Request;
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

        // Revenue (from subscriptions)
        $revenue = [
            'today' => Subscription::where('status', 'active')
                ->whereDate('started_at', today())
                ->sum('amount'),
            'this_month' => Subscription::where('status', 'active')
                ->whereMonth('started_at', now()->month)
                ->whereYear('started_at', now()->year)
                ->sum('amount'),
            'total' => Subscription::where('status', 'active')->sum('amount'),
        ];

        // Plan distribution
        $planDistribution = User::role('guru')
            ->select('plan', DB::raw('count(*) as total'))
            ->groupBy('plan')
            ->get();

        // Recent users (pending approval)
        $pendingUsers = User::role('guru')
            ->pendingApproval()
            ->latest()
            ->take(5)
            ->get();

        // Recent subscriptions
        $recentSubscriptions = Subscription::with('user')
            ->latest()
            ->take(10)
            ->get();

        // Monthly revenue chart data
        $monthlyRevenue = Subscription::where('status', 'active')
            ->whereYear('started_at', now()->year)
            ->select(
                DB::raw('MONTH(started_at) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'revenue',
            'planDistribution',
            'pendingUsers',
            'recentSubscriptions',
            'monthlyRevenue'
        ));
    }
}
