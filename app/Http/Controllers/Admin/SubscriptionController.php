<?php

// app/Http/Controllers/Admin/SubscriptionController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index(Request $request)
    {
        $query = Subscription::with('user')->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by plan
        if ($request->filled('plan')) {
            $query->where('plan', $request->plan);
        }

        $subscriptions = $query->paginate(20);

        // Count pending
        $pendingCount = Subscription::where('status', 'pending')->count();

        return view('admin.subscriptions.index', compact('subscriptions', 'pendingCount'));
    }

    public function show(Subscription $subscription)
    {
        $subscription->load(['user', 'histories']);

        return view('admin.subscriptions.show', compact('subscription'));
    }

    public function approve(Subscription $subscription)
    {
        if ($subscription->status !== 'pending') {
            return redirect()->back()->with('error', 'Subscription status bukan pending.');
        }

        DB::beginTransaction();
        try {
            // Update subscription
            $subscription->update([
                'status' => 'active',
                'started_at' => now(),
            ]);

            // Update user plan
            $teacher = $subscription->user;

            // Set limits based on plan
            $limits = [
                'pro' => [
                    'max_students' => 150,
                    'max_packages' => 999999,
                    'max_questions' => 500,
                    'max_classes' => 5,
                ],
                'advanced' => [
                    'max_students' => 999999,
                    'max_packages' => 999999,
                    'max_questions' => 999999,
                    'max_classes' => 999999,
                ],
            ];

            $teacher->update([
                'plan' => $subscription->plan,
                'plan_expired_at' => $subscription->expired_at,
                'max_students' => $limits[$subscription->plan]['max_students'],
                'max_packages' => $limits[$subscription->plan]['max_packages'],
                'max_questions' => $limits[$subscription->plan]['max_questions'],
                'max_classes' => $limits[$subscription->plan]['max_classes'],
            ]);

            // Create history
            $subscription->histories()->create([
                'user_id' => $teacher->id,
                'action' => 'upgraded',
                'old_plan' => 'free',
                'new_plan' => $subscription->plan,
                'amount' => $subscription->amount,
                'notes' => 'Approved by admin',
            ]);

            // TODO: Send email notification to teacher
            // Mail::to($teacher->email)->send(new SubscriptionApproved($subscription));

            DB::commit();

            return redirect()->back()->with('success', "Subscription untuk {$teacher->name} berhasil diapprove!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal approve subscription: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, Subscription $subscription)
    {
        $request->validate([
            'reject_reason' => ['required', 'string', 'max:500'],
        ]);

        if ($subscription->status !== 'pending') {
            return redirect()->back()->with('error', 'Subscription status bukan pending.');
        }

        $subscription->update([
            'status' => 'failed',
            'notes' => $request->reject_reason,
        ]);

        $subscription->histories()->create([
            'user_id' => $subscription->user_id,
            'action' => 'cancelled',
            'notes' => 'Rejected by admin: ' . $request->reject_reason,
        ]);

        // TODO: Send email notification
        // Mail::to($subscription->user->email)->send(new SubscriptionRejected($subscription));

        return redirect()->back()->with('success', 'Subscription berhasil ditolak.');
    }

    public function manualDowngrade(Request $request, User $user)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        DB::beginTransaction();
        try {
            $oldPlan = $user->plan;

            // Reset to free plan
            $user->update([
                'plan' => 'free',
                'plan_expired_at' => null,
                'max_students' => 30,
                'max_packages' => 3,
                'max_questions' => 100,
                'max_classes' => 1,
            ]);

            // Create subscription history if has active subscription
            $activeSubscription = $user->activeSubscription;
            if ($activeSubscription) {
                $activeSubscription->update(['status' => 'expired']);

                $activeSubscription->histories()->create([
                    'user_id' => $user->id,
                    'action' => 'downgraded',
                    'old_plan' => $oldPlan,
                    'new_plan' => 'free',
                    'notes' => 'Manual downgrade by admin: ' . $request->reason,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', "User {$user->name} berhasil di-downgrade ke FREE plan.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal downgrade: ' . $e->getMessage());
        }
    }
}
