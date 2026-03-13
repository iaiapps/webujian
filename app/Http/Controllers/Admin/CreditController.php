<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    // Dashboard/List user dengan kredit
    public function index(Request $request)
    {
        $query = User::role('guru');

        // Filter search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter kredit
        if ($request->filled('credit_filter')) {
            switch ($request->credit_filter) {
                case 'empty':
                    $query->where('credits', 0);
                    break;
                case 'low':
                    $query->whereBetween('credits', [1, 5]);
                    break;
                case 'high':
                    $query->where('credits', '>', 50);
                    break;
            }
        }

        $users = $query->withCount('creditTransactions')
            ->orderBy('credits', 'asc')
            ->paginate(20);

        // Statistics
        $stats = [
            'total_users' => User::role('guru')->count(),
            'total_credits' => User::role('guru')->sum('credits'),
            'empty_credits' => User::role('guru')->where('credits', 0)->count(),
            'low_credits' => User::role('guru')->whereBetween('credits', [1, 5])->count(),
            'today_transactions' => CreditTransaction::whereDate('created_at', today())->count(),
        ];

        return view('admin.credits.index', compact('users', 'stats'));
    }

    // Detail user + form manajemen kredit
    public function show(User $user)
    {
        $user->loadCount('testPackages');

        $transactions = $user->creditTransactions()
            ->with('performedBy')
            ->paginate(20);

        $stats = [
            'total_in' => $user->creditTransactions()->in()->sum('amount'),
            'total_out' => abs($user->creditTransactions()->out()->sum('amount')),
            'total_packages' => $user->test_packages_count,
        ];

        return view('admin.credits.show', compact('user', 'transactions', 'stats'));
    }

    // Tambah kredit manual
    public function add(Request $request, User $user)
    {
        $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        DB::beginTransaction();
        try {
            $transaction = $user->addCredits(
                $request->amount,
                'manual_add',
                "Penambahan manual: {$request->reason}",
                null,
                'manual',
                auth()->id(),
                $request->reason
            );

            DB::commit();

            return redirect()->back()->with('success', "Berhasil menambah {$request->amount} kredit ke {$user->name}. Balance sekarang: {$transaction->balance_after}");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal menambah kredit: '.$e->getMessage());
        }
    }

    // Kurangi kredit manual
    public function deduct(Request $request, User $user)
    {
        $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        if ($user->credits < $request->amount) {
            return redirect()->back()->with('error', "Kredit user tidak cukup. Current: {$user->credits}, Request: {$request->amount}");
        }

        DB::beginTransaction();
        try {
            $transaction = $user->deductCredits(
                $request->amount,
                'manual_deduct',
                "Pengurangan manual: {$request->reason}",
                null,
                'manual'
            );

            // Update transaction with performed_by and notes
            if ($transaction) {
                $transaction->update([
                    'performed_by' => auth()->id(),
                    'notes' => $request->reason,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', "Berhasil mengurangi {$request->amount} kredit dari {$user->name}. Balance sekarang: {$transaction->balance_after}");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal mengurangi kredit: '.$e->getMessage());
        }
    }

    // History semua transaksi
    public function transactions(Request $request)
    {
        $query = CreditTransaction::with(['user', 'performedBy'])->latest();

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $transactions = $query->paginate(50);

        // Summary
        $summary = [
            'total_in' => CreditTransaction::in()->sum('amount'),
            'total_out' => abs(CreditTransaction::out()->sum('amount')),
        ];

        return view('admin.credits.transactions', compact('transactions', 'summary'));
    }
}
