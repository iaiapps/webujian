<?php

// app/Http/Controllers/Guru/CreditController.php
// ============================================================
// SISTEM KREDIT - Ganti dari SubscriptionController
// ============================================================

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
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

        $payment = Setting::getByGroup('payment');

        return view('guru.credits.index', compact('creditInfo', 'payment'));
    }

    public function topup()
    {
        $user = Auth::user();

        $creditPackages = $this->getCreditPackages();

        $payment = Setting::getByGroup('payment');

        return view('guru.credits.topup', compact('creditPackages', 'payment'));
    }

    public function purchase(Request $request)
    {
        $request->validate([
            'credit_amount' => ['required', 'integer', 'min:5'],
            'payment_proof' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $user = Auth::user();

        $creditPrice = Setting::get('credit_price', 5000);
        $bonusThreshold = Setting::get('credit_bonus_threshold', 5);
        $bonusAmount = Setting::get('credit_bonus_amount', 1);

        $creditAmount = $request->credit_amount;
        $totalPrice = $creditAmount * $creditPrice;

        $bonusCredits = floor($creditAmount / $bonusThreshold) * $bonusAmount;
        $totalCredits = $creditAmount + $bonusCredits;

        $proofPath = $this->uploadPaymentProof($request->file('payment_proof'));

        $invoiceNumber = 'CRD-'.date('Ymd').'-'.strtoupper(uniqid());

        session([
            'credit_purchase' => [
                'invoice_number' => $invoiceNumber,
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

        $user->addCredits($purchase['total_credits']);

        $result = $purchase;
        $user->refresh();

        return view('guru.credits.success', compact('result'))->with('success', 'Pembelian kredit berhasil! Kredit telah ditambahkan ke akun Anda.');
    }

    public function cancel(Request $request)
    {
        session()->forget('credit_purchase');

        return redirect()->route('guru.credits.index');
    }

    private function getCreditPackages()
    {
        $creditPrice = Setting::get('credit_price', 5000);
        $bonusThreshold = Setting::get('credit_bonus_threshold', 5);
        $bonusAmount = Setting::get('credit_bonus_amount', 1);

        $packages = [];
        for ($i = 5; $i <= 50; $i += 5) {
            $bonus = floor($i / $bonusThreshold) * $bonusAmount;
            $packages[] = [
                'credits' => $i,
                'bonus' => $bonus,
                'total' => $i + $bonus,
                'price' => $i * $creditPrice,
            ];
        }

        return $packages;
    }

    private function uploadPaymentProof($file)
    {
        $filename = 'credit_payment_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $path = 'payment_proofs/'.$filename;

        Storage::disk('public')->put($path, file_get_contents($file));

        return $path;
    }
}
