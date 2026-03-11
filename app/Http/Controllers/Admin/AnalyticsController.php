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

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index(Request $request)
    {
        $period = $request->get('period', 'month');

        // Overview stats
        $stats = [
            'total_users' => User::role('guru')->count(),
            'total_students' => Student::count(),
            'total_questions' => Question::count(),
            'total_packages' => TestPackage::count(),
            'total_attempts' => TestAttempt::where('status', 'completed')->count(),
            'total_revenue' => Subscription::where('status', 'active')->sum('amount'),
        ];

        // User growth
        $userGrowth = User::role('guru')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Revenue by month
        $revenueByMonth = Subscription::where('status', 'active')
            ->whereYear('confirmed_at', now()->year)
            ->select(
                DB::raw('MONTH(confirmed_at) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Plan distribution
        $planDistribution = User::role('guru')
            ->select('plan', DB::raw('COUNT(*) as count'))
            ->groupBy('plan')
            ->get();

        // Top teachers by students
        $topTeachers = User::role('guru')
            ->withCount('students')
            ->orderByDesc('students_count')
            ->take(10)
            ->get();

        // Test statistics
        $testStats = [
            'avg_score' => TestAttempt::where('status', 'completed')->avg('total_score'),
            'highest_score' => TestAttempt::where('status', 'completed')->max('total_score'),
            'total_correct' => TestAttempt::where('status', 'completed')->sum('correct_answers'),
            'total_wrong' => TestAttempt::where('status', 'completed')->sum('wrong_answers'),
        ];

        // Recent activities
        $recentSubscriptions = Subscription::with('user')
            ->where('status', 'active')
            ->latest('confirmed_at')
            ->take(5)
            ->get();

        return view('admin.analytics.index', compact(
            'stats',
            'userGrowth',
            'revenueByMonth',
            'planDistribution',
            'topTeachers',
            'testStats',
            'recentSubscriptions'
        ));
    }
}
