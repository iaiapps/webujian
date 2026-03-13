<?php

// app/Http/Controllers/Guru/CreditController.php
// ============================================================
// SISTEM KREDIT - Ganti dari SubscriptionController
// ============================================================

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\CreditPackage;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CreditController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:guru', 'check.approved']);
    }

    public function index()
    {
        $user = Auth::user();

        $creditInfo = [
            'current_credits' => $user->credits,
            'can_create_package' => $user->canCreatePackage(),
        ];

        // Ambil 5 transaksi terakhir
        $recentTransactions = $user->creditTransactions()->take(5)->get();

        $payment = Setting::getByGroup('payment');

        return view('guru.credits.index', compact('creditInfo', 'recentTransactions', 'payment'));
    }

    public function history()
    {
        $user = Auth::user();
        $transactions = $user->creditTransactions()->paginate(20);

        $stats = [
            'total_in' => $user->creditTransactions()->in()->sum('amount'),
            'total_out' => abs($user->creditTransactions()->out()->sum('amount')),
        ];

        return view('guru.credits.history', compact('transactions', 'stats'));
    }

    public function topup()
    {
        $user = Auth::user();

        // Ambil paket kredit aktif dari database
        $creditPackages = CreditPackage::active()->ordered()->get();

        $payment = Setting::getByGroup('payment');

        return view('guru.credits.topup', compact('creditPackages', 'payment'));
    }

    public function purchase(Request $request)
    {
        $request->validate([
            'package_id' => ['required', 'exists:credit_packages,id'],
            'payment_proof' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $user = Auth::user();
        $package = CreditPackage::findOrFail($request->package_id);

        // Hitung total
        $creditAmount = $package->credit_amount;
        $bonusCredits = $package->bonus_credits;
        $totalCredits = $package->getTotalCredits();
        $totalPrice = $package->price;

        $proofPath = $this->uploadPaymentProof($request->file('payment_proof'));

        $invoiceNumber = 'CRD-'.date('Ymd').'-'.strtoupper(uniqid());

        session([
            'credit_purchase' => [
                'invoice_number' => $invoiceNumber,
                'package_name' => $package->name,
                'credit_amount' => $creditAmount,
                'bonus_credits' => $bonusCredits,
                'total_credits' => $totalCredits,
                'total_price' => $totalPrice,
                'payment_proof' => $proofPath,
            ],
        ]);

        return redirect()->route('guru.credits.success');
    }

    public function success()
    {
        $purchase = session('credit_purchase');

        if (! $purchase) {
            return redirect()->route('guru.credits.index');
        }

        $user = Auth::user();

        // Catat transaksi utama (purchase)
        $user->addCredits(
            $purchase['credit_amount'],
            'purchase',
            "Pembelian paket {$purchase['package_name']}",
            $purchase['invoice_number'],
            'purchase'
        );

        // Catat transaksi bonus jika ada
        if ($purchase['bonus_credits'] > 0) {
            $user->addCredits(
                $purchase['bonus_credits'],
                'bonus',
                "Bonus {$purchase['bonus_credits']} kredit ({$purchase['package_name']})",
                $purchase['invoice_number'],
                'purchase'
            );
        }

        $result = $purchase;
        $user->refresh();

        session()->forget('credit_purchase');

        return view('guru.credits.success', compact('result'))->with('success', 'Pembelian kredit berhasil! Kredit telah ditambahkan ke akun Anda.');
    }

    public function cancel(Request $request)
    {
        session()->forget('credit_purchase');

        return redirect()->route('guru.credits.index');
    }

    private function uploadPaymentProof($file)
    {
        $filename = 'credit_payment_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $path = 'payment_proofs/'.$filename;

        Storage::disk('public')->put($path, file_get_contents($file));

        return $path;
    }
}
