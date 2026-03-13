<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Student;
use App\Models\TestAttempt;
use App\Models\TestPackage;
use App\Models\User;
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
            'total_revenue' => 0, // SISTEM KREDIT - Tidak ada revenue tracking
        ];

        // User growth
        $userGrowth = User::role('guru')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // SISTEM KREDIT - Revenue tracking dihapus
        $revenueByMonth = collect();

        // SISTEM KREDIT - Distribusi kredit (ganti dari plan distribution)
        $creditDistribution = User::role('guru')
            ->select(
                DB::raw('CASE 
                    WHEN credits = 0 THEN "Tidak ada kredit"
                    WHEN credits BETWEEN 1 AND 10 THEN "1-10 Kredit"
                    WHEN credits BETWEEN 11 AND 50 THEN "11-50 Kredit"
                    ELSE "50+ Kredit"
                END as credit_range'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('credit_range')
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

        // SISTEM KREDIT - Recent subscriptions dihapus
        $recentSubscriptions = collect();

        return view('admin.analytics.index', compact(
            'stats',
            'userGrowth',
            'revenueByMonth',
            'creditDistribution',
            'topTeachers',
            'testStats',
            'recentSubscriptions'
        ));
    }
}
