<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\TestAttempt;
use App\Models\TestPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonitoringController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:guru', 'check.approved']);
    }

    public function index(?TestPackage $package = null)
    {
        // If no package specified, show all active packages
        if (! $package) {
            $activePackages = TestPackage::where('user_id', Auth::id())
                ->where('is_active', true)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->withCount(['testAttempts' => function ($query) {
                    $query->where('status', 'ongoing');
                }])
                ->withCount(['testAttempts as flagged_count' => function ($query) {
                    $query->where('is_flagged', true);
                }])
                ->get();

            return view('guru.monitoring.index', compact('activePackages'));
        }

        // Check ownership
        if ($package->user_id !== Auth::id()) {
            abort(403);
        }

        // Get ongoing attempts with students - EAGER LOAD dengan with()
        $ongoingAttempts = TestAttempt::where('package_id', $package->id)
            ->where('status', 'ongoing')
            ->where('is_flagged', false) // Hanya tampilkan yang belum di-flag
            ->with(['student', 'student.classRoom'])
            ->orderBy('violations_count', 'desc')
            ->get();

        // Get flagged attempts (separate)
        $flaggedAttempts = TestAttempt::where('package_id', $package->id)
            ->where('is_flagged', true)
            ->with(['student', 'student.classRoom'])
            ->orderBy('flagged_at', 'desc')
            ->get();

        return view('guru.monitoring.package', compact('package', 'ongoingAttempts', 'flaggedAttempts'));
    }

    public function getData(Request $request, TestPackage $package)
    {
        // Check ownership
        if ($package->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get ongoing attempts with real-time data
        $attempts = TestAttempt::where('package_id', $package->id)
            ->where('status', 'ongoing')
            ->with(['student:id,name,username,class_id', 'student.classRoom:id,name'])
            ->orderBy('violations_count', 'desc')
            ->get()
            ->map(function ($attempt) {
                // Parse violations log
                $violationsLog = $attempt->violations_log ? json_decode($attempt->violations_log, true) : [];

                // Format violations for display
                $formattedViolations = array_map(function ($log) {
                    $typeLabels = [
                        'tab_switch' => 'Pindah Tab',
                        'window_blur' => 'Klik Luar',
                        'right_click' => 'Klik Kanan',
                        'copy' => 'Copy',
                        'cut' => 'Cut',
                        'paste' => 'Paste',
                        'devtools' => 'DevTools',
                        'exit_fullscreen' => 'Keluar Fullscreen',
                    ];

                    return [
                        'type' => $typeLabels[$log['type']] ?? $log['type'],
                        'time' => $log['time'],
                    ];
                }, $violationsLog);

                // Calculate time remaining
                $timeRemaining = $attempt->end_time ? $attempt->end_time->diffForHumans() : 'N/A';
                $minutesRemaining = $attempt->end_time ? $attempt->end_time->diffInMinutes(now()) : 0;

                return [
                    'id' => $attempt->id,
                    'student' => [
                        'name' => $attempt->student->name,
                        'username' => $attempt->student->username,
                        'class' => $attempt->student->classRoom?->name ?? 'Tidak ada kelas',
                    ],
                    'violations_count' => $attempt->violations_count,
                    'max_violations' => $attempt->package->max_violations ?? 3,
                    'violations_log' => $formattedViolations,
                    'start_time' => $attempt->start_time->format('H:i:s'),
                    'time_remaining' => $timeRemaining,
                    'minutes_remaining' => $minutesRemaining,
                    'is_flagged' => $attempt->is_flagged,
                    'ip_address' => $attempt->ip_address,
                ];
            });

        return response()->json([
            'attempts' => $attempts,
            'total_ongoing' => $attempts->count(),
            'total_violations' => $attempts->sum('violations_count'),
        ]);
    }

    public function getViolationsDetail(TestAttempt $attempt)
    {
        // Check ownership
        if ($attempt->package->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $violationsLog = $attempt->violations_log ? json_decode($attempt->violations_log, true) : [];

        $typeLabels = [
            'tab_switch' => 'Pindah Tab',
            'window_blur' => 'Klik Luar',
            'right_click' => 'Klik Kanan',
            'copy' => 'Copy',
            'cut' => 'Cut',
            'paste' => 'Paste',
            'devtools' => 'DevTools',
            'exit_fullscreen' => 'Keluar Fullscreen',
        ];

        $formattedLog = array_map(function ($log) use ($typeLabels) {
            return [
                'type' => $typeLabels[$log['type']] ?? $log['type'],
                'time' => $log['time'],
            ];
        }, $violationsLog);

        return response()->json([
            'violations_count' => $attempt->violations_count,
            'max_violations' => $attempt->package->max_violations ?? 3,
            'violations_log' => $formattedLog,
        ]);
    }
}
